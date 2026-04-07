<?php

namespace App\Repositories\User;

use App\Models\User;

/**
 * UserRepository
 *
 * Concrete implementation of UserRepositoryInterface.
 * All database queries related to the User model live here.
 *
 * Keeping queries here means:
 * - Controllers and Services never call Eloquent directly
 * - Query changes (adding indexes, caching, scopes) happen in one place
 * - Identical queries are not duplicated across multiple Services
 */
class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        // fresh() reloads the model from DB so that columns with DB-level
        // defaults (e.g. is_active = true) are reflected in the returned model.
        return User::create($data)->fresh();
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function findWithRoles(int $id): ?User
    {
        // Eager-load roles and their permissions in a single query set.
        // Avoids N+1 when calling $user->hasRole() or $user->hasPermission()
        // immediately after login or token refresh.
        return User::with('roles.permissions')->find($id);
    }

    public function existsByEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }
}
