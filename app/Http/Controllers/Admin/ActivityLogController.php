<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\LocalTime;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $clock = LocalTime::current();

        return view('admin.activity', [
            'clock' => $clock,
            'breakdown' => $this->breakdown($clock),
            'logs' => ActivityLog::with(['user', 'school'])->latest()->paginate(30),
        ]);
    }

    /**
     * Every action type ever recorded, with its share of the log. This lives
     * here rather than on the activity dashboard because it describes the log
     * itself, not teacher behaviour during the expo.
     */
    private function breakdown(LocalTime $clock): array
    {
        $rows = ActivityLog::selectRaw('action, COUNT(*) AS total, MIN(created_at) AS first_at, MAX(created_at) AS last_at')
            ->groupBy('action')
            ->orderByDesc('total')
            ->get();

        $grand = max(1, (int) $rows->sum('total'));

        return $rows->map(fn ($r) => [
            'action' => $r->action,
            'label' => ucfirst(str_replace(['.', '_'], [' · ', ' '], $r->action)),
            'total' => (int) $r->total,
            'share' => round((int) $r->total / $grand * 100, 1),
            'firstAt' => $clock->local(Carbon::parse($r->first_at)),
            'lastAt' => $clock->local(Carbon::parse($r->last_at)),
        ])->all();
    }
}
