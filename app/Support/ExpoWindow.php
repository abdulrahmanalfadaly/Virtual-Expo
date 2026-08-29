<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Carbon;

/**
 * The expo is a short, fixed-length event rather than an always-on site, so
 * almost every activity figure is only meaningful relative to its window.
 * This wraps the two settings that define it and answers the questions the
 * dashboard actually asks: has it started, which day are we on, how long is
 * left.
 */
class ExpoWindow
{
    public function __construct(
        public readonly ?Carbon $start,
        public readonly int $days,
    ) {}

    public static function current(): self
    {
        $start = SiteSetting::get('expo_starts_at');
        $days = (int) SiteSetting::get('expo_days', 3);

        return new self(
            $start ? Carbon::parse($start) : null,
            max(1, $days ?: 3),
        );
    }

    public function isConfigured(): bool
    {
        return $this->start !== null;
    }

    public function end(): ?Carbon
    {
        return $this->start?->copy()->addDays($this->days);
    }

    public function hasStarted(): bool
    {
        return $this->isConfigured() && now()->greaterThanOrEqualTo($this->start);
    }

    public function hasEnded(): bool
    {
        $end = $this->end();

        return $end !== null && now()->greaterThan($end);
    }

    public function isRunning(): bool
    {
        return $this->hasStarted() && ! $this->hasEnded();
    }

    /**
     * Which day of the expo we're on, 1-based and clamped to the window.
     */
    public function currentDay(): ?int
    {
        if (! $this->hasStarted()) {
            return null;
        }

        $day = (int) floor($this->start->diffInDays(now(), absolute: false)) + 1;

        return max(1, min($this->days, $day));
    }

    /**
     * Whole hours since the expo opened — the headline "how far in are we".
     */
    public function hoursElapsed(): int
    {
        if (! $this->hasStarted()) {
            return 0;
        }

        return (int) floor($this->start->diffInHours(now(), absolute: false));
    }

    public function minutesRemaining(): int
    {
        $end = $this->end();

        if ($end === null || $this->hasEnded()) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInMinutes($end, absolute: false)));
    }

    public function progressPercent(): float
    {
        if (! $this->hasStarted()) {
            return 0.0;
        }

        if ($this->hasEnded()) {
            return 100.0;
        }

        $total = $this->start->diffInSeconds($this->end(), absolute: true);

        if ($total <= 0) {
            return 100.0;
        }

        return round(min(100, max(0, ($this->start->diffInSeconds(now(), absolute: true) / $total) * 100)), 1);
    }

    /**
     * A short human status used in the dashboard header.
     */
    public function statusLabel(): string
    {
        if (! $this->isConfigured()) {
            return 'Not scheduled';
        }

        if (! $this->hasStarted()) {
            return 'Starts '.$this->start->diffForHumans();
        }

        if ($this->hasEnded()) {
            return 'Ended '.$this->end()->diffForHumans();
        }

        return 'Live now';
    }

    /**
     * Start of each expo day, for day markers on an hourly chart.
     */
    public function dayBoundaries(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        return collect(range(0, $this->days - 1))
            ->map(fn (int $i) => [
                'day' => $i + 1,
                'at' => $this->start->copy()->addDays($i),
            ])
            ->all();
    }
}
