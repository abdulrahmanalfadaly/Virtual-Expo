<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Support\ExpoWindow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TeacherActivityController extends Controller
{
    private const LOGIN_ACTION = 'teacher.logged_in';

    /**
     * How recently a teacher must have been seen to count as "using the site
     * right now". Paired with the one-minute write throttle in TrackLastSeen,
     * this keeps the figure accurate to within about a minute.
     */
    private const ONLINE_MINUTES = 3;

    private const CACHE_SECONDS = 45;

    public function index(Request $request): View
    {
        $expo = ExpoWindow::current();
        $range = $this->resolveRange($request->query('range'), $expo);

        $data = [
            'expo' => $expo,
            'range' => $range,
            'rangeOptions' => $this->rangeOptions($expo),
            'onlineMinutes' => self::ONLINE_MINUTES,
            'onlineNow' => $this->onlineNow(),
            'accounts' => $this->accountTotals(),
        ];

        if ($range['key'] !== 'live') {
            $key = 'ta:'.$range['key'].':'.$this->windowSignature($range['windows']);

            $data['metrics'] = Cache::remember(
                $key.':m',
                self::CACHE_SECONDS,
                fn () => $this->windowMetrics($range['windows']),
            );
        }

        if ($range['showCharts']) {
            $key = 'ta:'.$range['key'].':'.$this->windowSignature($range['windows']);

            $data['series'] = Cache::remember(
                $key.':s',
                self::CACHE_SECONDS,
                fn () => $this->loginSeries($expo, $range['windows'], $range['bucketMinutes']),
            );

            $data['timeOfDay'] = Cache::remember(
                $key.':t',
                self::CACHE_SECONDS,
                fn () => $this->timeOfDay($expo, $range['windows']),
            );

            $data['topTeachers'] = Cache::remember(
                'ta:top',
                self::CACHE_SECONDS,
                fn () => $this->topTeachers(),
            );

            $data['recent'] = $this->recentActivity();

            $data['highlights'] = Cache::remember(
                'ta:highlights:'.$this->windowSignature($expo->elapsedSessions()),
                self::CACHE_SECONDS,
                fn () => $this->highlights($expo),
            );
        }

        return view('admin.teacher-activity', $data);
    }

    /**
     * Tiny polling endpoint behind the live counter — one indexed COUNT, no
     * view rendering, so it can be hit every few seconds without cost.
     */
    public function live(): JsonResponse
    {
        $expo = ExpoWindow::current();

        return response()->json([
            'online' => $this->onlineNow(),
            'total' => User::where('role', 'teacher')->count(),
            'isLive' => $expo->isLive(),
            'status' => $expo->statusLabel(),
            'at' => $expo->now()->format('g:i:s A'),
        ]);
    }

    private function rangeOptions(ExpoWindow $expo): array
    {
        return [
            'live' => [
                'label' => 'Live',
                'hint' => 'Who is on the site this second',
            ],
            '5min' => [
                'label' => 'Last 5 minutes',
                'hint' => 'The last few minutes of traffic',
            ],
            'hour' => [
                'label' => 'Last hour',
                'hint' => 'The past 60 minutes in detail',
            ],
            'today' => [
                'label' => 'Today',
                'hint' => $expo->isConfigured() ? 'Day '.($expo->currentDay() ?? '—').' · midnight to now' : 'Today, midnight to now',
            ],
            'all' => [
                'label' => 'All time',
                'hint' => 'The whole expo so far',
            ],
        ];
    }

    /**
     * Each range resolves to a set of local windows plus how much detail to
     * render. "Live" carries no window at all — it is a single instant.
     */
    private function resolveRange(?string $key, ExpoWindow $expo): array
    {
        $options = $this->rangeOptions($expo);
        $key = array_key_exists((string) $key, $options) ? (string) $key : 'live';

        $now = $expo->now();

        $base = [
            'key' => $key,
            'label' => $options[$key]['label'],
            'hint' => $options[$key]['hint'],
            'windows' => [],
            'bucketMinutes' => 5,
            'showCharts' => false,
            'showAccountsShare' => false,
            'showActive' => false,
        ];

        return match ($key) {
            'live' => $base,

            '5min' => array_merge($base, [
                'windows' => [['start' => $now->copy()->subMinutes(5), 'end' => $now->copy()]],
                'showActive' => true,
            ]),

            'hour' => array_merge($base, [
                'windows' => [['start' => $now->copy()->subHour(), 'end' => $now->copy()]],
                'bucketMinutes' => 5,
                'showCharts' => true,
                'showActive' => true,
            ]),

            'today' => array_merge($base, [
                'windows' => $this->todayWindows($expo),
                'bucketMinutes' => 60,
                'showCharts' => true,
                'showAccountsShare' => true,
            ]),

            default => array_merge($base, [
                'windows' => $expo->elapsedSessions(),
                'bucketMinutes' => $this->bucketForSpan($expo->totalMinutes()),
                'showCharts' => true,
                'showAccountsShare' => true,
            ]),
        };
    }

    /**
     * Keep multi-day charts readable: the smallest round interval that fits
     * the whole span into roughly four dozen points.
     */
    private function bucketForSpan(int $totalMinutes): int
    {
        foreach ([60, 120, 180, 240, 360, 720, 1440] as $interval) {
            if ($totalMinutes / $interval <= 48) {
                return $interval;
            }
        }

        return 1440;
    }

    /** Today's expo day so far, midnight to now; empty before it starts. */
    private function todayWindows(ExpoWindow $expo): array
    {
        $session = $expo->todaySession();

        if (! $session) {
            return [];
        }

        $now = $expo->now();

        if ($session['start']->greaterThan($now)) {
            return [];
        }

        return [[
            'day' => $session['day'],
            'start' => $session['start']->copy(),
            'end' => $session['end']->greaterThan($now) ? $now->copy() : $session['end']->copy(),
        ]];
    }

    private function windowSignature(array $windows): string
    {
        if (! $windows) {
            return 'none';
        }

        $parts = array_map(
            fn ($w) => $w['start']->timestamp.'-'.$w['end']->timestamp,
            $windows,
        );

        return substr(md5(implode('|', $parts)), 0, 12);
    }

    private function onlineNow(): int
    {
        return User::where('role', 'teacher')
            ->where('last_seen_at', '>=', now()->subMinutes(self::ONLINE_MINUTES))
            ->count();
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

    /** The four figures every windowed range reports. */
    private function windowMetrics(array $windows): array
    {
        $expo = ExpoWindow::current();

        $logins = $expo->scopeToWindows(
            ActivityLog::where('action', self::LOGIN_ACTION),
            'created_at',
            $windows,
        )->selectRaw('COUNT(*) AS total, COUNT(DISTINCT user_id) AS teachers')->first();

        return [
            'active' => $expo->scopeToWindows(
                User::where('role', 'teacher'),
                'last_seen_at',
                $windows,
            )->count(),
            'logins' => (int) $logins->total,
            'loginTeachers' => (int) $logins->teachers,
            'signups' => $expo->scopeToWindows(
                User::where('role', 'teacher'),
                'created_at',
                $windows,
            )->count(),
            'applications' => $expo->scopeToWindows(
                Application::query(),
                'created_at',
                $windows,
            )->count(),
        ];
    }

    /**
     * Logins per time bucket. Signups already emit a login row of their own,
     * so this single series covers both, exactly as the dashboard claims.
     *
     * Buckets are aligned to the organiser's local clock via a fixed-offset
     * shift, using TIMESTAMPDIFF rather than UNIX_TIMESTAMP so the result is
     * independent of the database session's own timezone.
     */
    private function loginSeries(ExpoWindow $expo, array $windows, int $bucketMinutes): array
    {
        if (! $windows) {
            return [];
        }

        $offset = $expo->now()->getOffset();
        $size = $bucketMinutes * 60;

        $expr = "FLOOR((TIMESTAMPDIFF(SECOND, '1970-01-01 00:00:00', created_at) + {$offset}) / {$size})";

        $rows = $expo->scopeToWindows(
            ActivityLog::where('action', self::LOGIN_ACTION),
            'created_at',
            $windows,
        )
            ->selectRaw("{$expr} AS bucket, COUNT(*) AS total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $out = [];

        foreach ($windows as $window) {
            $cursor = $this->floorToBucket($window['start'], $bucketMinutes);
            $guard = 0;

            while ($cursor->lessThan($window['end']) && $guard++ < 2000) {
                $index = (int) floor(($cursor->getTimestamp() + $offset) / $size);

                $out[] = [
                    'at' => $cursor->copy(),
                    'label' => $this->bucketLabel($cursor, $bucketMinutes),
                    'day' => $window['day'] ?? null,
                    'logins' => (int) ($rows[$index] ?? 0),
                ];

                $cursor = $cursor->copy()->addMinutes($bucketMinutes);
            }
        }

        return $out;
    }

    private function floorToBucket(Carbon $moment, int $bucketMinutes): Carbon
    {
        $copy = $moment->copy()->seconds(0);

        if ($bucketMinutes >= 60) {
            return $copy->minutes(0);
        }

        return $copy->minutes(intdiv($copy->minute, $bucketMinutes) * $bucketMinutes);
    }

    private function bucketLabel(Carbon $at, int $bucketMinutes): string
    {
        return $bucketMinutes >= 60
            ? $at->format('D g A')
            : $at->format('g:i A');
    }

    /**
     * What time of day teachers arrive, folded onto a single 24-hour clock so
     * the chart answers "what hour", not "which date".
     *
     * The hour is derived with a fixed-offset shift rather than the database's
     * own timezone, keeping it correct regardless of server configuration.
     */
    private function timeOfDay(ExpoWindow $expo, array $windows): array
    {
        $totals = [];
        for ($h = 0; $h < 24; $h++) {
            $totals[$h] = [
                'label' => Carbon::createFromTime($h)->format('ga'),
                'total' => 0,
            ];
        }

        if (! $windows) {
            return array_values($totals);
        }

        $offset = $expo->now()->getOffset();

        $expr = "MOD(FLOOR((TIMESTAMPDIFF(SECOND, '1970-01-01 00:00:00', created_at) + {$offset}) / 3600), 24)";

        $rows = $expo->scopeToWindows(
            ActivityLog::where('action', self::LOGIN_ACTION),
            'created_at',
            $windows,
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
     * Positive, share-ready totals for the whole expo so far. Everything here
     * is a real count — nothing is projected or rounded up.
     */
    private function highlights(ExpoWindow $expo): array
    {
        $windows = $expo->elapsedSessions();

        $logins = $expo->scopeToWindows(
            ActivityLog::where('action', self::LOGIN_ACTION),
            'created_at',
            $windows,
        )->selectRaw('COUNT(*) AS total, COUNT(DISTINCT user_id) AS teachers')->first();

        $timeOfDay = $this->timeOfDay($expo, $windows);
        $peak = collect($timeOfDay)->sortByDesc('total')->first();

        return [
            'teachers' => User::where('role', 'teacher')->count(),
            'signups' => $expo->scopeToWindows(
                User::where('role', 'teacher'),
                'created_at',
                $windows,
            )->count(),
            'logins' => (int) $logins->total,
            'activeTeachers' => (int) $logins->teachers,
            'applications' => $expo->scopeToWindows(
                Application::query(),
                'created_at',
                $windows,
            )->count(),
            'schools' => School::where('is_published', true)->where('status', 'active')->count(),
            'hoursLive' => round($expo->elapsedMinutes() / 60, 1),
            'peakLabel' => $peak && $peak['total'] > 0 ? $peak['label'] : null,
            'peakCount' => $peak['total'] ?? 0,
            'series' => collect($timeOfDay)->pluck('total')->all(),
        ];
    }
}
