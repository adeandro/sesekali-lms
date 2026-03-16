@forelse($threads as $thread)
@php
    $other       = $thread->otherParty(auth()->id());
    $unreadCount = $thread->unreadCountFor(auth()->id());
    $lastMsg     = $thread->replies->last() ?? $thread;
@endphp
<div class="relative group/thread">
<a href="{{ route('communication.messages.thread', $thread->id) }}"
   class="thread-item {{ request()->route('id') == $thread->id ? 'active' : '' }} block"
   data-search="{{ strtolower($other->name . ' ' . $thread->body) }}">
    <div class="relative shrink-0">
        <img src="{{ $other->photo_url }}" alt="{{ $other->name }}"
             class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 unread-badge">{{ $unreadCount }}</span>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
            <p class="text-sm font-black text-gray-900 truncate {{ $unreadCount > 0 ? 'text-[var(--brand-primary)]' : '' }}">
                {{ $other->formatted_name }}
            </p>
            <p class="text-[9px] text-gray-400 shrink-0 ml-2">{{ $lastMsg->created_at->diffForHumans(null, true) }}</p>
        </div>
        <p class="text-xs text-gray-500 truncate mt-0.5 {{ $unreadCount > 0 ? 'font-semibold' : '' }}">
            @if($lastMsg->sender_id === auth()->id())<span class="text-[var(--brand-primary)]">Anda: </span>@endif
            {{ $lastMsg->body }}
        </p>
        <p class="text-[9px] text-gray-400 mt-0.5 uppercase tracking-wide">{{ ucfirst($other->role) }}</p>
    </div>
</a>
{{-- Delete thread button: hover only, superadmin or participant --}}
@can('deleteThread', $thread)
<form id="del-thread-{{ $thread->id }}"
      action="{{ route('communication.messages.delete-thread', $thread->id) }}"
      method="POST" class="no-loading absolute top-2 right-2 opacity-0 group-hover/thread:opacity-100 transition-opacity">
    @csrf @method('DELETE')
    <button type="button"
            onclick="confirmDeleteThread({{ $thread->id }}, '{{ addslashes($other->formatted_name) }}')"
            class="w-7 h-7 rounded-lg flex items-center justify-center bg-white shadow border border-gray-100 text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition"
            title="Hapus percakapan">
        <i class="fas fa-trash-alt text-[10px]"></i>
    </button>
</form>
@endcan
</div>
@empty
<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
        <i class="fas fa-inbox text-gray-400"></i>
    </div>
    <p class="text-sm font-bold text-gray-600">Tidak ada pesan</p>
    <p class="text-xs text-gray-400 mt-0.5">Belum ada percakapan aktif.</p>
</div>
@endforelse
