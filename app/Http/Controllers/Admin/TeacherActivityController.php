<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Teacher;
use App\Models\User;
use App\Support\LocalTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TeacherActivityController extends Controller
{
    private const LOGIN_ACTION = 'teacher.logged_in';

    /**
     * How recently a teacher must have been seen to count as active. Paired
     * with the one-minute write throttle in TrackLastSeen, this keeps the
     * figure accurate to within about a minute.
     */
    private const ONLINE_MINUTES = 3;

    private const CACHE_SECONDS = 45;

    /** Bucket sizes in minutes, smallest first, for adaptive chart detail. */
    private const BUCKET_LADDER = [1, 5, 10, 15, 30, 60, 120, 180, 360, 720, 1440, 2880, 10080];

    public function index(Request $request): View
    {
        $clock = LocalTime::current();
        $range = $this->resolveRange($request, $clock);

        $signature = substr(md5($range['key'].$range['start']->timestamp.'-'.$range['end']->timestamp), 0, 12);

        $data = [
            'clock' => $clock,
            'range' => $range,
            'rangeOptions' => $this->rangeOptions(),
            'onlineMinutes' => self::ONLINE_MINUTES,
            'accounts' => $this->accountTotals(),
            'metrics' => Cache::remember(
                "ta:{$signature}:m",
                self::CACHE_SECONDS,
                fn () => $this->windowMetrics($clock, $range),
            ),
        ];

        if ($range['showCharts']) {
            $data['series'] = Cache::remember(
                "ta:{$signature}:s",
                self::CACHE_SECONDS,
                fn () => $this->loginSeries($clock, $range),
            );

            $data['timeOfDay'] = Cache::remember(
                "ta:{$signature}:t",
                self::CACHE_SECONDS,
                fn () => $this->timeOfDay($clock, $range),
            );

            $data['topTeachers'] = Cache::remember(
                'ta:top',
                self::CACHE_SECONDS,
                fn () => $this->topTeachers(),
            );

            $data['recent'] = $this->recentActivity();

            $data['highlights'] = Cache::remember(
                "ta:{$signature}:h",
                self::CACHE_SECONDS,
                fn () => $this->highlights($clock, $range),
            );
        }

        return view('admin.teacher-activity', $data);
    }

    private function rangeOptions(): array
    {
        return [
            '5min' => ['label' => 'Last 5 minutes', 'hint' => 'The last few minutes of traffic'],
            'hour' => ['label' => 'Last hour', 'hint' => 'The past 60 minutes in detail'],
            'today' => ['label' => 'Today', 'hint' => 'Midnight until now'],
            'custom' => ['label' => 'Custom range', 'hint' => 'Pick any dates and times'],
        ];
    }

    /**
     * Every range resolves to one contiguous local window. The custom range is
     * driven by admin-typed wall-clock values, so it is defended against
     * missing, malformed, reversed, zero-length and future inputs.
     */
    private function resolveRange(Request $request, LocalTime $clock): array
    {
        $options = $this->rangeOptions();
        $key = (string) $request->query('range', 'today');
        $key = array_key_exists($key, $options) ? $key : 'today';

        $base = [
            'key' => $key,
            'label' => $options[$key]['label'],
            'notice' => null,
            'fromInput' => null,
            'toInput' => null,
        ];

        if ($key === 'custom') {
            return array_merge($base, $this->resolveCustomRange($request, $clock));
        }

        $now = $clock->now();

        [$start, $end] = match ($key) {
            '5min' => [$now->copy()->subMinutes(5), $now->copy()],
            'hour' => [$now->copy()->subHour(), $now->copy()],
            default => [$now->copy()->startOfDay(), $now->copy()],
        };

        return array_merge($base, [
            'start' => $start,
            'end' => $end,
            'bucketMinutes' => $this->bucketForSpan((int) $start->diffInMinutes($end, absolute: true)),
            'showActive' => in_array($key, ['5min', 'hour'], true),
            'showAccountsShare' => $key === 'today',
            'showCharts' => in_array($key, ['hour', 'today'], true),
            'description' => $start->format('j M, g:i A').' → '.$end->format('j M, g:i A'),
        ]);
    }

    private function resolveCustomRange(Request $request, LocalTime $clock): array
    {
        $now = $clock->now();

        $from = $clock->parseLocal($request->query('from'));
        $to = $clock->parseLocal($request->query('to'));
        $notice = null;

        // Nothing chosen yet, or only one side given: fall back to a window
        // that still shows something useful rather than an error.
        if (! $from && ! $to) {
            $from = $now->copy()->subDays(7)->startOfDay();
            $to = $now->copy();
        } elseif (! $from) {
            $from = $to->copy()->subDays(7);
        } elseif (! $to) {
            $to = $from->copy()->addDays(7);

            if ($to->greaterThan($now)) {
                $to = $now->copy();
            }
        }

        // Reversed input is a slip, not an error — read it the way it was meant.
        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
            $notice = 'Start was after end, so the two were swapped.';
        }

        // A zero-length window would render an empty chart with no axis.
        if ($from->equalTo($to)) {
            $to = $to->copy()->addMinute();
        }

        $entirelyFuture = $from->greaterThan($now);

        if ($entirelyFuture) {
            $notice = 'That range is in the future — nothing has been recorded yet.';
        }

        $span = (int) $from->diffInMinutes($to, absolute: true);

        return [
            'start' => $from,
            'end' => $to,
            'bucketMinutes' => $this->bucketForSpan($span),
            'showActive' => $span <= 180,
            'showAccountsShare' => true,
            'showCharts' => ! $entirelyFuture,
            'notice' => $notice,
            'fromInput' => $from->format('Y-m-d\TH:i'),
            'toInput' => $to->format('Y-m-d\TH:i'),
            'description' => $from->format('j M Y, g:i A').' → '.$to->format('j M Y, g:i A'),
        ];
    }

    /**
     * The smallest round interval that fits the span into roughly four dozen
     * points, so a five-minute window and a five-month one both stay readable.
     */
    private function bucketForSpan(int $totalMinutes): int
    {
        $totalMinutes = max(1, $totalMinutes);

        foreach (self::BUCKET_LADDER as $interval) {
            if ($totalMinutes / $interval <= 48) {
                return $interval;
            }
        }

        return self::BUCKET_LADDER[count(self::BUCKET_LADDER) - 1];
    }

    private function accountTotals(): array
    {
        $u = User::where('role', 'teacher')
            ->selectRaw('COUNT(*) AS total, SUM(CASE WHEN last_login_at IS NULL THEN 1 ELSE 0 END) AS never_logged_in')
            ->first();

        $total = (int) $u->total;
        $never = (int) $u->never_logged_in;

        return [
            'total' => $total,
            'neverLoggedIn' => $never,
            'neverPercent' => $total > 0 ? round($never / $total * 100) : 0,
            'loggedInEver' => $total - $never,
        ];
    }

    private function windowMetrics(LocalTime $clock, array $range): array
    {
        $start = $range['start'];
        $end = $range['end'];

        $logins = $clock->scopeToWindow(
            ActivityLog::where('action', self::LOGIN_ACTION),
            'created_at',
            $start,
            $end,
        )->selectRaw('COUNT(*) AS total, COUNT(DISTINCT user_id) AS teachers')->first();

        return [
            'active' => $clock->scopeToWindow(
                User::where('role', 'teacher'),
                'last_seen_at',
                $start,
                $end,
            )->count(),
            'logins' => (int) $logins->total,
            'loginTeachers' => (int) $logins->teachers,
            'signups' => $clock->scopeToWindow(
                User::where('role', 'teacher'),
                'created_at',
                $start,
                $end,
            )->count(),
            'applications' => $clock->scopeToWindow(
                Application::query(),
                'created_at',
                $start,
                $end,
            )->count(),
        ];
    }

    /**
     * Sign-ins per time bucket. Registering signs a teacher in, so new
     * sign-ups are already represented in this single series.
     *
     * Buckets are aligned to the local clock with a fixed-offset shift, using
     * TIMESTAMPDIFF rather than UNIX_TIMESTAMP so the result does not depend
     * on the database session's own timezone.
     */
    private function loginSeries(LocalTime $clock, array $range): array
    {
        $start = $range['start'];
        $end = $range['end'];
        $bucketMinutes = $range['bucketMinutes'];

        $offset = $clock->offsetSeconds();
        $size = $bucketMinutes * 60;

        $expr = "FLOOR((TIMESTAMPDIFF(SECOND, '1970-01-01 00:00:00', created_at) + {$offset}) / {$size})";

        $rows = $clock->scopeToWindow(
            ActivityLog::where('action', self::LOGIN_ACTION),
            'created_at',
            $start,
            $end,
        )
            ->selectRaw("{$expr} AS bucket, COUNT(*) AS total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $out = [];
        $cursor = $this->floorToBucket($start, $bucketMinutes);
        $guard = 0;

        while ($cursor->lessThan($end) && $guard++ < 400) {
            $index = (int) floor(($cursor->getTimestamp() + $offset) / $size);

            $out[] = [
                'label' => $this->bucketLabel($cursor, $bucketMinutes),
                'logins' => (int) ($rows[$index] ?? 0),
            ];

            $cursor = $cursor->copy()->addMinutes($bucketMinutes);
        }

        return $out;
    }

    private function floorToBucket(Carbon $moment, int $bucketMinutes): Carbon
    {
        $copy = $moment->copy()->seconds(0);

        if ($bucketMinutes >= 1440) {
            return $copy->startOfDay();
        }

        if ($bucketMinutes >= 60) {
            $hoursPerBucket = max(1, intdiv($bucketMinutes, 60));

            return $copy->minutes(0)->hours(intdiv($copy->hour, $hoursPerBucket) * $hoursPerBucket);
        }

        return $copy->minutes(intdiv($copy->minute, $bucketMinutes) * $bucketMinutes);
    }

    private function bucketLabel(Carbon $at, int $bucketMinutes): string
    {
        return match (true) {
            $bucketMinutes >= 1440 => $at->format('j M'),
            $bucketMinutes >= 60 => $at->format('j M g A'),
            default => $at->format('g:i A'),
        };
    }

    /** What time of day teachers arrive, folded onto a single 24-hour clock. */
    private function timeOfDay(LocalTime $clock, array $range): array
    {
        $totals = [];

        for ($h = 0; $h < 24; $h++) {
            $totals[$h] = ['label' => Carbon::createFromTime($h)->format('ga'), 'total' => 0];
        }

        $offset = $clock->offsetSeconds();
        $expr = "MOD(FLOOR((TIMESTAMPDIFF(SECOND, '1970-01-01 00:00:00', created_at) + {$offset}) / 3600), 24)";

        $rows = $clock->scopeToWindow(
            ActivityLog::where('action', self::LOGIN_ACTION),
            'created_at',
            $range['start'],
            $range['end'],
        )
            ->selectRaw("{$expr} AS hour, COUNT(*) AS total")
            ->groupBy('hour')
            ->pluck('total', 'hour');

        foreach ($rows as $hour => $total) {
            $hour = (int) $hour;

            if (isset($totals[$hour])) {
                $totals[$hour]['total'] = (int) $total;
            }
        }

        return array_values($totals);
    }

    private function topTeachers()
    {
        // select() must come before withCount() — it replaces the select list,
        // and would otherwise drop the count subquery column.
        return Teacher::query()
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->select('teachers.*')
            ->withCount('applications')
            ->with('user:id,name,email,last_login_at,last_seen_at,login_count')
            ->orderByDesc('users.login_count')
            ->orderByDesc('users.last_seen_at')
            ->limit(10)
            ->get();
    }

    private function recentActivity()
    {
        return ActivityLog::with('user:id,name,email')
            ->latest()
            ->limit(12)
            ->get();
    }

    /**
     * The share card: one headline figure and the shape of the activity
     * behind it. Both are real counts for the selected window.
     */
    private function highlights(LocalTime $clock, array $range): array
    {
        $series = $this->loginSeries($clock, $range);

        return [
            'logins' => (int) collect($series)->sum('logins'),
            'series' => collect($series)->pluck('logins')->all(),
            'labels' => collect($series)->pluck('label')->all(),
        ];
    }
}
