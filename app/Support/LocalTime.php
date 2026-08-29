<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Carbon;

/**
 * The platform stores every timestamp in UTC but reports in the organiser's
 * own timezone. This wraps that one setting and the conversions built on it,
 * so no controller or view has to reason about offsets.
 */
class LocalTime
{
    public function __construct(
        public readonly string $timezone,
    ) {}

    public static function current(): self
    {
        $timezone = SiteSetting::get('expo_timezone') ?: config('app.timezone');

        // A bad timezone string would throw deep inside Carbon on every page,
        // so fall back rather than take the admin area down.
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = config('app.timezone');
        }

        return new self($timezone);
    }

    /** "Now", as the organiser's wall clock reads it. */
    public function now(): Carbon
    {
        return Carbon::now($this->timezone);
    }

    /** Re-express any instant in the reporting timezone. */
    public function local(\DateTimeInterface|string|null $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->setTimezone($this->timezone);
    }

    /**
     * Read a wall-clock string typed by the admin. It carries no offset, and
     * is meant as local time, so it is parsed in the reporting timezone.
     */
    public function parseLocal(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse(str_replace('T', ' ', $value), $this->timezone);
        } catch (\Throwable) {
            return null;
        }
    }

    /** A short timezone label for the UI, e.g. "GMT+8". */
    public function label(): string
    {
        $offset = $this->now()->getOffset() / 3600;
        $sign = $offset >= 0 ? '+' : '-';
        $magnitude = rtrim(rtrim(number_format(abs($offset), 1, '.', ''), '0'), '.');

        return 'GMT'.$sign.$magnitude;
    }

    /** The offset in seconds, used to align SQL buckets to the local clock. */
    public function offsetSeconds(): int
    {
        return $this->now()->getOffset();
    }

    /**
     * Constrain a query to one local window, converted to UTC for the
     * database. A null window matches nothing rather than everything.
     */
    public function scopeToWindow($query, string $column, ?Carbon $start, ?Carbon $end)
    {
        if (! $start || ! $end) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereBetween($column, [
            $start->copy()->utc(),
            $end->copy()->utc(),
        ]);
    }
}
