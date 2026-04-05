<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * UserRepositoryInterface
 *
 * Defines the contract for all User data-access operations.
 * The Service layer depends on this interface, not the concrete class.
 *
 * Benefits:
 * - Swap the implementation (e.g. add caching) without touching any Service
 * - Easy to mock in unit tests
 * - Enforces a consistent data-access API across the team
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by their primary key.
     * Returns null if not found (no exception thrown).
     */
    public function findById(int $id): ?User;

    /**
     * Find a user by their email address.
     * Returns null if not found — callers decide how to handle missing users.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user record.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function create(array $data): User;

    /**
     * Update an existing user's attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;

    /**
     * Load a user with their roles (and role permissions) eager-loaded.
     * Used after login to avoid N+1 queries when checking roles/permissions.
     */
    public function findWithRoles(int $id): ?User;

    /**
     * Check whether an email address is already taken.
     * Used in RegisterRequest validation for a clean, readable rule.
     */
    public function existsByEmail(string $email): bool;
}
