<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class School extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'school_type',
        'slug',
        'contact_person',
        'logo_path',
        'theme_color',
        'short_description',
        'full_description',
        'video_url',
        'website',
        'contact_email',
        'contact_phone',
        'address',
        'zoom_url',
        'is_published',
        'approved_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class)->orderBy('sort_order');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('sort_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isVisible(): bool
    {
        return $this->is_published && $this->isActive();
    }

    public function boothStatusLabel(): string
    {
        if ($this->is_published) {
            return 'Published';
        }

        return $this->approved_at ? 'Unpublished' : 'Pending Approval';
    }

    public function schoolTypeLabel(): string
    {
        return match ($this->school_type) {
            'national' => 'National',
            'international' => 'International',
            'online' => 'Online',
            default => '—',
        };
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? \Storage::disk('public')->url($this->logo_path) : null;
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'school';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function profileCompletion(): int
    {
        $fields = [
            $this->name,
            $this->logo_path,
            $this->short_description,
            $this->full_description,
            $this->contact_email,
        ];

        $filled = count(array_filter($fields, fn ($v) => filled($v)));

        return (int) round(($filled / count($fields)) * 100);
    }
}
