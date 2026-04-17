@extends('layouts.app')

@section('title', 'Percakapan - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('content')
<div class="space-y-4">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('communication.messages.inbox') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-[var(--brand-primary)] hover:bg-[var(--brand-glow)] transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        @php
            $other = $thread->otherParty(auth()->id());
        @endphp
        <div class="flex items-center gap-3">
            <img src="{{ $other->photo_url }}" alt="{{ $other->name }}"
                 class="w-10 h-10 rounded-full object-cover border-2 shadow-sm"
                 style="border-color: var(--brand-glow)">
            <div>
                <p class="text-base font-black text-gray-900">{{ $other->full_name }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold">{{ ucfirst($other->role) }}</p>
            </div>
        </div>
    </div>

    {{-- Chat Container --}}
    <div class="theme-surface-card rounded-2xl theme-soft-shadow flex flex-col" style="height: calc(100vh - 18rem)">

        {{-- Messages Area --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chatArea">

            {{-- Root message --}}
            @php $isSentRoot = $thread->sender_id === auth()->id(); @endphp
            <div class="flex flex-col w-full group/msg">
                <div class="flex items-end gap-2 {{ $isSentRoot ? 'flex-row-reverse' : '' }}">
                    @if(!$isSentRoot)
                    <img src="{{ $thread->sender->photo_url }}" alt="{{ $thread->sender->name }}"
                         class="w-8 h-8 rounded-full object-cover shrink-0 mb-1 border-2 border-white shadow-sm">
                    @endif
                    <div class="{{ $isSentRoot ? 'chat-bubble-sent' : 'chat-bubble-received' }}">
                        <p class="text-sm leading-relaxed">{{ $thread->body }}</p>
                    </div>
                    {{-- Delete button (sender or superadmin) --}}
                    @can('deleteMessage', $thread)
                    <form id="del-msg-{{ $thread->id }}"
                          action="{{ route('communication.messages.delete', $thread->id) }}" method="POST" class="no-loading shrink-0 mb-1 opacity-0 group-hover/msg:opacity-100 transition-opacity">
                        @csrf @method('DELETE')
                        <button type="button"
                                onclick="confirmDeleteMsg({{ $thread->id }})"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition" title="Hapus pesan">
                            <i class="fas fa-trash-alt text-[10px]"></i>
                        </button>
                    </form>
                    @endcan
                </div>
                <p class="text-[10px] text-gray-400 mt-1 {{ $isSentRoot ? 'text-right' : 'text-left ml-10' }} font-semibold">
                    {{ $thread->created_at->diffForHumans() }}
                </p>
            </div>

            {{-- Replies --}}
            @foreach($thread->replies as $reply)
            @php $isSent = $reply->sender_id === auth()->id(); @endphp
            <div class="flex flex-col w-full group/msg">
                <div class="flex items-end gap-2 {{ $isSent ? 'flex-row-reverse' : '' }}">
                    @if(!$isSent)
                    <img src="{{ $reply->sender->photo_url }}" alt="{{ $reply->sender->name }}"
                         class="w-8 h-8 rounded-full object-cover shrink-0 mb-1 border-2 border-white shadow-sm">
                    @endif
                    <div class="{{ $isSent ? 'chat-bubble-sent' : 'chat-bubble-received' }}">
                        <p class="text-sm leading-relaxed">{{ $reply->body }}</p>
                    </div>
                    {{-- Delete button (sender or superadmin) --}}
                    @can('deleteMessage', $reply)
                    <form id="del-msg-{{ $reply->id }}"
                          action="{{ route('communication.messages.delete', $reply->id) }}" method="POST" class="no-loading shrink-0 mb-1 opacity-0 group-hover/msg:opacity-100 transition-opacity">
                        @csrf @method('DELETE')
                        <button type="button"
                                onclick="confirmDeleteMsg({{ $reply->id }})"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition" title="Hapus pesan">
                            <i class="fas fa-trash-alt text-[10px]"></i>
                        </button>
                    </form>
                    @endcan
                </div>
                <p class="text-[10px] text-gray-400 mt-1 {{ $isSent ? 'text-right' : 'text-left ml-10' }} font-semibold">
                    {{ $reply->created_at->diffForHumans() }}
                </p>
            </div>
            @endforeach
        </div>

        {{-- Reply Form (all roles including superadmin) --}}
        <div class="border-t border-gray-100 p-4">
            <form action="{{ route('communication.messages.send') }}" method="POST"
                  class="flex items-end gap-3 no-loading" id="replyForm">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $other->id }}">
                <input type="hidden" name="parent_id"   value="{{ $thread->id }}">

                <div class="flex-1 relative">
                    <textarea name="body" rows="1" required
                              placeholder="Tulis balasan..."
                              id="replyInput"
                              class="w-full px-4 py-3 pr-12 rounded-2xl border border-gray-200 text-sm font-medium resize-none focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent transition"
                              style="max-height: 120px; overflow-y: auto;"
                              onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); this.closest('form').dispatchEvent(new Event('submit')); }"></textarea>
                </div>

                <button type="submit" id="sendBtn"
                        class="theme-primary-btn w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 transition">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </form>
            <p class="text-[10px] text-gray-400 mt-2 text-center">Enter untuk kirim · Shift+Enter untuk baris baru</p>
        </div>
    </div>
