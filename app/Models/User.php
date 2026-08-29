<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Stamp a successful login and record it as an activity row.
     *
     * The row is written straight through ActivityLog rather than via
     * ActivityLogger, deliberately: the logger notifies every admin on every
     * call, which would fan a single login out into one notification per
     * admin. Logins are high-frequency and not individually noteworthy — the
     * activity dashboard reads them in aggregate.
     */
    public function recordLogin(?string $ip = null, ?string $userAgent = null): void
    {
        $now = now();

        $this->forceFill([
            'last_login_at' => $now,
            'last_seen_at' => $now,
            'login_count' => $this->login_count + 1,
        ])->saveQuietly();

        ActivityLog::create([
            'user_id' => $this->id,
            'action' => "{$this->role}.logged_in",
            'description' => "{$this->name} logged in",
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    public function isOnline(int $withinMinutes = 5): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes($withinMinutes));
    }

    public function school()
    {
        return $this->hasOne(School::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSchool(): bool
    {
        return $this->role === 'school';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }
}
