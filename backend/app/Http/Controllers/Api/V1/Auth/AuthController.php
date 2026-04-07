<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AuthController
 *
 * Responsibilities (only these, nothing more):
 *   1. Receive the validated HTTP request
 *   2. Build a DTO from validated data
 *   3. Delegate to AuthService
 *   4. Set / clear the HttpOnly refresh token cookie (HTTP concern)
 *   5. Return a standardized JSON response via ApiResponseTrait
 *
 * No business logic lives here.
 * No Eloquent queries live here.
 * No token generation lives here.
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AuthService $authService,
    ) {}

    // =========================================================================
    // REGISTER
    // =========================================================================

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register(
                RegisterDTO::fromRequest($request->validated())
            );

            return $this->created(
                new UserResource($result['user']),
                'Registration successful'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    // =========================================================================
    // LOGIN
    // =========================================================================

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                LoginDTO::fromRequest($request->validated())
            );

            $response = $this->success(
                [
                    'access_token' => $result['access_token'],
                    'token_type'   => 'Bearer',
                    'user'         => new UserResource($result['user']),
                ],
                'Login successful'
            );

            // Set the refresh token in an HttpOnly cookie.
            // HttpOnly: JavaScript cannot read this cookie — eliminates XSS token theft.
            // Secure: only sent over HTTPS (browser enforces this in production).
            // SameSite=Strict: cookie is never sent on cross-site navigated requests,
            //   only on requests originating from the same site — mitigates CSRF.
            // Path=/api/v1/auth: cookie is only sent to the refresh/logout endpoints,
            //   not to every API request — reduces exposure surface.
            return $response->withCookie(
                cookie(
                    name:     'refresh_token',
                    value:    $result['refresh_token'],
                    minutes:  60 * 24 * config('app.refresh_token_ttl_days', 30),
                    path:     '/api/v1/auth',
                    secure:   app()->isProduction(),
                    httpOnly: true,
                    sameSite: 'Strict',
                )
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    // =========================================================================
    // REFRESH
    // =========================================================================

    public function refresh(Request $request): JsonResponse
    {
        // Read refresh token from HttpOnly cookie — never from request body.
        // If it's not in the cookie, the client has no valid session to refresh.
        $rawRefreshToken = $request->cookie('refresh_token');

        if (! $rawRefreshToken) {
            return $this->unauthenticated('No refresh token provided');
        }

        try {
            $result = $this->authService->refresh($rawRefreshToken);

            $response = $this->success(
                [
                    'access_token' => $result['access_token'],
                    'token_type'   => 'Bearer',
                    'user'         => new UserResource($result['user']),
                ],
                'Token refreshed successfully'
            );

            // Rotate the cookie: new refresh token replaces the old one.
            // The old token is already revoked in the DB by AuthService.
            return $response->withCookie(
                cookie(
                    name:     'refresh_token',
                    value:    $result['refresh_token'],
                    minutes:  60 * 24 * config('app.refresh_token_ttl_days', 30),
                    path:     '/api/v1/auth',
                    secure:   app()->isProduction(),
                    httpOnly: true,
                    sameSite: 'Strict',
                )
            );
        } catch (\Exception $e) {
            // Refresh failed (expired, revoked, theft detected) — clear the cookie too
            return $this->unauthenticated($e->getMessage())
                ->withCookie($this->clearRefreshTokenCookie());
        }
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================

    public function logout(Request $request): JsonResponse
    {
        $rawRefreshToken = $request->cookie('refresh_token');

        try {
            // Pass empty string if cookie missing — Service will handle gracefully
            $this->authService->logout($rawRefreshToken ?? '');
        } catch (\Exception $e) {
            // Logout should always succeed from the user's perspective —
            // even if the token was already invalid, we still clear the cookie.
        }

        return $this->success(null, 'Logged out successfully')
            ->withCookie($this->clearRefreshTokenCookie());
    }

    // =========================================================================
    // ME
    // =========================================================================

    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->me();

            return $this->success(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->unauthenticated('Could not retrieve user');
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Returns an expired cookie that instructs the browser to delete
     * the refresh_token cookie immediately.
     * Setting a cookie with a past expiry is the standard way to delete cookies.
     */
    private function clearRefreshTokenCookie(): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            name:     'refresh_token',
            value:    '',
            minutes:  -1,
            path:     '/api/v1/auth',
            secure:   app()->isProduction(),
            httpOnly: true,
            sameSite: 'Strict',
        );
    }

    /**
     * Handles Google OAuth login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $credential = $request->input('credential');
        if (!$credential) {
            return $this->error('Missing credential', 422);
        }

        try {
            $result = $this->authService->loginWithGoogle($credential);

            $response = $this->success(
                [
                    'access_token' => $result['access_token'],
                    'token_type'   => 'Bearer',
                    'user'         => new UserResource($result['user']),
                ],
                'Login successful'
            );

            return $response->withCookie(
                cookie(
                    name:     'refresh_token',
                    value:    $result['refresh_token'],
                    minutes:  60 * 24 * config('app.refresh_token_ttl_days', 30),
                    path:     '/api/v1/auth',
                    secure:   app()->isProduction(),
                    httpOnly: true,
                    sameSite: 'Strict',
                )
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
