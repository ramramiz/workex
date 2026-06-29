@extends('layouts.app')

@section('title', 'Direct Chat')
@section('page-title', 'Direct Chat')

@section('breadcrumb')
    <li class="breadcrumb-item active">Direct Chat</li>
@endsection

@push('styles')
<style>
    .chat-layout {
        display: flex;
        height: calc(100vh - var(--topnav-height) - 48px);
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.08);
        position: relative;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    .chat-sidebar {
        width: 350px;
        border-right: 1px solid rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        background: #fafafa;
        flex-shrink: 0;
    }
    .chat-sidebar-search {
        padding: 16px;
        background: #ffffff;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .chat-sidebar-search .input-group {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .chat-sidebar-search .input-group:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }
    .chat-sidebar-search .form-control {
        background-color: transparent;
        border: none;
        box-shadow: none;
        font-size: 13.5px;
        padding: 10px 14px;
        color: #0f172a !important;
    }
    .chat-sidebar-search .form-control::placeholder {
        color: #94a3b8 !important;
        opacity: 1;
    }
    .chat-sidebar-search .input-group-text {
        background-color: transparent;
        border: none;
        color: #64748b;
        padding-left: 14px;
    }
    .chat-user-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
    }
    .chat-user-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        margin: 4px 12px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
        color: inherit !important;
    }
    .chat-user-item:hover {
        background: #f1f5f9;
    }
    .chat-user-item.active {
        background: #e0e7ff;
        color: #1e1b4b !important;
    }
    .chat-user-item .avatar-container {
        position: relative;
        flex-shrink: 0;
    }
    .chat-user-item .avatar-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .chat-user-item .online-dot {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 10px;
        height: 10px;
        background: #10b981;
        border: 2px solid #ffffff;
        border-radius: 50%;
    }
    .chat-user-item .user-info {
        flex: 1;
        min-width: 0;
    }
    .chat-user-item .user-name {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .chat-user-item .user-meta {
        font-size: 11px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .chat-user-item .last-msg {
        font-size: 12px;
        color: #475569;
        margin-top: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .chat-user-item.active .last-msg {
        color: #312e81;
    }
    .chat-user-item .badge-count {
        background-color: #6366f1;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        padding: 4px 7px;
        border-radius: 20px;
        min-width: 18px;
        text-align: center;
    }

    /* Main Chat Container */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        position: relative;
    }
    .chat-header {
        padding: 14px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.08);
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content-between;
        z-index: 10;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .chat-header-user {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .chat-header-user img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .chat-header-name {
        font-weight: 600;
        font-size: 15px;
        color: #0f172a;
    }
    .chat-header-role {
        font-size: 11px;
        color: #64748b;
    }

    /* Messages Area */
    .chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        background-color: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .chat-row {
        display: flex;
        width: 100%;
        align-items: flex-end;
        gap: 10px;
    }
    .chat-row.sent {
        justify-content: flex-end;
    }
    .chat-row.received {
        justify-content: flex-start;
    }
    .chat-bubble {
        max-width: 65%;
        padding: 10px 14px 8px 14px;
        border-radius: 16px;
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        word-break: break-word;
    }
    .chat-row.sent .chat-bubble {
        background-color: #e0e7ff;
        color: #1e1b4b;
        border-bottom-right-radius: 4px;
    }
    .chat-row.received .chat-bubble {
        background-color: #ffffff;
        color: #0f172a;
        border-bottom-left-radius: 4px;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .chat-text {
        font-size: 14px;
        line-height: 1.5;
        white-space: pre-wrap;
    }
    .chat-image-preview {
        max-width: 100%;
        border-radius: 12px;
        margin-bottom: 6px;
        cursor: pointer;
        display: block;
        transition: opacity 0.2s;
    }
    .chat-image-preview:hover {
        opacity: 0.95;
    }
    .chat-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        font-size: 10px;
        color: #64748b;
        margin-top: 4px;
        font-weight: 500;
    }
    .chat-row.sent .chat-meta {
        color: #6366f1;
    }

    /* Footer / Input Area */
    .chat-footer {
        padding: 16px 20px;
        background: #ffffff;
        border-top: 1px solid rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .chat-input-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .chat-textarea {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
        resize: none;
        max-height: 100px;
        outline: none;
        transition: border-color 0.2s;
        background: #f8fafc !important;
        color: #0f172a !important;
    }
    .chat-textarea:focus {
        border-color: #6366f1;
        background: #ffffff !important;
    }
    .chat-textarea::placeholder {
        color: #94a3b8 !important;
        opacity: 1;
    }
    .chat-action-btn {
        background: none;
        border: none;
        color: #64748b;
        font-size: 20px;
        cursor: pointer;
        padding: 6px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content-center;
        transition: all 0.2s;
    }
    .chat-action-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }
    .chat-send-btn {
        background-color: #6366f1;
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background-color 0.2s;
    }
    .chat-send-btn:hover {
        background-color: #4f46e5;
    }

    /* Attachment Preview */
    .attachment-preview {
        display: inline-flex;
        position: relative;
        width: 72px;
        height: 72px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background-size: cover;
        background-position: center;
        margin-bottom: 4px;
    }
    .attachment-preview .remove-btn {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 18px;
        height: 18px;
        background-color: #ef4444;
        color: #ffffff;
        border-radius: 50%;
        border: none;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    /* Empty State */
    .chat-empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #f8fafc;
        color: #64748b;
        padding: 40px;
    }
</style>
@endpush

@section('content')
<div class="chat-layout">
    <!-- Left Sidebar: Active Contacts -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-search">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="contact-search" class="form-control" placeholder="Search contacts..." autocomplete="off">
            </div>
        </div>

        <div class="chat-user-list" id="contacts-container">
            @forelse($users as $u)
                <a href="javascript:void(0);" 
                   class="chat-user-item contact-item" 
                   data-id="{{ $u->id }}" 
                   data-name="{{ $u->name }}" 
                   data-role="{{ $u->role?->name ?? 'Staff' }}" 
                   data-avatar="{{ $u->avatar_url }}"
                   id="contact-{{ $u->id }}">
                    <div class="avatar-container">
                        <img src="{{ $u->avatar_url }}" alt="" class="avatar-circle">
                        @if($u->is_working_today)
                            <div class="online-dot" title="Working Today"></div>
                        @endif
                    </div>
                    <div class="user-info">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="user-name">{{ $u->name }}</span>
                        </div>
                        <div class="last-msg text-muted" id="last-msg-{{ $u->id }}">
                            @if($u->last_message)
                                {{ $u->last_message->message ?? '[Image]' }}
                            @else
                                No messages yet
                            @endif
                        </div>
                    </div>
                    <span class="badge-count {{ $u->unread_count > 0 ? '' : 'd-none' }}" id="badge-{{ $u->id }}">
                        {{ $u->unread_count }}
                    </span>
                </a>
            @empty
                <div class="text-center py-5 text-muted fs-7">No other users found.</div>
            @endforelse
        </div>
    </div>

    <!-- Right Pane: Conversation History -->
    <div class="chat-main">
        <!-- Empty State (No contact selected) -->
        <div class="chat-empty-state" id="empty-state">
            <i class="bi bi-chat-text-fill" style="font-size: 64px; color: #cbd5e1; margin-bottom: 16px;"></i>
            <h5 class="fw-bold text-dark">Direct Messages</h5>
            <p class="fs-7 text-center" style="max-width: 320px;">Select a contact from the list on the left to start a private conversation.</p>
        </div>

        <!-- Chat Frame (Hidden initially) -->
        <div class="d-none flex-column h-100" id="chat-frame">
            <!-- Header -->
            <div class="chat-header">
                <div class="chat-header-user">
                    <img src="" alt="" id="chat-header-avatar">
                    <div>
                        <div class="chat-header-name" id="chat-header-name"></div>
                        <div class="chat-header-role" id="chat-header-role"></div>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="chat-body" id="chat-body">
                <!-- Chat history loads dynamically -->
            </div>

            <!-- Footer / Input Form -->
            <div class="chat-footer">
                <!-- Image/PDF Attachment Preview Card -->
                <div id="attachment-preview-container" class="d-none">
                    <div class="attachment-preview" id="attachment-preview">
                        <button type="button" class="remove-btn" id="btn-remove-attachment"><i class="bi bi-x"></i></button>
                    </div>
                </div>

                <form id="direct-chat-form" onsubmit="sendMessage(event)">
                    <input type="hidden" id="chat-image-data" name="image_data">
                    <input type="file" id="direct-image-input" name="document" accept="image/*,application/pdf" class="d-none" onchange="handleImageFileSelect(event)">
                    
                    <div class="chat-input-container">
                        <button type="button" class="chat-action-btn" onclick="document.getElementById('direct-image-input').click()" title="Attach Image/PDF">
                            <i class="bi bi-paperclip"></i>
                        </button>
                        
                        <textarea id="chat-message-input" class="chat-textarea" rows="1" placeholder="Type a message..." autocomplete="off"></textarea>
                        
                        <button type="submit" class="chat-send-btn">
                            <span>Send</span>
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Modal (Lightbox) -->
<div class="modal fade" id="directImageViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-header border-0 p-0 position-absolute" style="right: 10px; top: 10px; z-index: 10;">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" id="directLightboxImage" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeContactId = null;
    let pollInterval = null;
    let lastPolledAt = new Date().toISOString();

    document.addEventListener('DOMContentLoaded', function() {
        // Set up contact filter search
        const searchInput = document.getElementById('contact-search');
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const contacts = document.querySelectorAll('.contact-item');
            
            contacts.forEach(contact => {
                const name = contact.getAttribute('data-name').toLowerCase();
                const role = contact.getAttribute('data-role').toLowerCase();
                if (name.includes(query) || role.includes(query)) {
                    contact.classList.remove('d-none');
                } else {
                    contact.classList.add('d-none');
                }
            });
        });

        // Set up click handlers for contact items
        const contactItems = document.querySelectorAll('.contact-item');
        contactItems.forEach(item => {
            item.addEventListener('click', function() {
                contactItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                const contactId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const role = this.getAttribute('data-role');
                const avatar = this.getAttribute('data-avatar');
                
                openChat(contactId, name, role, avatar);
            });
        });

        // Setup image pasting support in chat textarea
        const textarea = document.getElementById('chat-message-input');
        textarea.addEventListener('paste', function(e) {
            const items = (e.clipboardData || window.clipboardData || e.originalEvent.clipboardData).items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const file = items[i].getAsFile();
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const base64 = event.target.result;
                        document.getElementById('chat-image-data').value = base64;
                        
                        const preview = document.getElementById('attachment-preview');
                        preview.style.backgroundImage = `url(${base64})`;
                        document.getElementById('attachment-preview-container').classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                    e.preventDefault();
                    break;
                }
            }
        });

        // Setup auto-expand textarea height
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Textarea submit message on enter key
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(e);
            }
        });

        // Setup remove attachment button click handler
        document.getElementById('btn-remove-attachment').addEventListener('click', function() {
            clearAttachment();
        });

        // Start global polling trigger for contact list last message / badges updates
        startDirectChatPolling();
    });

    function openChat(contactId, name, role, avatar) {
        activeContactId = contactId;
        
        // Update header details
        document.getElementById('chat-header-avatar').src = avatar;
        document.getElementById('chat-header-name').textContent = name;
        document.getElementById('chat-header-role').textContent = role;
        
        // Hide empty state and show chat frame
        document.getElementById('empty-state').classList.add('d-none');
        document.getElementById('chat-frame').classList.remove('d-none');
        
        // Reset inputs and clear attachments
        document.getElementById('chat-message-input').value = '';
        document.getElementById('chat-message-input').style.height = 'auto';
        clearAttachment();
        
        // Load message history from DB
        const chatBody = document.getElementById('chat-body');
        chatBody.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading messages...</div>';

        fetch(`/direct-chat/messages/${contactId}`)
            .then(res => res.json())
            .then(data => {
                chatBody.innerHTML = '';
                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        appendMessageHtml(msg);
                    });
                    scrollToBottom();
                } else {
                    chatBody.innerHTML = '<div class="text-center py-5 text-muted" id="no-messages">No messages yet. Send a message to start the conversation!</div>';
                }
                
                // Clear badge count
                const badge = document.getElementById(`badge-${contactId}`);
                if (badge) {
                    badge.classList.add('d-none');
                    badge.textContent = '0';
                }
            })
            .catch(err => {
                console.error(err);
                chatBody.innerHTML = '<div class="text-center py-5 text-danger">Failed to load message history.</div>';
            });
    }

    function handleImageFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            const preview = document.getElementById('attachment-preview');
            if (file.type === 'application/pdf') {
                document.getElementById('chat-image-data').value = '';
                preview.style.backgroundImage = 'none';
                preview.innerHTML = `
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center p-2 bg-light rounded border border-light-subtle" style="width: 100%; height: 100%;">
                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-4"></i>
                        <span class="text-xs text-truncate w-100 mt-1" style="font-size: 8px; max-width: 60px; color: #000;" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
                    </div>
                    <button type="button" class="remove-btn" id="btn-remove-attachment" onclick="clearAttachment()"><i class="bi bi-x"></i></button>
                `;
                document.getElementById('attachment-preview-container').classList.remove('d-none');
            } else if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const base64 = e.target.result;
                    document.getElementById('chat-image-data').value = base64;
                    
                    preview.style.backgroundImage = `url(${base64})`;
                    preview.innerHTML = `<button type="button" class="remove-btn" id="btn-remove-attachment" onclick="clearAttachment()"><i class="bi bi-x"></i></button>`;
                    document.getElementById('attachment-preview-container').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        }
    }

    function clearAttachment() {
        document.getElementById('chat-image-data').value = '';
        document.getElementById('direct-image-input').value = '';
        const preview = document.getElementById('attachment-preview');
        preview.style.backgroundImage = '';
        preview.innerHTML = `<button type="button" class="remove-btn" id="btn-remove-attachment" onclick="clearAttachment()"><i class="bi bi-x"></i></button>`;
        document.getElementById('attachment-preview-container').classList.add('d-none');
    }

    function appendMessageHtml(msg) {
        const chatBody = document.getElementById('chat-body');
        
        // Remove empty state message if it is showing
        const noMessages = document.getElementById('no-messages');
        if (noMessages) {
            noMessages.remove();
        }

        const msgRow = document.createElement('div');
        msgRow.className = `chat-row ${msg.is_sent ? 'sent' : 'received'} mb-3`;
        msgRow.id = `msg-row-${msg.id}`;

        let imgHtml = '';
        if (msg.image_url) {
            imgHtml = `<img src="${msg.image_url}" class="chat-image-preview" onclick="openLightbox('${msg.image_url}')">`;
        }

        let fileHtml = '';
        if (msg.file_url) {
            fileHtml = `
                <div class="chat-file-attachment p-2 mb-2 rounded border border-light-subtle bg-light d-flex align-items-center gap-2" style="max-width: 280px; font-size: 13px;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-4 flex-shrink-0"></i>
                    <div class="flex-grow-1 text-truncate" style="max-width: 180px;">
                        <a href="${msg.file_url}" target="_blank" class="text-decoration-none text-dark fw-semibold" title="${escapeHtml(msg.file_name)}">
                            ${escapeHtml(msg.file_name)}
                        </a>
                    </div>
                    <a href="${msg.file_url}" target="_blank" download="${escapeHtml(msg.file_name)}" class="btn btn-link text-muted p-1 ms-auto" title="Download">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            `;
        }

        let textHtml = '';
        if (msg.message) {
            textHtml = `<div class="chat-text">${escapeHtml(msg.message)}</div>`;
        }

        msgRow.innerHTML = `
            <div class="chat-bubble">
                ${imgHtml}
                ${fileHtml}
                ${textHtml}
                <div class="chat-meta">
                    <span>${msg.formatted_time}</span>
                    ${msg.is_sent ? '<i class="bi bi-check2-all"></i>' : ''}
                </div>
            </div>
        `;

        chatBody.appendChild(msgRow);
    }

    function sendMessage(event) {
        if (event) event.preventDefault();
        
        const messageInput = document.getElementById('chat-message-input');
        const messageText = messageInput.value.trim();
        const imageData = document.getElementById('chat-image-data').value;
        const fileInput = document.getElementById('direct-image-input');
        const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
        const contactId = activeContactId;

        if (!messageText && !imageData && !hasFile) return;
        if (!contactId) return;

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        if (messageText) formData.append('message', messageText);
        if (imageData) formData.append('image_data', imageData);
        
        if (hasFile) {
            const file = fileInput.files[0];
            if (file && file.type === 'application/pdf') {
                formData.append('document', file);
            }
        }

        // Reset form inputs immediately for user responsiveness
        messageInput.value = '';
        messageInput.style.height = 'auto';
        clearAttachment();

        fetch(`/direct-chat/messages/${contactId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(errData => {
                    let errMsg = errData.message || 'Failed to send message.';
                    if (errData.errors) {
                        const firstKey = Object.keys(errData.errors)[0];
                        if (firstKey && errData.errors[firstKey].length > 0) {
                            errMsg = errData.errors[firstKey][0];
                        }
                    }
                    throw new Error(errMsg);
                });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                appendMessageHtml(data.message);
                scrollToBottom();
                
                // Update sidebar list info
                const lastMsgDiv = document.getElementById(`last-msg-${contactId}`);
                if (lastMsgDiv) {
                    lastMsgDiv.textContent = data.message.message || (data.message.file_url ? '[Document]' : '[Image]');
                }
            } else {
                alert(data.message || 'Failed to send message.');
            }
        })
        .catch(err => {
            alert(err.message || 'Error sending message.');
            console.error('Error sending message:', err);
        });
    }

    function openLightbox(url) {
        document.getElementById('directLightboxImage').src = url;
        const myModal = new bootstrap.Modal(document.getElementById('directImageViewerModal'));
        myModal.show();
    }

    function scrollToBottom() {
        const chatBody = document.getElementById('chat-body');
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function startDirectChatPolling() {
        // Poll for updates every 4 seconds
        setInterval(function() {
            let url = `/direct-chat/updates?since=${encodeURIComponent(lastPolledAt)}`;
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        lastPolledAt = data.timestamp;

                        // 1. If currently in an active conversation, append incoming messages matching activeContactId
                        if (activeContactId && data.new_messages.length > 0) {
                            let appendedAny = false;
                            
                            data.new_messages.forEach(msg => {
                                if (parseInt(msg.sender_id) === parseInt(activeContactId)) {
                                    // Check if this message was already appended
                                    if (!document.getElementById(`msg-row-${msg.id}`)) {
                                        appendMessageHtml({
                                            id: msg.id,
                                            sender_id: msg.sender_id,
                                            receiver_id: {{ auth()->id() }},
                                            message: msg.message,
                                            image_url: msg.image_url,
                                            file_url: msg.file_url,
                                            file_name: msg.file_name,
                                            formatted_time: msg.formatted_time,
                                            is_sent: false
                                        });
                                        appendedAny = true;
                                    }
                                }
                            });

                            if (appendedAny) {
                                scrollToBottom();
                                // Mark as read
                                fetch(`/direct-chat/read/${activeContactId}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                            }
                        }

                        // 2. Update sidebar unread counts and message previews
                        if (data.unread_counts) {
                            Object.keys(data.unread_counts).forEach(senderId => {
                                const count = data.unread_counts[senderId];
                                const badge = document.getElementById(`badge-${senderId}`);
                                if (badge) {
                                    // If active chat contact matches, keep badge hidden
                                    if (activeContactId && parseInt(activeContactId) === parseInt(senderId)) {
                                        badge.classList.add('d-none');
                                    } else if (count > 0) {
                                        badge.classList.remove('d-none');
                                        badge.textContent = count;
                                    } else {
                                        badge.classList.add('d-none');
                                    }
                                }
                            });
                        }
                        
                        // Update last message preview in list
                        if (data.new_messages.length > 0) {
                            data.new_messages.forEach(msg => {
                                const lastMsgDiv = document.getElementById(`last-msg-${msg.sender_id}`);
                                if (lastMsgDiv) {
                                    lastMsgDiv.textContent = msg.message || (msg.file_url ? '[Document]' : '[Image]');
                                    lastMsgDiv.classList.add('fw-bold');
                                }
                            });
                        }
                    }
                })
                .catch(err => console.error('Error polling direct messages:', err));
        }, 4000);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
@endpush
