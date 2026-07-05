<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Webklex\PHPIMAP\ClientManager;

class MailboxController extends Controller
{
    private function getMailboxUser(Request $request)
    {
        $currentUser = auth()->user();
        if ($request->has('user_id')) {
            $targetUserId = $request->input('user_id');
            if ($currentUser->id != $targetUserId) {
                if (!$currentUser->isAdminOrAbove() && !$currentUser->isHR()) {
                    abort(403, 'Unauthorized access to this mailbox.');
                }
            }
            return User::findOrFail($targetUserId);
        }
        return $currentUser;
    }

    public function index(Request $request)
    {
        $user = $this->getMailboxUser($request);
        $folder = $request->input('folder', 'inbox');
        if (!in_array($folder, ['inbox', 'sent', 'trash'])) {
            $folder = 'inbox';
        }

        return view('mailbox.index', compact('folder', 'user'));
    }

    private function getImapClient($user)
    {
        if (!$user) {
            throw new \Exception("User is not authenticated.");
        }

        $enabled = $user->mailbox_imap_enabled;
        if (!$enabled) {
            throw new \Exception("Personal Domain Mailbox integration is not enabled. Please configure your settings first.");
        }

        $host = $user->mailbox_imap_host;
        $port = $user->mailbox_imap_port ?: '993';
        $encryption = $user->mailbox_imap_encryption ?: 'ssl';
        $username = $user->mailbox_imap_username;
        $password = $user->mailbox_imap_password;

        if (empty($host) || empty($username) || empty($password)) {
            throw new \Exception("Personal Domain Mailbox configuration is incomplete. Please verify settings.");
        }

        $cm = new ClientManager();
        return $cm->make([
            'host'          => $host,
            'port'          => $port,
            'encryption'    => $encryption,
            'validate_cert' => false,
            'username'      => $username,
            'password'      => $password,
            'protocol'      => 'imap'
        ]);
    }

    /**
     * Resolve and CACHE the IMAP folder path for a given folder type.
     * Avoids calling getFolders() on every single request.
     */
    private function resolveFolderPath($client, string $folderType, $user): string
    {
        $cacheKey = "mailbox_folder_{$user->id}_{$folderType}";

        return Cache::remember($cacheKey, 300, function () use ($client, $folderType, $user) {
            if ($folderType === 'inbox') {
                return 'INBOX';
            }

            $pattern = match ($folderType) {
                'sent'  => 'sent',
                'trash' => 'trash',
                default => $folderType,
            };
            $default = match ($folderType) {
                'sent'  => 'Sent',
                'trash' => 'Trash',
                default => 'INBOX',
            };

            try {
                $folders = $client->getFolders(false);
                foreach ($folders as $folder) {
                    $name = strtolower($folder->name);
                    $path = strtolower($folder->path);
                    if (str_contains($name, $pattern) || str_contains($path, $pattern)) {
                        return $folder->path;
                    }
                }
                // Trash: also try 'bin' / 'delete' aliases
                if ($folderType === 'trash') {
                    foreach ($folders as $folder) {
                        $name = strtolower($folder->name);
                        $path = strtolower($folder->path);
                        if (str_contains($name, 'bin') || str_contains($path, 'bin') ||
                            str_contains($name, 'delete') || str_contains($path, 'delete')) {
                            return $folder->path;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("IMAP getFolders failed: " . $e->getMessage());
            }

            return $default;
        });
    }

    /**
     * Open an IMAP folder, trying the resolved path first then a plain name fallback.
     */
    private function openFolder($client, string $folderType, $user)
    {
        $folderPath = $this->resolveFolderPath($client, $folderType, $user);
        $folder = $client->getFolder($folderPath);
        if (!$folder) {
            // Clear cached path so next request re-discovers it
            Cache::forget("mailbox_folder_{$user->id}_{$folderType}");
            $fallback = match ($folderType) {
                'sent'  => 'Sent',
                'trash' => 'Trash',
                default => 'INBOX',
            };
            $folder = $client->getFolder($fallback);
        }
        return $folder;
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'mailbox_imap_enabled' => 'required|boolean',
            'mailbox_imap_host' => 'required_if:mailbox_imap_enabled,1|nullable|string|max:255',
            'mailbox_imap_port' => 'required_if:mailbox_imap_enabled,1|nullable|string|max:10',
            'mailbox_imap_encryption' => 'required_if:mailbox_imap_enabled,1|nullable|string|in:ssl,tls,none',
            'mailbox_imap_username' => 'required_if:mailbox_imap_enabled,1|nullable|string|max:255',
            'mailbox_imap_password' => 'nullable|string|max:255',
            
            // SMTP Settings
            'mailbox_smtp_host' => 'required_if:mailbox_imap_enabled,1|nullable|string|max:255',
            'mailbox_smtp_port' => 'required_if:mailbox_imap_enabled,1|nullable|string|max:10',
            'mailbox_smtp_encryption' => 'required_if:mailbox_imap_enabled,1|nullable|string|in:ssl,tls,none',
            'mailbox_smtp_username' => 'required_if:mailbox_imap_enabled,1|nullable|string|max:255',
            'mailbox_smtp_password' => 'nullable|string|max:255',
        ]);

        $user = $this->getMailboxUser($request);

        $data = [
            'mailbox_imap_enabled' => $request->mailbox_imap_enabled,
            'mailbox_imap_host' => $request->mailbox_imap_host,
            'mailbox_imap_port' => $request->mailbox_imap_port ?: '993',
            'mailbox_imap_encryption' => $request->mailbox_imap_encryption ?: 'ssl',
            'mailbox_imap_username' => $request->mailbox_imap_username,
            
            'mailbox_smtp_host' => $request->mailbox_smtp_host,
            'mailbox_smtp_port' => $request->mailbox_smtp_port ?: '465',
            'mailbox_smtp_encryption' => $request->mailbox_smtp_encryption ?: 'ssl',
            'mailbox_smtp_username' => $request->mailbox_smtp_username,
        ];

        // Only update passwords if provided
        if ($request->filled('mailbox_imap_password')) {
            $data['mailbox_imap_password'] = $request->mailbox_imap_password;
        }
        if ($request->filled('mailbox_smtp_password')) {
            $data['mailbox_smtp_password'] = $request->mailbox_smtp_password;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Personal mailbox settings saved successfully!'
        ]);
    }

