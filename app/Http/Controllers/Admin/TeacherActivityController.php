<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Teacher;
use App\Models\User;
use App\Support\ExpoWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TeacherActivityController extends Controller
{
    private const LOGIN_ACTION = 'teacher.logged_in';

    private const ONLINE_MINUTES = 5;

    /**
     * Time-series aggregates are cached briefly so a refresh (or several
     * admins on the page at once) doesn't re-run the GROUP BYs. The live
     * counters stay uncached so "online now" is always truthful.
     */
    private const CACHE_SECONDS = 60;

    public function index(Request $request): View
    {
        $expo = ExpoWindow::current();
        $range = $this->resolveRange($request->query('range'), $expo);
        $accounts = $this->accountStats();

        $cacheKey = 'teacher-activity:'.$range['key'].':'.$range['since']->timestamp;

        return view('admin.teacher-activity', [
            'expo' => $expo,
            'range' => $range,
            'rangeOptions' => $this->rangeOptions($expo),
            'onlineMinutes' => self::ONLINE_MINUTES,
            'live' => $accounts['live'],
            'buckets' => $accounts['buckets'],
            'series' => Cache::remember(
                $cacheKey.':series',
                self::CACHE_SECONDS,
                fn () => $this->timeSeries($range['since'], $range['until'], $range['granularity']),
            ),
            'hourly' => Cache::remember(
                $cacheKey.':hourly',
                self::CACHE_SECONDS,
                fn () => $this->hourlyDistribution($range['since'], $range['until']),
            ),
            'totals' => Cache::remember(
                $cacheKey.':totals',
                self::CACHE_SECONDS,
                fn () => $this->periodTotals($range['since'], $range['until']),
            ),
            'actionBreakdown' => Cache::remember(
                $cacheKey.':actions',
                self::CACHE_SECONDS,
                fn () => $this->activityByType($range['since'], $range['until']),
            ),
            'preExpo' => Cache::remember(
                'teacher-activity:pre-expo',
                self::CACHE_SECONDS,
                fn () => $this->preExpoSummary($expo),
            ),
            'topTeachers' => Cache::remember(
                'teacher-activity:top',
                self::CACHE_SECONDS,
                fn () => $this->topTeachers(),
            ),
            'recent' => $this->recentActivity(),
        ]);
    }

    /**
     * The expo is three days long, so the useful windows are the event itself
     * and the last day — not calendar months. "All time" reaches back over the
     * setup period so nothing recorded before the doors opened is hidden.
     */
    private function rangeOptions(ExpoWindow $expo): array
    {
        $options = [];

        if ($expo->isConfigured()) {
            $options['expo'] = $expo->days.'-day expo';
        }

        $options['24h'] = 'Last 24 hours';
        $options['all'] = 'All time';

        return $options;
    }

    private function resolveRange(?string $key, ExpoWindow $expo): array
    {
        $options = $this->rangeOptions($expo);
        $key = array_key_exists((string) $key, $options) ? (string) $key : array_key_first($options);

        if ($key === 'expo' && $expo->isConfigured()) {
            $since = $expo->start->copy();
            $until = $expo->hasEnded() ? $expo->end() : now();

            return [
                'key' => 'expo',
                'label' => $options['expo'],
                'since' => $since,
                'until' => $until,
                // A 3-day window needs hour resolution; days would give 3 points.
                'granularity' => 'hour',
            ];
        }

        if ($key === '24h') {
            return [
                'key' => '24h',
                'label' => $options['24h'],
                'since' => now()->subDay()->startOfHour(),
                'until' => now(),
                'granularity' => 'hour',
            ];
        }

        return [
            'key' => 'all',
            'label' => $options['all'],
            'since' => $this->earliestRecord(),
            'until' => now(),
            'granularity' => 'day',
        ];
    }

    /**
     * The first thing that ever happened on the site, so "all time" genuinely
     * covers the pre-expo setup history rather than an arbitrary cutoff.
     */
    private function earliestRecord(): Carbon
    {
        $earliestLog = ActivityLog::min('created_at');
        $earliestUser = User::min('created_at');

        $candidates = array_filter([$earliestLog, $earliestUser]);

        return $candidates
            ? Carbon::parse(min($candidates))->startOfDay()
            : now()->subDays(7)->startOfDay();
    }

    /**
     * Every teacher-account figure — headline counters and the mutually
     * exclusive engagement bands — in two conditional aggregates.
     */
    private function accountStats(): array
    {
        $online = now()->subMinutes(self::ONLINE_MINUTES);
        $startOfDay = now()->startOfDay();
        $week = now()->subDays(7);
        $month = now()->subDays(30);

        $u = User::where('role', 'teacher')
            ->selectRaw('
                COUNT(*) AS total,
                SUM(CASE WHEN last_seen_at >= ? THEN 1 ELSE 0 END) AS online,
                SUM(CASE WHEN last_login_at >= ? THEN 1 ELSE 0 END) AS today_logins,
                SUM(CASE WHEN last_seen_at >= ? THEN 1 ELSE 0 END) AS week_seen,
                SUM(CASE WHEN last_login_at IS NULL THEN 1 ELSE 0 END) AS never_logged_in,
                SUM(CASE WHEN last_seen_at >= ? AND last_seen_at < ? THEN 1 ELSE 0 END) AS band_today,
                SUM(CASE WHEN last_seen_at >= ? AND last_seen_at < ? THEN 1 ELSE 0 END) AS band_week,
                SUM(CASE WHEN last_seen_at >= ? AND last_seen_at < ? THEN 1 ELSE 0 END) AS band_month,
                SUM(CASE WHEN last_login_at IS NOT NULL AND (last_seen_at IS NULL OR last_seen_at < ?) THEN 1 ELSE 0 END) AS band_dormant
            ', [
                $online,
                $startOfDay,
                $week,
                $startOfDay, $online,
                $week, $startOfDay,
                $month, $week,
                $month,
            ])
            ->first();

        $t = Teacher::selectRaw("
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) AS suspended
            ")->first();

        return [
            'live' => [
                'online' => (int) $u->online,
                'today' => (int) $u->today_logins,
                'week' => (int) $u->week_seen,
                'total' => (int) $u->total,
                'neverLoggedIn' => (int) $u->never_logged_in,
                'activeAccounts' => (int) $t->active,
                'suspended' => (int) $t->suspended,
                'lastHourLogins' => ActivityLog::where('action', self::LOGIN_ACTION)
                    ->where('created_at', '>=', now()->subHour())
                    ->count(),
            ],
            'buckets' => [
                ['key' => 'online', 'label' => 'Online now', 'count' => (int) $u->online],
                ['key' => 'today', 'label' => 'Earlier today', 'count' => (int) $u->band_today],
                ['key' => 'week', 'label' => 'This week', 'count' => (int) $u->band_week],
                ['key' => 'month', 'label' => 'This month', 'count' => (int) $u->band_month],
                ['key' => 'dormant', 'label' => 'Dormant (30d+)', 'count' => (int) $u->band_dormant],
                ['key' => 'never', 'label' => 'Never logged in', 'count' => (int) $u->never_logged_in],
            ],
        ];
    }

    /**
     * Logins, distinct teachers, signups and applications per time bucket,
     * with empty buckets filled so the line has no false gaps.
     */
    private function timeSeries(Carbon $since, Carbon $until, string $granularity): array
    {
        $sqlBucket = $granularity === 'hour'
            ? "DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')"
            : 'DATE(created_at)';

        $logins = ActivityLog::where('action', self::LOGIN_ACTION)
            ->whereBetween('created_at', [$since, $until])
            ->selectRaw("{$sqlBucket} AS bucket, COUNT(*) AS logins, COUNT(DISTINCT user_id) AS teachers")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $signups = User::where('role', 'teacher')
            ->whereBetween('created_at', [$since, $until])
            ->selectRaw("{$sqlBucket} AS bucket, COUNT(*) AS total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $applications = Application::whereBetween('created_at', [$since, $until])
            ->selectRaw("{$sqlBucket} AS bucket, COUNT(*) AS total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $out = [];
        $cursor = $granularity === 'hour' ? $since->copy()->startOfHour() : $since->copy()->startOfDay();
        $guard = 0;

        while ($cursor->lessThanOrEqualTo($until) && $guard++ < 2000) {
            $key = $granularity === 'hour'
                ? $cursor->format('Y-m-d H:00:00')
                : $cursor->format('Y-m-d');

            $row = $logins->get($key);

            $out[] = [
                'bucket' => $key,
                'label' => $granularity === 'hour' ? $cursor->format('M j g a') : $cursor->format('M j'),
                'short' => $granularity === 'hour' ? $cursor->format('ga') : $cursor->format('M j'),
                'logins' => (int) ($row->logins ?? 0),
                'teachers' => (int) ($row->teachers ?? 0),
                'signups' => (int) ($signups->get($key) ?? 0),
                'applications' => (int) ($applications->get($key) ?? 0),
            ];

            $granularity === 'hour' ? $cursor->addHour() : $cursor->addDay();
        }

        return $out;
    }

    private function hourlyDistribution(Carbon $since, Carbon $until): array
    {
        $counts = ActivityLog::where('action', self::LOGIN_ACTION)
            ->whereBetween('created_at', [$since, $until])
            ->selectRaw('HOUR(created_at) AS hour, COUNT(*) AS total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        return collect(range(0, 23))
            ->map(fn (int $hour) => [
                'hour' => $hour,
                'label' => Carbon::createFromTime($hour)->format('ga'),
                'total' => (int) ($counts->get($hour) ?? 0),
            ])
            ->all();
    }

    private function periodTotals(Carbon $since, Carbon $until): array
    {
        $logins = ActivityLog::where('action', self::LOGIN_ACTION)
            ->whereBetween('created_at', [$since, $until])
            ->selectRaw('COUNT(*) AS logins, COUNT(DISTINCT user_id) AS teachers')
            ->first();

        return [
            'logins' => (int) $logins->logins,
            'teachers' => (int) $logins->teachers,
            'signups' => User::where('role', 'teacher')->whereBetween('created_at', [$since, $until])->count(),
            'applications' => Application::whereBetween('created_at', [$since, $until])->count(),
        ];
    }

    /**
     * Every action type recorded in the window — not just teacher ones — so
     * the older setup-period entries are visible rather than filtered away.
     */
    private function activityByType(Carbon $since, Carbon $until): array
    {
        return ActivityLog::whereBetween('created_at', [$since, $until])
            ->selectRaw('action, COUNT(*) AS total, MAX(created_at) AS latest')
            ->groupBy('action')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'action' => $r->action,
                'label' => ucfirst(str_replace(['.', '_'], [' · ', ' '], $r->action)),
                'total' => (int) $r->total,
                'latest' => Carbon::parse($r->latest),
            ])
            ->all();
    }

    /**
     * What was already on record before the doors opened, so the expo numbers
     * are never mistaken for the site's whole history.
     */
    private function preExpoSummary(ExpoWindow $expo): ?array
    {
        if (! $expo->isConfigured()) {
            return null;
        }

        $logs = ActivityLog::where('created_at', '<', $expo->start)->count();

        if ($logs === 0) {
            return null;
        }

        return [
            'logs' => $logs,
            'teachers' => User::where('role', 'teacher')->where('created_at', '<', $expo->start)->count(),
            'schools' => User::where('role', 'school')->where('created_at', '<', $expo->start)->count(),
            'firstAt' => Carbon::parse(ActivityLog::min('created_at')),
        ];
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
            ->limit(15)
            ->get();
    }
}
