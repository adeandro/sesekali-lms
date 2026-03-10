@extends('layouts.app')

@section('title', 'Pesan - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background: var(--brand-glow)">
                    <i class="fas fa-comments text-[var(--brand-primary)]"></i>
                </div>
                Pesan
                @if($isAudit)
                    <span class="text-[10px] font-black bg-rose-100 text-rose-600 px-2 py-1 rounded-lg uppercase tracking-widest">AUDIT MODE</span>
                @endif
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                @if($isAudit) Anda melihat semua percakapan sebagai administrator.
                @else Percakapan antara Anda dan civitas akademika. @endif
            </p>
        </div>

        {{-- Compose Button (teacher/superadmin only) --}}
        @can('initiate', \App\Models\Message::class)
        <button x-data x-on:click="$dispatch('open-compose')"
                class="theme-primary-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold">
            <i class="fas fa-pen"></i> Pesan Baru
        </button>
        @endcan
    </div>

    {{-- Main Panel: 2-column layout --}}
    <div class="flex h-[calc(100vh-16rem)] gap-4 overflow-hidden">

        {{-- LEFT: Thread Sidebar --}}
        <div class="w-full sm:w-80 flex-shrink-0 theme-surface-card rounded-2xl theme-soft-shadow flex flex-col overflow-hidden"
             id="inboxSidebar">

            {{-- Search --}}
            <div class="p-3 border-b border-gray-100">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="search" id="threadSearch" placeholder="Cari percakapan..."
                           class="w-full pl-8 pr-4 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent">
                </div>
            </div>

            {{-- Thread List --}}
            <div class="flex-1 overflow-y-auto divide-y divide-gray-50" id="threadList">
                @forelse($threads as $thread)
                @php
                    $other       = $thread->otherParty(auth()->id());
                    $unreadCount = $thread->unreadCountFor(auth()->id());
                    $lastMsg     = $thread->replies->last() ?? $thread;
                @endphp
                <div class="relative group/thread">
                <a href="{{ route('communication.messages.thread', $thread->id) }}"
                   class="thread-item {{ request()->route('rootId') == $thread->id ? 'active' : '' }} block"
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
            </div>
        </div>

        {{-- RIGHT: Empty / Placeholder area (filled when thread is opened) --}}
        <div class="flex-1 theme-surface-card rounded-2xl theme-soft-shadow hidden sm:flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 rounded-3xl flex items-center justify-center mb-4" style="background: var(--brand-glow)">
                <i class="fas fa-comments text-[var(--brand-primary)] text-3xl"></i>
            </div>
            <p class="text-base font-black text-gray-700">Pilih percakapan</p>
            <p class="text-sm text-gray-400 mt-1.5">Klik salah satu dari daftar percakapan di sebelah kiri untuk membuka pesan.</p>
            @can('initiate', \App\Models\Message::class)
            <button x-data x-on:click="$dispatch('open-compose')"
                    class="mt-5 theme-secondary-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold">
                <i class="fas fa-pen text-xs"></i> Mulai Percakapan Baru
            </button>
            @endcan
        </div>
    </div>
</div>

{{-- COMPOSE MODAL (teacher/superadmin only) --}}
@can('initiate', \App\Models\Message::class)
<div x-data="{ open: false }" @open-compose.window="open = true">
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         @click.self="open = false"
         style="display:none">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal Header --}}
            <div class="p-6 border-b border-gray-100 flex items-center justify-between sidebar-header-gradient">
                <div>
                    <h3 class="text-base font-black text-white">Pesan Baru</h3>
                    <p class="text-xs text-indigo-100 mt-0.5">Kirim sekaligus ke satu atau lebih siswa</p>
                </div>
                <button @click="open = false" class="text-indigo-200 hover:text-white p-2 rounded-xl hover:bg-white/10 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Modal Form --}}
            <form action="{{ route('communication.messages.send') }}" method="POST" class="p-6 space-y-4 no-loading" id="composeForm">
                @csrf

                {{-- Recipient Picker --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-black text-gray-600 uppercase tracking-widest">Kepada</label>
                        <button type="button" id="selectAllBtn"
                                class="text-[10px] font-bold text-[var(--brand-primary)] hover:underline"
                                onclick="toggleSelectAll()">Pilih Semua</button>
                    </div>

                    {{-- Search box inside modal --}}
                    <div class="relative mb-2">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="recipientSearch" placeholder="Cari nama siswa..."
                               class="w-full pl-8 pr-4 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent">
                    </div>

                    {{-- Scrollable checkbox list --}}
                    <div class="border border-gray-200 rounded-xl overflow-y-auto divide-y divide-gray-50" style="max-height: 180px;" id="recipientList">
                        @foreach($messageableUsers as $student)
                        <label class="recipient-item flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer transition"
                               data-name="{{ strtolower($student->formatted_name . ' ' . ($student->class_group ?? '')) }}">
                            <input type="checkbox" name="receiver_ids[]" value="{{ $student->id }}"
                                   class="rounded text-[var(--brand-primary)] focus:ring-[var(--brand-primary)] w-4 h-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $student->formatted_name }}</p>
                                @if($student->class_group)
                                <p class="text-[10px] text-gray-400">Kelas {{ $student->class_group }}</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1.5">
                        <span id="selectedCount">0</span> siswa dipilih
                    </p>
                </div>

                {{-- Message body --}}
                <div>
                    <label class="block text-xs font-black text-gray-600 uppercase tracking-widest mb-1.5">Pesan</label>
                    <textarea name="body" rows="4" required
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium resize-none focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent"
                              placeholder="Tulis pesan Anda di sini..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" @click="open = false"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="theme-primary-btn px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<script>
// Live thread search
const searchInput = document.getElementById('threadSearch');
const threadItems = document.querySelectorAll('#threadList a[data-search]');

searchInput?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    threadItems.forEach(item => {
        item.closest('.group\/thread').style.display =
            item.dataset.search.includes(q) ? '' : 'none';
    });
});

