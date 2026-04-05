<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * UserResource
 *
 * Controls exactly which User fields are exposed in every API response.
 * This is the ONLY place the User model is serialized to JSON.
 *
 * Why not just return $user directly from the Controller?
 * - Returning raw models can accidentally expose sensitive fields
 *   (password hash, internal flags, pivot metadata, etc.)
 * - When the User schema changes, you only update this one file —
 *   not every controller that returns user data
 * - Conditional fields (whenLoaded) prevent N+1 queries by only
 *   including relationships when they were explicitly eager-loaded
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'is_active'         => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'created_at'        => $this->created_at,

            // whenLoaded: only included if roles were eager-loaded by the caller.
            // If roles were NOT loaded, this key is omitted entirely from the response
            // (not null, not [] — completely absent). This prevents silent N+1 queries
            // from firing inside the Resource when roles weren't pre-fetched.
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(fn ($role) => [
                    'name'         => $role->name,
                    'display_name' => $role->display_name,
                ]);
            }),
        ];
    }
}
