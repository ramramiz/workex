@php
    $isSent = $isSent ?? ($item->user_id === auth()->id());
    $formattedTime = $formattedTime ?? $item->created_at->format('h:i A');
@endphp

<div class="chat-row {{ $isSent ? 'sent' : 'received' }} mb-2">
    @if(!$isSent)
        <img src="{{ $item->user->avatar_url }}" alt="{{ $item->user->name }}" class="chat-avatar" title="{{ $item->user->name }}">
    @endif

    <div class="chat-bubble">
        <span class="chat-sender">{{ $isSent ? 'You' : $item->user->name }}</span>

        @if($item->feed_type === 'comment')
             <!-- Comment Content -->
             @php
                 $formattedComment = e($item->comment);
                 
                 // Highlight statuses with their specific badge colors
                 $formattedComment = str_replace('**Pending**', '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Pending</span>', $formattedComment);
                 $formattedComment = str_replace('**In Progress**', '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">In Progress</span>', $formattedComment);
                 $formattedComment = str_replace('**Review**', '<span class="badge bg-info-subtle text-info border border-info-subtle">Review</span>', $formattedComment);
                 $formattedComment = str_replace('**Rework**', '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rework</span>', $formattedComment);
                 $formattedComment = str_replace('**Completed**', '<span class="badge bg-success-subtle text-success border border-success-subtle">Completed</span>', $formattedComment);
                 $formattedComment = str_replace('**Cancelled**', '<span class="badge bg-dark-subtle text-dark border border-dark-subtle">Cancelled</span>', $formattedComment);
                 
                 $formattedComment = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedComment);
                 $formattedComment = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank" class="text-primary fw-semibold">$1</a>', $formattedComment);
                 foreach(\App\Models\User::where('status', 'active')->get() as $u) {
                     $mentionName = '@' . $u->name;
                     $escapedName = e($mentionName);
                     if (stripos($formattedComment, $escapedName) !== false) {
                         $formattedComment = str_ireplace($escapedName, '<span class="fw-bold" style="color: #0284c7;">' . $escapedName . '</span>', $formattedComment);
                     }
                     $mentionEmail = '@' . $u->email;
                     $escapedEmail = e($mentionEmail);
                     if (stripos($formattedComment, $escapedEmail) !== false) {
                         $formattedComment = str_ireplace($escapedEmail, '<span class="fw-bold" style="color: #0284c7;">' . $escapedEmail . '</span>', $formattedComment);
                     }
                 }
             @endphp
             @if($item->image_path)
                 <div class="comment-image-container mb-2">
                     <img src="{{ asset('storage/' . $item->image_path) }}" alt="Annotated image" class="img-fluid rounded-3 comment-image" style="max-height: 250px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.08);" data-bs-toggle="modal" data-bs-target="#imageViewerModal" data-src="{{ asset('storage/' . $item->image_path) }}">
                 </div>
             @endif
             <div class="chat-text text-dark" style="white-space: pre-wrap;">{!! $formattedComment !!}</div>
         @else
             <!-- Time Log Content -->
             <div class="time-log-box">
                 <div class="time-log-header">
                     <i class="bi bi-stopwatch-fill"></i>
                     <span>Work Time Logged</span>
                 </div>
                 <div class="time-log-grid">
                     <span class="time-log-label">Start Time:</span>
                     <span class="time-log-value">{{ $item->started_at ? $item->started_at->format('d M Y, h:i A') : '—' }}</span>
                     
                     <span class="time-log-label">End Time:</span>
                     <span class="time-log-value">
                         @if($item->status === 'running')
                             <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-8 d-inline-flex align-items-center gap-1">
                                 <span class="pulse-dot"></span> Live / Running
                             </span>
                         @elseif($item->ended_at)
                             {{ $item->ended_at->format('d M Y, h:i A') }}
                         @else
                             Paused
                         @endif
                     </span>

                     <span class="time-log-label">Total Time:</span>
                     <span class="time-log-value">
                         @if($item->status === 'running')
                             <span class="text-danger fw-bold">Active</span>
                         @elseif($item->ended_at)
                             @php
                                 $hrs = intdiv($item->total_minutes, 60);
                                 $mins = $item->total_minutes % 60;
                                 $durationStr = $hrs > 0 ? "{$hrs}h {$mins}m" : "{$mins} mins";
                             @endphp
                             <span class="text-success fw-bold">{{ $durationStr }}</span>
                         @else
                             Paused
                         @endif
                     </span>
                 </div>

                 @if($item->note)
                     <div class="time-log-note-section">
                         <div class="time-log-note-title">Progress / Work Notes</div>
                         <div class="time-log-note-content">{{ $item->note }}</div>
                     </div>
                 @endif
             </div>
         @endif

         <div class="chat-meta">
             @if($item->feed_type === 'comment')
                 <a href="#" class="chat-info-trigger text-muted me-1" 
                    data-bs-toggle="modal" 
                    data-bs-target="#commentInfoModal" 
                    data-viewers="{{ json_encode($item->views->map(function($v) {
                        return [
                            'name' => $v->user->name,
                            'avatar_url' => $v->user->avatar_url,
                            'viewed_at' => $v->viewed_at ? $v->viewed_at->format('d M Y, h:i A') : '—'
                        ];
                    })) }}">
                     <i class="bi bi-info-circle"></i>
                 </a>
             @endif
             <span>{{ $formattedTime }}</span>
             @if($isSent)
                 <i class="bi bi-check2-all"></i>
             @endif
         </div>
    </div>

    @if($isSent)
        <img src="{{ $item->user->avatar_url }}" alt="{{ $item->user->name }}" class="chat-avatar" title="{{ $item->user->name }}">
    @endif
</div>
