<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logType = $request->input('log_type', 'all');

        $logs = ActivityLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($logType === 'whatsapp', function ($q) {
                return $q->where(function ($sq) {
                    $sq->where('action', 'like', '%whatsapp%')
                       ->orWhere('description', 'like', '%whatsapp%');
                });
            })
            ->when($logType === 'email', function ($q) {
                return $q->where(function ($sq) {
                    $sq->where('action', 'email_sent')
                       ->orWhere('action', 'like', '%mail%')
                       ->orWhere('description', 'like', '%email%');
                });
            })
            ->latest()
            ->paginate(50);

        return view('activity-logs.index', compact('logs', 'logType'));
    }
}