    /* ---------------------------------------------------------------
     |  Helper: build the cache key for a user+folder message list
     --------------------------------------------------------------- */
    private function msgCacheKey(int $userId, string $folder): string
    {
        return "mailbox_msgs_{$userId}_{$folder}";
    }

    /* ---------------------------------------------------------------
     |  Helper: format a single IMAP message into our array shape
     --------------------------------------------------------------- */
    private function formatMsg($msg): array
    {
        $subject = (string) $msg->getSubject();
        if ($subject) {
            $subject = mb_decode_mimeheader($subject);
        }
        $dateAttr = $msg->getDate();
        $date     = $dateAttr ? $dateAttr->toDate() : now();

        $fromArray = $msg->getFrom();
        $fromName  = '';
        $fromEmail = '';
        if (!empty($fromArray) && isset($fromArray[0])) {
            $fromName  = $fromArray[0]->personal ?? '';
            $fromEmail = $fromArray[0]->mail ?? '';
            if (empty($fromName)) {
                $fromName = $fromEmail;
            }
        }

        $toArray = $msg->getTo();
        $toName  = '';
        $toEmail = '';
        if (!empty($toArray) && isset($toArray[0])) {
            $toName  = trim($toArray[0]->personal ?? '');
            $toEmail = trim($toArray[0]->mail ?? '');
            if ($toName === $toEmail) {
                $toName = '';
            }
        }

        $isSeen = false;
        try {
            $flags = method_exists($msg, 'getFlags') ? $msg->getFlags() : null;
            if ($flags) {
                $isSeen = $flags->has('Seen') || $flags->has('\Seen');
            }
        } catch (\Exception $e) {
            Log::warning("IMAP getFlags failed: " . $e->getMessage());
        }

        $hasAttachment = false;
        try {
            $hasAttachment = method_exists($msg, 'hasAttachments') && $msg->hasAttachments();
        } catch (\Exception $e) {}

        return [
            'uid'                  => $msg->getUid(),
            'subject'              => $subject ?: '(No Subject)',
            'sender_name'          => $fromName,
            'sender_email'         => $fromEmail,
            'to_name'              => $toName,
            'to_email'             => $toEmail,
            'created_at_formatted' => $date->format('d M Y, h:i A'),
            'created_at_human'     => $date->diffForHumans(),
            'timestamp'            => $date->timestamp,
            'snippet'              => '',
            'has_attachment'       => $hasAttachment,
            'is_seen'              => $isSeen,
        ];
    }

