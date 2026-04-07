<?php

namespace App\Services\Auth;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Models\RefreshToken;
use App\Models\Role;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    // =========================================================================
    // REGISTER
    // =========================================================================

    /**
     * Register a new user account.
     *
     * Design decisions:
     * - No token is issued on register. User must explicitly login after registering.
     *   This forces a deliberate login action and confirms credentials work.
     * - Default role 'user' is assigned here in the Service, not in the Controller.
     * - If the 'user' role doesn't exist yet (fresh install), we create it silently.
     *
     * @return array{user: User}
     */
    public function register(RegisterDTO $dto): array
    {
        $user = $this->userRepository->create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => $dto->password, // hashed automatically via 'hashed' cast on User model
        ]);

        // Assign default role. firstOrCreate ensures this is idempotent.
        $defaultRole = Role::firstOrCreate(
            ['name' => 'user'],
            ['display_name' => 'User']
        );

        $user->roles()->attach($defaultRole->id);

        // Reload user with roles so UserResource can include roles in the response.
        return ['user' => $this->userRepository->findWithRoles($user->id)];
    }

    // =========================================================================
    // LOGIN
    // =========================================================================

    /**
     * Authenticate a user and issue tokens.
     *
     * Flow:
     * 1. Find user by email
     * 2. Verify password
     * 3. Check account is active
     * 4. Issue JWT access token (15 min)
     * 5. Create refresh token record in DB (30 days, new family)
     * 6. Return access token + raw refresh token + user
     *
     * Why return raw refresh token here and not set the cookie in the Service?
     * - Setting cookies is an HTTP concern → belongs in the Controller.
     * - Service stays framework-agnostic — could be called from CLI, tests, etc.
     *
     * @return array{access_token: string, refresh_token: string, user: User}
     * @throws \Exception
     */
    public function login(LoginDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            // Generic message intentionally — do not reveal whether email or password was wrong.
            // Separate error messages enable user enumeration attacks.
            throw new \Exception('Invalid credentials', 401);
        }

        if (! $user->isActive()) {
            throw new \Exception('Your account has been deactivated', 403);
        }

        $accessToken = JWTAuth::fromUser($user);

        $rawRefreshToken = $this->createRefreshToken($user, familyId: Str::uuid()->toString());

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $rawRefreshToken,
            'user'          => $this->userRepository->findWithRoles($user->id),
        ];
    }

    // =========================================================================
    // REFRESH — Token Rotation + Theft Detection
    // =========================================================================

    /**
     * Issue a new access token using a refresh token.
     *
     * This implements the RFC-recommended "refresh token rotation" pattern:
     *
     * NORMAL FLOW:
     *   1. Client sends refresh token (from HttpOnly cookie)
     *   2. We hash it and find the matching DB record
     *   3. If valid → revoke old token → create new token (same family) → return new access token
     *
     * THEFT DETECTION FLOW:
     *   If a refresh token that was already revoked is presented again,
     *   it means either:
     *     a) An attacker stole a previous token and is trying to replay it, OR
     *     b) A network glitch caused a token to be used twice (rare, handled by Grace Period)
     *   In both cases, we REVOKE THE ENTIRE FAMILY immediately and force re-login.
     *   This limits the damage window of a stolen refresh token to one rotation cycle.
     *
     * @return array{access_token: string, refresh_token: string, user: User}
     * @throws \Exception
     */
    public function refresh(string $rawToken): array
    {
        $hashedToken = $this->hashToken($rawToken);

        $refreshToken = RefreshToken::where('token', $hashedToken)->first();

        // Token not found in DB at all — invalid or tampered
        if (! $refreshToken) {
            throw new \Exception('Invalid refresh token', 401);
        }

        // Token was already used (revoked) — THEFT DETECTED
        // Revoke the entire family to protect the legitimate user
        if ($refreshToken->isRevoked()) {
            $this->revokeFamily($refreshToken->family_id);

            Log::warning('Refresh token reuse detected — full family revoked', [
                'user_id'   => $refreshToken->user_id,
                'family_id' => $refreshToken->family_id,
            ]);

            throw new \Exception('Refresh token reuse detected. Please log in again.', 401);
        }

        // Token exists and is not revoked, but has expired naturally
        if (! $refreshToken->isValid()) {
            throw new \Exception('Refresh token expired', 401);
        }

        // All checks passed — rotate: revoke old, issue new (same family)
        $refreshToken->update(['revoked_at' => now()]);

        $user = $refreshToken->user;

        if (! $user->isActive()) {
            throw new \Exception('Your account has been deactivated', 403);
        }

        $newAccessToken = JWTAuth::fromUser($user);

        // New token inherits the same family_id — maintains the rotation chain
        $newRawRefreshToken = $this->createRefreshToken($user, familyId: $refreshToken->family_id);

        return [
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRawRefreshToken,
            'user'          => $this->userRepository->findWithRoles($user->id),
        ];
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================

    /**
     * Invalidate the current session.
     *
     * Two things must happen on logout:
     * 1. Blacklist the current JWT access token (JWT blacklist in cache)
     *    → Prevents the access token from being used for its remaining TTL
     * 2. Revoke the refresh token from the DB
     *    → Prevents the client from silently re-authenticating via /refresh
     *
     * Clearing the HttpOnly cookie is done in the Controller (HTTP concern).
     *
     * @throws \Exception
     */
    public function logout(string $rawRefreshToken): void
    {
        // Blacklist the current access token immediately.
        // After this, any request with the same token returns 401 even before TTL expires.
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // Token may already be expired — log but don't block logout
            Log::info('JWT invalidation skipped during logout', ['error' => $e->getMessage()]);
        }

        // Revoke the refresh token so the client cannot silently refresh
        $hashedToken = $this->hashToken($rawRefreshToken);

        RefreshToken::where('token', $hashedToken)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    // =========================================================================
    // ME
    // =========================================================================

    /**
     * Return the currently authenticated user with roles loaded.
     */
    public function me(): User
    {
        $userId = JWTAuth::parseToken()->authenticate()->id;

        return $this->userRepository->findWithRoles($userId);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Generate a cryptographically secure random token,
     * store its SHA-256 hash in the DB, and return the raw token.
     *
     * The raw token ONLY lives in the client's HttpOnly cookie.
     * The DB only ever contains the hash — if the DB is breached,
     * the hashes are useless without the raw tokens.
     */
    private function createRefreshToken(User $user, string $familyId): string
    {
        // 64 bytes of randomness → 128 hex chars → secure enough for a refresh token
        $rawToken = bin2hex(random_bytes(64));

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => $this->hashToken($rawToken),
            'family_id'  => $familyId,
            'expires_at' => Carbon::now()->addDays(
                (int) config('app.refresh_token_ttl_days', 30)
            ),
        ]);

        return $rawToken;
    }

    /**
     * Hash a raw token using SHA-256.
     * Consistent hashing across store + lookup operations.
     */
    private function hashToken(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    /**
     * Revoke all tokens in a family.
     * Called when token replay is detected (theft detection).
     * Forces the legitimate user to log in again from scratch.
     */
    private function revokeFamily(string $familyId): void
    {
        RefreshToken::where('family_id', $familyId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public function loginWithGoogle(string $credential): array
    {
        $client = new Client();

        $response = $client->get('https://oauth2.googleapis.com/tokeninfo', [
            'query' => ['id_token' => $credential],
        ]);

        $data = json_decode($response->getBody(), true);

        // Check that the token is valid and intended for our app by verifying the 'aud' claim
        $googleClientId = Config::get('services.google.client_id');
        if (!isset($data['aud']) || $data['aud'] !== $googleClientId) {
            throw new \Exception('Invalid Google token', 401);
        }

        // Extract relevant user info from the token payload
        $googleId = $data['sub'] ?? null;
        $email = $data['email'] ?? null;
        $name = $data['name'] ?? null;

        if (!$googleId || !$email) {
            throw new \Exception('Could not retrieve user info from Google', 401);
        }

        // Find or create a local user linked to this Google ID
        $user = $this->userRepository->findByGoogleId($googleId);

        // Doesn't exist yet — create a new user record with the Google info
        if (!$user) {
            $user = $this->userRepository->create([
                'name' => $name ?? $email,
                'email' => $email,
                'google_id' => $googleId,
                'is_active' => true,
                'password' => null,
            ]);
        }

        if (! $user->isActive()) {
            throw new \Exception('Your account has been deactivated', 403);
        }

        $accessToken = JWTAuth::fromUser($user);
        $rawRefreshToken = $this->createRefreshToken($user, familyId: Str::uuid()->toString());

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $rawRefreshToken,
            'user'          => $this->userRepository->findWithRoles($user->id),
        ];
    }
}