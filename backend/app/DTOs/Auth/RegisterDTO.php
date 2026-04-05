<?php

namespace App\DTOs\Auth;

/**
 * RegisterDTO
 *
 * Carries validated registration data from AuthController into AuthService.
 *
 * Note: 'password' here is still the plain-text password from the request.
 * Hashing happens inside AuthService (or via the 'hashed' cast on the User model)
 * — never in the DTO, never in the Controller.
 */
readonly class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /**
     * Named constructor — creates DTO from a validated RegisterRequest.
     *
     * Usage: RegisterDTO::fromRequest($request)
     */
    public static function fromRequest(array $validated): self
    {
        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