// Recipient search in compose modal
const recipientSearch  = document.getElementById('recipientSearch');
const recipientItems   = document.querySelectorAll('.recipient-item');
const selectedCountEl  = document.getElementById('selectedCount');

function updateCount() {
    const checked = document.querySelectorAll('#recipientList input[type=checkbox]:checked').length;
    if (selectedCountEl) selectedCountEl.textContent = checked;
}

recipientSearch?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    recipientItems.forEach(item => {
        item.style.display = item.dataset.name.includes(q) ? '' : 'none';
    });
});

document.querySelectorAll('#recipientList input[type=checkbox]').forEach(cb => {
    cb.addEventListener('change', updateCount);
});

let allSelected = false;
function toggleSelectAll() {
    allSelected = !allSelected;
    document.querySelectorAll('#recipientList input[type=checkbox]').forEach(cb => {
        // Only toggle visible ones
        if (cb.closest('.recipient-item').style.display !== 'none') {
            cb.checked = allSelected;
        }
    });
    updateCount();
    const btn = document.getElementById('selectAllBtn');
    if (btn) btn.textContent = allSelected ? 'Batalkan Semua' : 'Pilih Semua';
}

// Validate at least 1 recipient selected before submit
document.getElementById('composeForm')?.addEventListener('submit', function(e) {
    const checked = this.querySelectorAll('input[type=checkbox]:checked').length;
    if (checked === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Penerima',
            text: 'Pilih minimal satu siswa sebagai penerima pesan.',
            confirmButtonColor: 'var(--brand-primary)',
            customClass: { popup: 'rounded-3xl', confirm: 'rounded-xl font-black' }
        });
    }
});

// SweetAlert2 confirm for thread delete
function confirmDeleteThread(id, name) {
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Percakapan?',
        html: `<p class="text-sm text-gray-600 mt-1">Seluruh percakapan dengan <strong>${name}</strong> beserta semua balasannya akan dihapus secara permanen.</p>`,
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
            document.getElementById(`del-thread-${id}`).submit();
        }
    });
}
</script>
@endsection
