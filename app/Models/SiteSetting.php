<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $row = static::where('key', $key)->first();

            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    public static function getMany(array $keys): array
    {
        return collect($keys)->mapWithKeys(fn ($key) => [$key => static::get($key)])->all();
    }

    /**
     * Like get(), but for admin-authored content that can have a per-locale
     * variant stored under "{$key}_{$locale}". Falls back to the base key
     * when no locale, or an empty one, was set for the current locale.
     */
    public static function getLocalized(string $key, mixed $default = null): mixed
    {
        $locale = app()->getLocale();

        if ($locale !== 'en') {
            $localized = static::get("{$key}_{$locale}");

            if (filled($localized)) {
                return $localized;
            }
        }

        return static::get($key, $default);
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::set($key, $value);
        }
    }
}
