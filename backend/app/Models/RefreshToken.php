<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    // Matches the migration: only created_at, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'token',
        'family_id',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ---------------------------------------------------------------
    // Helper methods
    // Used in AuthService to check token validity before issuing new one
    // ---------------------------------------------------------------

    public function isValid(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at->isFuture();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}
