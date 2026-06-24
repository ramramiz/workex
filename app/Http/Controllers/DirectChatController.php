<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DirectMessage;
use Carbon\Carbon;

class DirectChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get all active users in the same company, excluding current user
        $users = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('status', 'active')
            ->with(['role', 'employee.designation'])
            ->get()
            ->map(function ($u) use ($user) {
                // Calculate unread count from this user
                $u->unread_count = DirectMessage::where('sender_id', $u->id)
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                // Get last message in the thread
                $u->last_message = DirectMessage::where(function ($q) use ($user, $u) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $u->id);
                    })
                    ->orWhere(function ($q) use ($user, $u) {
                        $q->where('sender_id', $u->id)->where('receiver_id', $user->id);
                    })
                    ->latest()
                    ->first();

                return $u;
            })
            ->sortByDesc(function ($u) {
                return $u->last_message ? $u->last_message->created_at->timestamp : 0;
            });

        return view('direct_chat.index', compact('users'));
    }

    public function show(User $user)
    {
        $me = auth()->user();

        // Mark incoming messages as read
        DirectMessage::where('sender_id', $user->id)
            ->where('receiver_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get conversation history
        $messages = DirectMessage::with(['parent.sender', 'sender', 'receiver'])
            ->where(function ($q) use ($me, $user) {
                $q->where('sender_id', $me->id)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($me, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $me->id);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'receiver_id' => $m->receiver_id,
                    'message' => $m->message,
                    'image_url' => $m->image_path ? asset('storage/' . $m->image_path) : null,
                    'formatted_time' => $m->created_at->format('d M Y, h:i A'),
                    'time' => $m->created_at->format('h:i A'),
                    'date' => $m->created_at->format('d M Y'),
                    'is_sent' => $m->sender_id === auth()->id(),
                    'read_at' => $m->read_at ? $m->read_at->format('d M Y, h:i A') : null,
                    'parent_id' => $m->parent_id,
                    'is_edited' => $m->is_edited,
                    'is_pinned' => $m->is_pinned,
                    'is_important' => $m->is_important,
                    'is_editable' => ($m->sender_id === auth()->id() && $m->created_at->diffInMinutes(now()) < 30),
                    'seen_by' => $m->read_at ? [
                        [
                            'name' => $m->receiver->name,
                            'seen_at' => $m->read_at->format('d M Y, h:i A')
                        ]
                    ] : [],
                    'reply_to_message' => $m->parent ? [
                        'sender_name' => $m->parent->sender_id === auth()->id() ? 'You' : $m->parent->sender->name,
                        'message' => $m->parent->message,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'latest_time' => now()->toISOString()
        ]);
    }

    public function send(Request $request, User $user)
    {
        $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'image_data' => 'nullable|string',
            'parent_id' => 'nullable|exists:direct_messages,id',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('direct_messages/' . auth()->id(), 'public');
        } elseif ($request->filled('image_data')) {
            $imageData = $request->image_data;
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);
                if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $imageData = base64_decode($imageData);
                    if ($imageData !== false) {
                        $fileName = 'dm_' . uniqid() . '.' . $type;
                        $path = 'direct_messages/' . auth()->id() . '/' . $fileName;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);
                        $imagePath = $path;
                    }
                }
            }
        }

        if (empty($request->message) && empty($imagePath)) {
            return response()->json(['success' => false, 'message' => 'Cannot send an empty message.'], 422);
        }

        $message = DirectMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'message' => $request->message,
            'image_path' => $imagePath,
            'company_id' => auth()->user()->company_id,
            'parent_id' => $request->parent_id,
        ]);

        $message->load(['parent.sender', 'sender', 'receiver']);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'message' => $message->message,
                'image_url' => $message->image_path ? asset('storage/' . $message->image_path) : null,
                'formatted_time' => $message->created_at->format('d M Y, h:i A'),
                'time' => $message->created_at->format('h:i A'),
                'date' => $message->created_at->format('d M Y'),
                'is_sent' => true,
                'read_at' => null,
                'parent_id' => $message->parent_id,
                'is_edited' => false,
                'is_pinned' => false,
                'is_important' => false,
                'is_editable' => true,
                'seen_by' => [],
                'reply_to_message' => $message->parent ? [
                    'sender_name' => $message->parent->sender_id === auth()->id() ? 'You' : $message->parent->sender->name,
                    'message' => $message->parent->message,
                ] : null,
            ]
        ]);
    }

    public function getUpdates(Request $request)
    {
        $user = auth()->user();
        $since = $request->input('since');

        $query = DirectMessage::where('receiver_id', $user->id)
            ->whereNull('read_at');

        if ($since) {
            $sinceTime = \Carbon\Carbon::parse($since)->setTimezone(config('app.timezone'));
            $query->where('created_at', '>', $sinceTime);
        }

        $newMessages = $query->with(['sender', 'parent.sender', 'receiver'])->orderBy('created_at', 'asc')->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'is_sent' => false,
                'sender_name' => $m->sender->name,
                'sender_avatar' => $m->sender->avatar_url,
                'message' => $m->message,
                'image_url' => $m->image_path ? asset('storage/' . $m->image_path) : null,
                'formatted_time' => $m->created_at->format('d M Y, h:i A'),
                'time' => $m->created_at->format('h:i A'),
                'date' => $m->created_at->format('d M Y'),
                'read_at' => $m->read_at ? $m->read_at->format('d M Y, h:i A') : null,
                'parent_id' => $m->parent_id,
                'is_edited' => $m->is_edited,
                'is_pinned' => $m->is_pinned,
                'is_important' => $m->is_important,
                'is_editable' => ($m->sender_id === auth()->id() && $m->created_at->diffInMinutes(now()) < 30),
                'seen_by' => $m->read_at ? [
                    [
                        'name' => $m->receiver->name,
                        'seen_at' => $m->read_at->format('d M Y, h:i A')
                    ]
                ] : [],
                'reply_to_message' => $m->parent ? [
                    'sender_name' => $m->parent->sender_id === auth()->id() ? 'You' : $m->parent->sender->name,
                    'message' => $m->parent->message,
                ] : null,
            ];
        });

        // Group unread counts by sender_id
        $unreadCounts = DirectMessage::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->select('sender_id', \DB::raw('count(*) as count'))
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id');

        $totalUnread = DirectMessage::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'new_messages' => $newMessages,
            'unread_counts' => $unreadCounts,
            'total_unread' => $totalUnread,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function markAsRead(User $user)
    {
        DirectMessage::where('sender_id', $user->id)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true
        ]);
    }

    public function edit(Request $request, DirectMessage $message)
    {
        abort_if($message->sender_id !== auth()->id(), 403, 'Unauthorized.');
        abort_if($message->created_at->diffInMinutes(now()) >= 30, 400, 'Editing window expired.');

        $request->validate([
            'message' => 'required|string',
        ]);

        $message->message = $request->message;
        $message->is_edited = true;
        $message->save();

        return response()->json([
            'success' => true,
            'message' => $message->message,
        ]);
    }

    public function togglePin(DirectMessage $message)
    {
        $userId = auth()->id();
        abort_if($message->sender_id !== $userId && $message->receiver_id !== $userId, 403, 'Unauthorized.');

        $message->is_pinned = !$message->is_pinned;
        $message->save();

        return response()->json([
            'success' => true,
            'is_pinned' => $message->is_pinned,
        ]);
    }

    public function toggleImportant(DirectMessage $message)
    {
        $userId = auth()->id();
        abort_if($message->sender_id !== $userId && $message->receiver_id !== $userId, 403, 'Unauthorized.');

        $message->is_important = !$message->is_important;
        $message->save();

        return response()->json([
            'success' => true,
            'is_important' => $message->is_important,
        ]);
    }
}
