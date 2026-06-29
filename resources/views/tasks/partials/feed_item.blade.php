@php
    $isSent = $isSent ?? ($item->user_id === auth()->id());
    $formattedTime = $formattedTime ?? $item->created_at->format('d M Y, h:i A');
    $itemDate = $item->created_at->format('d M Y');
    $itemTime = $item->created_at->format('h:i A');
    
    // Viewers list for comment info modal
    $viewers = $item->feed_type === 'comment' ? $item->views->map(function($v) {
        return [
            'name' => $v->user->name,
            'avatar_url' => $v->user->avatar_url,
            'viewed_at' => $v->viewed_at ? $v->viewed_at->format('d M Y, h:i A') : '—'
        ];
    })->toArray() : [];
@endphp

<div class="chat-row {{ $isSent ? 'sent' : 'received' }} mb-2" id="chat-row-{{ $item->feed_type }}-{{ $item->id }}" data-date="{{ $itemDate }}" data-time="{{ $itemTime }}">
    @if(!$isSent)
        <img src="{{ $item->user->avatar_url }}" alt="{{ $item->user->name }}" class="chat-avatar" title="{{ $item->user->name }}">
    @endif

    @if($isSent && $item->feed_type === 'comment')
        @php
            $isEditable = $isSent && $item->created_at->diffInMinutes(now()) < 30;
            $replySender = $isSent ? 'You' : $item->user->name;
        @endphp
        <div class="chat-bubble-actions dropdown">
            <button class="chat-bubble-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Options">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-start shadow-sm border border-light-subtle py-1" style="font-size: 13px; z-index: 1050;">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="javascript:void(0)" onclick="replyToComment('{{ $item->id }}', '{{ addslashes($replySender) }}')">
                        <i class="bi bi-reply-fill text-muted"></i> Reply
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#commentInfoModal" data-viewers="{{ json_encode($viewers) }}" data-sent-at="{{ $formattedTime }}">
                        <i class="bi bi-info-circle text-muted"></i> Message Info
                    </a>
                </li>
                @if($isEditable)
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="javascript:void(0)" onclick="editComment('{{ $item->id }}', 'comment')">
                            <i class="bi bi-pencil text-muted"></i> Edit Comment
                        </a>
                    </li>
                @endif
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 text-warning" href="javascript:void(0)" onclick="toggleMessageImportant('{{ $item->id }}', 'comment')">
                        <i class="bi {{ $item->is_important ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i> {{ $item->is_important ? 'Unstar Message' : 'Star Important' }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 text-danger" href="javascript:void(0)" onclick="toggleMessagePin('{{ $item->id }}', 'comment')">
                        <i class="bi {{ $item->is_pinned ? 'bi-pin-angle-fill text-danger' : 'bi-pin text-muted' }}"></i> {{ $item->is_pinned ? 'Unpin Message' : 'Pin Message' }}
                    </a>
                </li>
            </ul>
        </div>
    @endif

    <div class="chat-bubble">
        @if($item->feed_type === 'comment' && $item->parent_id && $item->parent)
             <div class="reply-quote-box p-2 mb-2 rounded border-start border-4 border-primary bg-light-subtle" style="font-size: 11.5px; opacity: 0.85; background-color: rgba(0,0,0,0.03);">
                 <div class="fw-bold text-primary mb-1">{{ $item->parent->user_id === auth()->id() ? 'You' : $item->parent->user->name }}</div>
                 <div class="text-truncate text-muted text-dark" style="max-width: 90%; font-style: italic;">{{ $item->parent->comment }}</div>
             </div>
        @endif
        
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

             @if(auth()->user()->isAdminOrAbove() && isset($task) && $task && $task->status === 'review' && str_contains($item->comment, 'Submitted task for completion review'))
                 <div class="d-flex border-top mt-3" style="margin-left: -16px; margin-right: -16px; margin-bottom: -10px;">
                     <a href="javascript:void(0)" onclick="handleCommentApprove(event, {{ $task->id }})" class="btn btn-link text-success fw-bold flex-fill text-center border-end rounded-0 py-2 text-decoration-none" style="font-size: 13px; outline: none; box-shadow: none;">
                         <i class="bi bi-check-circle-fill me-1"></i> Approve
                     </a>
                     <a href="javascript:void(0)" onclick="handleCommentReject(event, {{ $task->id }})" class="btn btn-link text-danger fw-bold flex-fill text-center border-end rounded-0 py-2 text-decoration-none" style="font-size: 13px; outline: none; box-shadow: none;">
                         <i class="bi bi-x-circle-fill me-1"></i> Reject
                     </a>
                     @if(request()->is('chat*') || request()->routeIs('chat.*'))
                         <a href="javascript:void(0)" onclick="toggleInfoSidebar(event)" class="btn btn-link text-primary fw-bold flex-fill text-center rounded-0 py-2 text-decoration-none" style="font-size: 13px; outline: none; box-shadow: none;">
                             <i class="bi bi-eye-fill me-1"></i> Review
                         </a>
                     @else
                         <a href="{{ route('tasks.show', $task) }}" class="btn btn-link text-primary fw-bold flex-fill text-center rounded-0 py-2 text-decoration-none" style="font-size: 13px; outline: none; box-shadow: none;">
                             <i class="bi bi-eye-fill me-1"></i> Review
                         </a>
                     @endif
                 </div>
             @endif
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

          <div class="chat-meta d-flex align-items-center gap-1">
              @if($item->feed_type === 'comment')
                  @if($item->is_important)
                      <i class="bi bi-star-fill text-warning me-1" style="font-size: 10px;" title="Starred"></i>
                  @endif
                  @if($item->is_pinned)
                      <i class="bi bi-pin-angle-fill text-danger me-1" style="font-size: 10px;" title="Pinned"></i>
                  @endif
                  @if($item->is_edited)
                      <span class="text-muted me-1" style="font-size: 9px; font-style: italic;">(edited)</span>
                  @endif
              @endif
              <span>{{ $itemTime }}</span>
              @if($isSent)
                  <i class="bi bi-check2-all"></i>
              @endif
          </div>
    </div>

    @if(!$isSent && $item->feed_type === 'comment')
        @php
            $isEditable = false;
            $replySender = $item->user->name;
        @endphp
        <div class="chat-bubble-actions dropdown">
            <button class="chat-bubble-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Options">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border border-light-subtle py-1" style="font-size: 13px; z-index: 1050;">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="javascript:void(0)" onclick="replyToComment('{{ $item->id }}', '{{ addslashes($replySender) }}')">
                        <i class="bi bi-reply-fill text-muted"></i> Reply
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#commentInfoModal" data-viewers="{{ json_encode($viewers) }}" data-sent-at="{{ $formattedTime }}">
                        <i class="bi bi-info-circle text-muted"></i> Message Info
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 text-warning" href="javascript:void(0)" onclick="toggleMessageImportant('{{ $item->id }}', 'comment')">
                        <i class="bi {{ $item->is_important ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i> {{ $item->is_important ? 'Unstar Message' : 'Star Important' }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 text-danger" href="javascript:void(0)" onclick="toggleMessagePin('{{ $item->id }}', 'comment')">
                        <i class="bi {{ $item->is_pinned ? 'bi-pin-angle-fill text-danger' : 'bi-pin text-muted' }}"></i> {{ $item->is_pinned ? 'Unpin Message' : 'Pin Message' }}
                    </a>
                </li>
            </ul>
        </div>
    @endif

    @if($isSent)
        <img src="{{ $item->user->avatar_url }}" alt="{{ $item->user->name }}" class="chat-avatar" title="{{ $item->user->name }}">
    @endif
</div>
