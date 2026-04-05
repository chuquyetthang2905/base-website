<?php

namespace App\DTOs\Auth;

/**
 * LoginDTO
 *
 * Carries validated login data from AuthController into AuthService.
 * Constructed directly from a validated LoginRequest — never from raw input.
 *
 * Why a DTO here instead of passing $request directly to the Service?
 * - Service has zero knowledge of HTTP (no Request import, no $request->input())
 * - Service can be called from CLI commands, tests, or queued jobs the same way
 * - Explicit typed properties catch field-name typos at development time, not runtime
 */
readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /**
     * Named constructor — creates DTO from a validated LoginRequest.
     * Called in AuthController after FormRequest passes validation.
     *
     * Usage: LoginDTO::fromRequest($request)
     */
    public static function fromRequest(array $validated): self
    {
        return new self(
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
