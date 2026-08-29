<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TeacherActivityController extends Controller
{
    private const LOGIN_ACTION = 'teacher.logged_in';

    private const ONLINE_MINUTES = 5;

    private const RANGES = [7, 30, 90];

    /**
     * Time-series aggregates are cached briefly so a refresh (or several
     * admins on the page at once) doesn't re-run the GROUP BYs. The live
     * counters below are single indexed COUNTs and stay uncached so
     * "online now" is always truthful.
     */
    private const CACHE_SECONDS = 60;

    public function index(Request $request): View
    {
        $days = (int) $request->query('days', 30);

        if (! in_array($days, self::RANGES, true)) {
            $days = 30;
        }

        $since = now()->startOfDay()->subDays($days - 1);
        $accounts = $this->accountStats();

        return view('admin.teacher-activity', [
            'days' => $days,
            'ranges' => self::RANGES,
            'onlineMinutes' => self::ONLINE_MINUTES,
            'live' => $accounts['live'],
            'buckets' => $accounts['buckets'],
            'series' => Cache::remember(
                "teacher-activity:series:{$days}",
                self::CACHE_SECONDS,
                fn () => $this->dailySeries($since, $days),
            ),
            'hourly' => Cache::remember(
                "teacher-activity:hourly:{$days}",
                self::CACHE_SECONDS,
                fn () => $this->hourlyDistribution($since),
            ),
            'period' => Cache::remember(
                "teacher-activity:period:{$days}",
                self::CACHE_SECONDS,
                fn () => $this->periodTotals($since, $days),
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
     * Every teacher-account figure on the page — headline counters and the
     * mutually exclusive engagement bands — in two queries: one conditional
     * aggregate over users, one over teachers. Kept uncached so the live
     * numbers are always truthful.
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
     * Daily logins and the number of distinct teachers behind them, with
     * empty days filled in so the line has no false gaps.
     */
    private function dailySeries(Carbon $since, int $days): array
    {
        $logins = ActivityLog::where('action', self::LOGIN_ACTION)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS logins, COUNT(DISTINCT user_id) AS teachers')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $applications = Application::where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($since, $logins, $applications) {
                $date = $since->copy()->addDays($offset);
                $key = $date->toDateString();
                $row = $logins->get($key);

                return [
                    'date' => $key,
                    'label' => $date->format('M j'),
                    'logins' => (int) ($row->logins ?? 0),
                    'teachers' => (int) ($row->teachers ?? 0),
                    'applications' => (int) ($applications->get($key) ?? 0),
                ];
            })
            ->all();
    }

    /**
     * When during the day teachers actually show up.
     */
    private function hourlyDistribution(Carbon $since): array
    {
        $counts = ActivityLog::where('action', self::LOGIN_ACTION)
            ->where('created_at', '>=', $since)
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

    /**
     * Period totals plus the same window immediately before it, so each
     * figure can carry an honest change indicator.
     */
    private function periodTotals(Carbon $since, int $days): array
    {
        $previousSince = $since->copy()->subDays($days);

        $logins = ActivityLog::where('action', self::LOGIN_ACTION);

        $current = (clone $logins)->where('created_at', '>=', $since)
            ->selectRaw('COUNT(*) AS logins, COUNT(DISTINCT user_id) AS teachers')
            ->first();

        $previous = (clone $logins)->whereBetween('created_at', [$previousSince, $since])
            ->selectRaw('COUNT(*) AS logins, COUNT(DISTINCT user_id) AS teachers')
            ->first();

        return [
            'logins' => (int) $current->logins,
            'teachers' => (int) $current->teachers,
            'applications' => Application::where('created_at', '>=', $since)->count(),
            'previousLogins' => (int) $previous->logins,
            'previousTeachers' => (int) $previous->teachers,
            'previousApplications' => Application::whereBetween('created_at', [$previousSince, $since])->count(),
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
            ->whereIn('action', [self::LOGIN_ACTION, 'teacher.registered', 'application.submitted', 'application.updated'])
            ->latest()
            ->limit(12)
            ->get();
    }
}
