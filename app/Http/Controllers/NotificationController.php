<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(AppNotification $notification)
    {
        if ($notification->user_id === auth()->id()) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount()
    {
        return response()->json([
            'unread_count' => auth()->user()->unreadNotifications()->count(),
            'unread_emails_count' => \App\Models\MailboxMessage::where('receiver_id', auth()->id())
                ->where('is_read', false)
                ->whereNull('receiver_deleted_at')
                ->count(),
            'latest_notifications' => auth()->user()->notifications()->latest()->take(10)->get()->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => \Illuminate\Support\Str::limit($notif->message, 60),
                    'url' => $notif->url ?? '#',
                    'is_unread' => is_null($notif->read_at),
                ];
            })
        ]);
    }
}