    /* ---------------------------------------------------------------
     |  GET /mailbox/official  —  Serve cached list INSTANTLY
     |  (no IMAP connection at all unless cache is empty)
     --------------------------------------------------------------- */
    public function officialIndex(Request $request)
    {
        try {
            $user = $this->getMailboxUser($request);
            session_write_close();
            $folderType = $request->input('folder', 'inbox');
            if (!in_array($folderType, ['inbox', 'sent', 'trash'])) {
                $folderType = 'inbox';
            }

            if (!$user->mailbox_imap_enabled) {
                $messagesQuery = \App\Models\MailboxMessage::query();
                if ($folderType === 'inbox') {
                    $messagesQuery->where('receiver_id', $user->id)
                        ->whereNull('receiver_deleted_at');
                } elseif ($folderType === 'sent') {
                    $messagesQuery->where('sender_id', $user->id)
                        ->whereNull('sender_deleted_at');
                } else { // trash
                    $messagesQuery->where(function($q) use ($user) {
                        $q->where('receiver_id', $user->id)
                          ->whereNotNull('receiver_deleted_at')
                          ->where('receiver_deleted_at', '!=', '1970-01-01 00:00:00');
                    })->orWhere(function($q) use ($user) {
                        $q->where('sender_id', $user->id)
                          ->whereNotNull('sender_deleted_at')
                          ->where('sender_deleted_at', '!=', '1970-01-01 00:00:00');
                    });
                }

                $messages = $messagesQuery->with(['sender', 'receiver'])->latest()->get();

                $formatted = $messages->map(function($msg) {
                    return [
                        'uid'                  => $msg->id,
                        'subject'              => $msg->subject ?: '(No Subject)',
                        'sender_name'          => $msg->sender->name,
                        'sender_email'         => $msg->sender->email,
                        'to_name'              => $msg->receiver->name,
                        'to_email'             => $msg->receiver->email,
                        'created_at_formatted' => $msg->created_at->format('d M Y, h:i A'),
                        'created_at_human'     => $msg->created_at->diffForHumans(),
                        'timestamp'            => $msg->created_at->timestamp,
                        'snippet'              => \Illuminate\Support\Str::limit(strip_tags($msg->body), 60),
                        'has_attachment'       => !empty($msg->attachment_path),
                        'is_seen'              => (bool) $msg->is_read,
                    ];
                })->all();

                return response()->json([
                    'success'    => true,
                    'messages'   => $formatted,
                    'from_cache' => false,
                ]);
            }

            $cacheKey = $this->msgCacheKey($user->id, $folderType);
            $cached   = Cache::get($cacheKey);

            // If we have something in cache, return it immediately — no IMAP
            if ($cached !== null) {
                $sortedCached = collect($cached)->sortByDesc('timestamp')->values()->all();
                return response()->json([
                    'success'    => true,
                    'messages'   => $sortedCached,
                    'from_cache' => true,
                ]);
            }

            // First ever load for this folder — fetch from IMAP and cache
            $client = $this->getImapClient($user);
            $client->connect();
            $folder = $this->openFolder($client, $folderType, $user);

            if (!$folder) {
                return response()->json([
                    'success' => false,
                    'error'   => "Folder '{$folderType}' not found on the mail server.",
                ], 404);
            }

            $messages = $folder->query()
                ->all()
                ->setFetchBody(false)
                ->setFetchFlags(true)
                ->setFetchOrder('desc')
                ->limit(50)
                ->get();

            $formatted = array_values(array_map([$this, 'formatMsg'], iterator_to_array($messages)));
            $formatted = collect($formatted)->sortByDesc('timestamp')->values()->all();

            // Cache indefinitely — refreshed only via fetchNew
            Cache::forever($cacheKey, $formatted);

            return response()->json([
                'success'    => true,
                'messages'   => $formatted,
                'from_cache' => false,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* ---------------------------------------------------------------
     |  POST /mailbox/fetch-new  —  Hit IMAP for new messages only,
     |  merge them into cache, return what's new
     --------------------------------------------------------------- */
    public function fetchNew(Request $request)
    {
        try {
            $user = $this->getMailboxUser($request);
            session_write_close();

            $folderType = $request->input('folder', 'inbox');
            if (!in_array($folderType, ['inbox', 'sent', 'trash'])) {
                $folderType = 'inbox';
            }

            if (!$user->mailbox_imap_enabled) {
                $messagesQuery = \App\Models\MailboxMessage::query();
                if ($folderType === 'inbox') {
                    $messagesQuery->where('receiver_id', $user->id)
                        ->whereNull('receiver_deleted_at');
                } elseif ($folderType === 'sent') {
                    $messagesQuery->where('sender_id', $user->id)
                        ->whereNull('sender_deleted_at');
                } else { // trash
                    $messagesQuery->where(function($q) use ($user) {
                        $q->where('receiver_id', $user->id)
                          ->whereNotNull('receiver_deleted_at')
                          ->where('receiver_deleted_at', '!=', '1970-01-01 00:00:00');
                    })->orWhere(function($q) use ($user) {
                        $q->where('sender_id', $user->id)
                          ->whereNotNull('sender_deleted_at')
                          ->where('sender_deleted_at', '!=', '1970-01-01 00:00:00');
                    });
                }

                $messages = $messagesQuery->with(['sender', 'receiver'])->latest()->get();

                $formatted = $messages->map(function($msg) {
                    return [
                        'uid'                  => $msg->id,
                        'subject'              => $msg->subject ?: '(No Subject)',
                        'sender_name'          => $msg->sender->name,
                        'sender_email'         => $msg->sender->email,
                        'to_name'              => $msg->receiver->name,
                        'to_email'             => $msg->receiver->email,
                        'created_at_formatted' => $msg->created_at->format('d M Y, h:i A'),
                        'created_at_human'     => $msg->created_at->diffForHumans(),
                        'timestamp'            => $msg->created_at->timestamp,
                        'snippet'              => \Illuminate\Support\Str::limit(strip_tags($msg->body), 60),
                        'has_attachment'       => !empty($msg->attachment_path),
                        'is_seen'              => (bool) $msg->is_read,
                    ];
                })->all();

                return response()->json([
                    'success'     => true,
                    'new_count'   => 0,
                    'new_messages'=> [],
                    'messages'    => $formatted,
                ]);
            }

            $cacheKey = $this->msgCacheKey($user->id, $folderType);
            $existing = Cache::get($cacheKey, []);

            // Build set of UIDs already in cache
            $knownUids = collect($existing)->pluck('uid')->flip()->all(); // uid => true

            // Connect and fetch latest headers
            $client = $this->getImapClient($user);
            $client->connect();
            $folder = $this->openFolder($client, $folderType, $user);

            if (!$folder) {
                return response()->json(['success' => false, 'error' => "Folder '{$folderType}' not found."], 404);
            }

            $messages = $folder->query()
                ->all()
                ->setFetchBody(false)
                ->setFetchFlags(true)
                ->setFetchOrder('desc')
                ->limit(50)
                ->get();

            $newMessages = [];
            $allFormatted = [];

            foreach ($messages as $msg) {
                $formatted = $this->formatMsg($msg);
                $allFormatted[] = $formatted;
                if (!isset($knownUids[$formatted['uid']])) {
                    $newMessages[] = $formatted;
                }
            }

            // Sort by timestamp descending
            $allFormatted = collect($allFormatted)->sortByDesc('timestamp')->values()->all();
            $newMessages = collect($newMessages)->sortByDesc('timestamp')->values()->all();

            // Replace cache with full fresh list (sorted desc by timestamp)
            Cache::forever($cacheKey, $allFormatted);

            return response()->json([
                'success'     => true,
                'new_count'   => count($newMessages),
                'new_messages'=> $newMessages,
                'messages'    => $allFormatted,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function officialShow(Request $request, $uid)
    {
        try {
            $user = $this->getMailboxUser($request);
            session_write_close();
            $folderType = $request->input('folder', 'inbox');

            if (!$user->mailbox_imap_enabled) {
                $msg = \App\Models\MailboxMessage::with(['sender', 'receiver'])->findOrFail($uid);
                
                if ($msg->receiver_id === $user->id && !$msg->is_read) {
                    $msg->update(['is_read' => true]);
                }

                return response()->json([
                    'success'              => true,
                    'uid'                  => $msg->id,
                    'subject'              => $msg->subject ?: '(No Subject)',
                    'body'                 => $msg->body,
                    'is_html'              => true,
                    'sender_name'          => $msg->sender->name,
                    'sender_email'         => $msg->sender->email,
                    'to_name'              => $msg->receiver->name,
                    'to_email'             => $msg->receiver->email,
                    'created_at_formatted' => $msg->created_at->format('d M Y, h:i A'),
                    'created_at_human'     => $msg->created_at->diffForHumans(),
                    'attachments'          => $msg->attachment_path ? [
                        [
                            'name' => $msg->attachment_name ?: basename($msg->attachment_path),
                            'url'  => asset('storage/' . $msg->attachment_path),
                        ]
                    ] : [],
                ]);
            }

            $detailCacheKey = "mailbox_msg_detail_{$user->id}_{$folderType}_{$uid}";
            $cached = Cache::get($detailCacheKey);

            if ($cached !== null) {
                return response()->json($cached);
            }

            $client = $this->getImapClient($user);
            $client->connect();

            $folder = $this->openFolder($client, $folderType, $user);

            if (!$folder) {
                return response()->json(['success' => false, 'error' => 'Folder not found.'], 404);
            }

            $msg = $folder->query()->getMessageByUid($uid);

            if (!$msg) {
                return response()->json(['success' => false, 'error' => 'Message not found.'], 404);
            }

            // Mark read on server
            try { $msg->setFlag('Seen'); } catch (\Exception $e) {}

            // Update local cache message Seen status
            try {
                $cacheKey = $this->msgCacheKey($user->id, $folderType);
                $cachedList = Cache::get($cacheKey);
                if (is_array($cachedList)) {
                    $updated = false;
                    foreach ($cachedList as &$cMsg) {
                        if ((int)$cMsg['uid'] === (int)$uid) {
                            $cMsg['is_seen'] = true;
                            $updated = true;
                            break;
                        }
                    }
                    if ($updated) {
                        Cache::forever($cacheKey, $cachedList);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Failed to update seen flag in cache: " . $e->getMessage());
            }

            $subject = (string) $msg->getSubject();
            if ($subject) {
                $subject = mb_decode_mimeheader($subject);
            }
            $dateAttr = $msg->getDate();
            $date     = $dateAttr ? $dateAttr->toDate() : now();

            $fromArray = $msg->getFrom();
            $fromName  = '';
            $fromEmail = '';
            if (!empty($fromArray) && isset($fromArray[0])) {
                $fromName  = $fromArray[0]->personal ?? '';
                $fromEmail = $fromArray[0]->mail ?? '';
                if (empty($fromName)) {
                    $fromName = $fromEmail;
                }
            }

            $toArray = $msg->getTo();
            $toName  = '';
            $toEmail = '';
            if (!empty($toArray) && isset($toArray[0])) {
                $toName  = trim($toArray[0]->personal ?? '');
                $toEmail = trim($toArray[0]->mail ?? '');
                if ($toName === $toEmail) {
                    $toName = '';
                }
            }

            $htmlBody = $msg->getHTMLBody();
            $textBody = $msg->getTextBody();
            $isHtml   = !empty($htmlBody);
            $body     = $isHtml ? $htmlBody : ($textBody ?? '');

            // Handle attachments
            $attachments = [];
            if ($msg->hasAttachments()) {
                foreach ($msg->getAttachments() as $attachment) {
                    $filename = $attachment->getName();
                    $path     = 'mailbox_temp/' . $uid . '/' . $filename;
                    Storage::disk('public')->put($path, $attachment->getContent());
                    $attachments[] = [
                        'name' => $filename,
                        'url'  => asset('storage/' . $path),
                    ];
                }
            }

            $formattedDetail = [
                'success'              => true,
                'uid'                  => $uid,
                'subject'              => $subject ?: '(No Subject)',
                'body'                 => $body,
                'is_html'             => $isHtml,
                'sender_name'          => $fromName,
                'sender_email'         => $fromEmail,
                'to_name'              => $toName,
                'to_email'             => $toEmail,
                'created_at_formatted' => $date->format('d M Y, h:i A'),
                'created_at_human'     => $date->diffForHumans(),
                'attachments'          => $attachments,
            ];

            // Cache indefinitely (deleted only when the email is deleted)
            Cache::forever($detailCacheKey, $formattedDetail);

            return response()->json($formattedDetail);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function officialDestroy(Request $request, $uid)
    {
        try {
            $user = $this->getMailboxUser($request);
            session_write_close();
            $folderType = $request->input('folder', 'inbox');

            if (!$user->mailbox_imap_enabled) {
                $msg = \App\Models\MailboxMessage::findOrFail($uid);
                if ($folderType === 'trash') {
                    if ($msg->receiver_id === $user->id) {
                        $msg->update(['receiver_deleted_at' => '1970-01-01 00:00:00']);
                    }
                    if ($msg->sender_id === $user->id) {
                        $msg->update(['sender_deleted_at' => '1970-01-01 00:00:00']);
                    }
                } else {
                    if ($msg->receiver_id === $user->id) {
                        $msg->update(['receiver_deleted_at' => now()]);
                    }
                    if ($msg->sender_id === $user->id) {
                        $msg->update(['sender_deleted_at' => now()]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Message deleted successfully.'
                ]);
            }

            $client = $this->getImapClient($user);
            $client->connect();

            $folder = $this->openFolder($client, $folderType, $user);

            if (!$folder) {
                return response()->json(['success' => false, 'error' => 'Folder not found.'], 404);
            }

            $msg = $folder->query()->getMessageByUid($uid);

            if (!$msg) {
                return response()->json(['success' => false, 'error' => 'Message not found.'], 404);
            }

            if ($folderType === 'trash') {
                $msg->delete(true);
            } else {
                $trashPath = $this->resolveFolderPath($client, 'trash', $user);
                $msg->move($trashPath, true);
            }

            // Clear cached message list for current folder and trash folder
            Cache::forget($this->msgCacheKey($user->id, $folderType));
            Cache::forget($this->msgCacheKey($user->id, 'trash'));

            // Forget the cached message details
            Cache::forget("mailbox_msg_detail_{$user->id}_{$folderType}_{$uid}");

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'to_email'   => 'required|email|max:255',
            'cc_email'   => 'nullable|string|max:1024',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $user = $this->getMailboxUser($request);
        session_write_close();

        if (!$user->mailbox_imap_enabled) {
            // Find receiver user
            $receiver = User::where('email', $request->to_email)->first();
            if (!$receiver) {
                return response()->json([
                    'success' => false,
                    'error'   => 'User with recipient email not found.',
                ], 404);
            }

            $attachmentPath = null;
            $attachmentName = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentName = $file->getClientOriginalName();
                $attachmentPath = $file->storeAs('mail_attachments', uniqid() . '_' . $attachmentName, 'public');
            }

            // Create main internal message
            \App\Models\MailboxMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'subject' => $request->subject,
                'body' => $request->body,
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
                'is_read' => false,
            ]);

            // Handle CC users
            if ($request->cc_email) {
                $ccEmails = array_filter(array_map('trim', explode(',', $request->cc_email)));
                foreach ($ccEmails as $ccEmail) {
                    $ccUser = User::where('email', $ccEmail)->first();
                    if ($ccUser) {
                        \App\Models\MailboxMessage::create([
                            'sender_id' => $user->id,
                            'receiver_id' => $ccUser->id,
                            'subject' => $request->subject,
                            'body' => $request->body,
                            'attachment_path' => $attachmentPath,
                            'attachment_name' => $attachmentName,
                            'is_read' => false,
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Internal message sent successfully!'
            ]);
        }

        $smtpHost = $user->mailbox_smtp_host ?: $user->mailbox_imap_host;
        $smtpUsername = $user->mailbox_smtp_username ?: $user->mailbox_imap_username;
        $smtpPassword = $user->mailbox_smtp_password ?: $user->mailbox_imap_password;

        if (empty($smtpHost) || empty($smtpUsername) || empty($smtpPassword)) {
            return response()->json([
                'success' => false,
                'error' => 'SMTP mail server configuration is incomplete. Please configure settings first.'
            ], 400);
        }

        try {
            $attachmentPath = null;
            $attachmentName = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentName = $file->getClientOriginalName();
                // Store the uploaded file in local storage temporarily so the queue worker can access it
                $attachmentPath = $file->storeAs('mail_attachments', uniqid() . '_' . $attachmentName, 'local');
            }

            // Dispatch background job to send mail and append to Sent folder asynchronously
            \App\Jobs\SendPersonalMail::dispatch(
                $user->id,
                $request->to_email,
                $request->cc_email,
                $request->subject,
                $request->body,
                $attachmentPath,
                $attachmentName
            );

            return response()->json([
                'success' => true,
                'message' => 'Mail is being sent in the background!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to queue mail: ' . $e->getMessage()
            ], 500);
        }
    }
}
