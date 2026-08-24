<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class PasswordResetRequest extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'email',
        'requested_password',
        'status',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function setRequestedPasswordAttribute(?string $value): void
    {
        $this->attributes['requested_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function decryptedPassword(): ?string
    {
        if (! $this->requested_password) {
            return null;
        }

        try {
            return Crypt::decryptString($this->requested_password);
        } catch (DecryptException) {
            return null;
        }
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
