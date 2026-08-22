<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\School;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, string $description, ?School $school = null, array $metadata = [], ?string $url = null): ActivityLog
    {
        if ($url) {
            $metadata['url'] = $url;
        }

        $log = ActivityLog::create([
            'user_id' => Auth::id(),
            'school_id' => $school?->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        $admins = User::where('role', 'admin')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new AdminActivityNotification($log));
        }

        return $log;
    }
}