</div>

<script>
// Auto-scroll chat to bottom
const chatArea = document.getElementById('chatArea');
if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;

// Auto-resize textarea
const replyInput = document.getElementById('replyInput');
if (replyInput) {
    replyInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
}

// SweetAlert2 confirm for message delete
function confirmDeleteMsg(id) {
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Pesan?',
        text: 'Pesan ini akan dihapus. Tindakan ini tidak dapat dibatalkan.',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash-alt mr-1.5"></i> Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup:   'rounded-3xl shadow-2xl border-0',
            confirm: 'rounded-xl px-5 py-2.5 font-black text-xs uppercase tracking-wide',
            cancel:  'rounded-xl px-5 py-2.5 font-bold text-xs',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`del-msg-${id}`).submit();
        }
    });
}

function confirmDeleteThread() {
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Percakapan?',
        html: `<p class="text-sm text-gray-600 mt-1">Seluruh percakapan beserta semua balasannya akan dihapus. Tindakan ini tidak dapat dibatalkan.</p>`,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash-alt mr-2"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup:   'rounded-3xl shadow-2xl border-0',
            confirm: 'rounded-xl px-6 py-2.5 font-black text-xs uppercase tracking-wide',
            cancel:  'rounded-xl px-6 py-2.5 font-bold text-xs',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('del-thread-main').submit();
        }
    });
}

// AJAX Submission & Polling
let lastPolledAt = "{{ now()->toDateTimeString() }}";
const replyForm = document.getElementById('replyForm');
const sendBtn   = document.getElementById('sendBtn');

replyForm?.addEventListener('submit', function(e) {
    e.preventDefault();
    const body = replyInput.value.trim();
    if (!body) return;

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-circle-notch animate-spin"></i>';

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            replyInput.value = '';
            replyInput.style.height = 'auto';
            appendMessage(data.message, true);
        }
    })
    .catch(err => console.error('Send error:', err))
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane text-sm"></i>';
    });
});

function pollMessages() {
    fetch("{{ route('communication.messages.poll-thread', $thread->id) }}?after=" + encodeURIComponent(lastPolledAt), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                // Only append if it's not our own (already appended via AJAX)
                if (msg.sender_id != {{ auth()->id() }}) {
                    appendMessage(msg, false);
                }
            });
        }
        lastPolledAt = data.timestamp;
    })
    .catch(e => console.error('Poll error:', e));
}

function appendMessage(msg, isSent) {
    const chatArea = document.getElementById('chatArea');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'flex flex-col w-full group/msg animate-in fade-in slide-in-from-bottom-2 duration-300';
    
    // Check if message already exists (simple deduplication)
    if (document.getElementById(`del-msg-${msg.id}`)) return;

    // Use placeholder if photo_url is missing (should be there if object is complete)
    const photo = msg.sender.photo_url || `https://ui-avatars.com/api/?name=${msg.sender.name}`;

    msgDiv.innerHTML = `
        <div class="flex items-end gap-2 ${isSent ? 'flex-row-reverse' : ''}">
            ${!isSent ? `<img src="${photo}" class="w-8 h-8 rounded-full object-cover shrink-0 mb-1 border-2 border-white shadow-sm">` : ''}
            <div class="${isSent ? 'chat-bubble-sent' : 'chat-bubble-received'}">
                <p class="text-sm leading-relaxed">${msg.body}</p>
            </div>
        </div>
        <p class="text-[10px] text-gray-400 mt-1 ${isSent ? 'text-right' : 'text-left ml-10'} font-semibold">
            Baru saja
        </p>
    `;
    chatArea.appendChild(msgDiv);
    chatArea.scrollTop = chatArea.scrollHeight;
}

// Start polling every 30 seconds for threads (RAMAH HOSTING)
let threadPollInterval = setInterval(pollMessages, 30000);

document.addEventListener('visibilitychange', () => {
    if (document.hidden) clearInterval(threadPollInterval);
    else threadPollInterval = setInterval(pollMessages, 30000);
});
</script>
@endsection
