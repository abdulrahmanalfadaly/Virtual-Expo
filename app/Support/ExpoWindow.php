<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Carbon;

/**
 * The expo runs continuously for a number of full calendar days. Every figure
 * the dashboard reports is scoped to those days and expressed in the
 * organiser's own timezone rather than the server's UTC clock.
 */
class ExpoWindow
{
    public function __construct(
        public readonly string $timezone,
        public readonly ?string $startDate,
        public readonly int $days,
    ) {}

    public static function current(): self
    {
        $settings = SiteSetting::getMany(['expo_timezone', 'expo_start_date', 'expo_days']);

        $timezone = $settings['expo_timezone'] ?: config('app.timezone');

        // A bad timezone string would throw deep inside Carbon on every page,
        // so fall back rather than take the dashboard down.
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = config('app.timezone');
        }

        return new self(
            $timezone,
            $settings['expo_start_date'] ?: null,
            max(1, (int) ($settings['expo_days'] ?: 1)),
        );
    }

    public function isConfigured(): bool
    {
        return $this->startDate !== null;
    }

    /** "Now", as the organiser's wall clock reads it. */
    public function now(): Carbon
    {
        return Carbon::now($this->timezone);
    }

    /** Re-express any instant in the expo's local timezone. */
    public function local(\DateTimeInterface|string|null $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->setTimezone($this->timezone);
    }

    /**
     * One full local day per expo day, midnight to midnight.
     *
     * @return array<int, array{day:int, start:Carbon, end:Carbon}>
     */
    public function sessions(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $out = [];

        for ($i = 0; $i < $this->days; $i++) {
            $start = Carbon::parse($this->startDate, $this->timezone)->startOfDay()->addDays($i);

            $out[] = [
                'day' => $i + 1,
                'start' => $start,
                'end' => $start->copy()->addDay(),
            ];
        }

        return $out;
    }

    public function firstStart(): ?Carbon
    {
        $sessions = $this->sessions();

        return $sessions ? $sessions[0]['start']->copy() : null;
    }

    public function lastEnd(): ?Carbon
    {
        $sessions = $this->sessions();

        if (! $sessions) {
            return null;
        }

        return $sessions[count($sessions) - 1]['end']->copy();
    }

    /** The expo day currently running. */
    public function currentSession(): ?array
    {
        $now = $this->now();

        foreach ($this->sessions() as $session) {
            if ($now->greaterThanOrEqualTo($session['start']) && $now->lessThan($session['end'])) {
                return $session;
            }
        }

        return null;
    }

    /** The session belonging to today's local date. */
    public function todaySession(): ?array
    {
        $today = $this->now()->toDateString();

        foreach ($this->sessions() as $session) {
            if ($session['start']->toDateString() === $today) {
                return $session;
            }
        }

        return null;
    }

    public function nextSession(): ?array
    {
        $now = $this->now();

        foreach ($this->sessions() as $session) {
            if ($session['start']->greaterThan($now)) {
                return $session;
            }
        }

        return null;
    }

    /**
     * Days that have already begun, each clipped to the present moment — the
     * exact spans "all time" should aggregate over.
     *
     * @return array<int, array{day:int, start:Carbon, end:Carbon}>
     */
    public function elapsedSessions(): array
    {
        $now = $this->now();
        $out = [];

        foreach ($this->sessions() as $session) {
            if ($session['start']->greaterThan($now)) {
                continue;
            }

            $out[] = [
                'day' => $session['day'],
                'start' => $session['start']->copy(),
                'end' => $session['end']->greaterThan($now) ? $now->copy() : $session['end']->copy(),
            ];
        }

        return $out;
    }

    /** The expo is live for its whole run — no daily closing time. */
    public function isLive(): bool
    {
        return $this->hasStarted() && ! $this->hasEnded();
    }

    public function hasStarted(): bool
    {
        $first = $this->firstStart();

        return $first !== null && $this->now()->greaterThanOrEqualTo($first);
    }

    public function hasEnded(): bool
    {
        $last = $this->lastEnd();

        return $last !== null && $this->now()->greaterThanOrEqualTo($last);
    }

    public function currentDay(): ?int
    {
        if (! $this->hasStarted()) {
            return null;
        }

        if ($current = $this->currentSession()) {
            return $current['day'];
        }

        $elapsed = $this->elapsedSessions();

        return $elapsed ? $elapsed[count($elapsed) - 1]['day'] : null;
    }

    public function totalMinutes(): int
    {
        return $this->days * 1440;
    }

    public function elapsedMinutes(): int
    {
        $total = 0;

        foreach ($this->elapsedSessions() as $session) {
            $total += (int) round($session['start']->diffInMinutes($session['end'], absolute: true));
        }

        return $total;
    }

    public function remainingMinutes(): int
    {
        return max(0, $this->totalMinutes() - $this->elapsedMinutes());
    }

    public function progressPercent(): float
    {
        $total = $this->totalMinutes();

        if ($total <= 0) {
            return 0.0;
        }

        return round(min(100, max(0, ($this->elapsedMinutes() / $total) * 100)), 1);
    }

    /** When the expo closes, or opens if it hasn't yet. */
    public function boundaryTarget(): ?Carbon
    {
        if (! $this->isConfigured() || $this->hasEnded()) {
            return null;
        }

        return $this->hasStarted() ? $this->lastEnd() : $this->firstStart();
    }

    public function statusLabel(): string
    {
        if (! $this->isConfigured()) {
            return 'Not scheduled';
        }

        if ($this->hasEnded()) {
            return 'Expo finished';
        }

        if ($this->hasStarted()) {
            return 'Live now';
        }

        return 'Not open yet';
    }

    /** A short timezone label for the UI, e.g. "GMT+8". */
    public function timezoneLabel(): string
    {
        $offset = $this->now()->getOffset() / 3600;
        $sign = $offset >= 0 ? '+' : '-';
        $magnitude = rtrim(rtrim(number_format(abs($offset), 1, '.', ''), '0'), '.');

        return 'GMT'.$sign.$magnitude;
    }

    /** Human summary of the schedule, e.g. "29 Aug – 1 Sep · round the clock". */
    public function scheduleLabel(): string
    {
        if (! $this->isConfigured()) {
            return '';
        }

        $first = $this->firstStart();
        $last = $this->lastEnd()->copy()->subDay();

        return $this->days === 1
            ? $first->format('j M Y')
            : $first->format('j M').' – '.$last->format('j M Y');
    }

    /**
     * Constrain a query to a set of local windows, converting to UTC for the
     * database. An empty set matches nothing rather than everything.
     */
    public function scopeToWindows($query, string $column, array $windows)
    {
        if (! $windows) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($sub) use ($column, $windows) {
            foreach ($windows as $window) {
                $sub->orWhereBetween($column, [
                    $window['start']->copy()->utc(),
                    $window['end']->copy()->utc(),
                ]);
            }
        });
    }
}
