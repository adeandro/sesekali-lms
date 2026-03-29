@extends('layouts.app')

@section('title', 'Pengumuman - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background: var(--brand-glow)">
                    <i class="fas fa-bullhorn text-[var(--brand-primary)]"></i>
                </div>
                Pengumuman
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola pengumuman untuk seluruh civitas akademika.</p>
        </div>
        @can('create', \App\Models\Announcement::class)
        <a href="{{ route('communication.announcements.create') }}"
           class="inline-flex items-center gap-2 theme-primary-btn px-5 py-2.5 rounded-xl text-sm font-bold">
            <i class="fas fa-plus"></i> Buat Pengumuman
        </a>
        @endcan
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
            $total   = $announcements->total();
            $active  = $announcements->getCollection()->where('is_active', true)->count();
            $urgent  = $announcements->getCollection()->where('type', 'urgent')->count();
            $warning = $announcements->getCollection()->where('type', 'warning')->count();
        @endphp
        @foreach([
            ['icon' => 'fa-list', 'color' => 'bg-slate-100 text-slate-600', 'label' => 'Total', 'value' => $total],
            ['icon' => 'fa-circle-check', 'color' => 'bg-green-100 text-green-600', 'label' => 'Aktif', 'value' => $active],
            ['icon' => 'fa-bell', 'color' => 'bg-red-100 text-red-600', 'label' => 'Urgent', 'value' => $urgent],
            ['icon' => 'fa-exclamation-triangle', 'color' => 'bg-amber-100 text-amber-600', 'label' => 'Peringatan', 'value' => $warning],
        ] as $stat)
        <div class="theme-surface-card rounded-2xl p-4 flex items-center gap-3 theme-soft-shadow">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $stat['color'] }}">
                <i class="fas {{ $stat['icon'] }} text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500">{{ $stat['label'] }}</p>
                <p class="text-xl font-black text-gray-900">{{ $stat['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Announcements Table --}}
    <div class="theme-surface-card rounded-2xl overflow-hidden theme-soft-shadow">
        @if($announcements->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background: var(--brand-glow)">
                <i class="fas fa-bullhorn text-[var(--brand-primary)] text-2xl"></i>
            </div>
            <p class="text-base font-bold text-gray-700">Belum ada pengumuman</p>
            <p class="text-sm text-gray-400 mt-1">Buat pengumuman pertama untuk civitas akademika.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest">Judul</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest hidden sm:table-cell">Tipe</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest hidden md:table-cell">Target</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest hidden lg:table-cell">Pembuat</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest hidden sm:table-cell">Login</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($announcements as $ann)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-900 text-sm group-hover:text-[var(--brand-primary)] transition-colors">{{ $ann->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $ann->content }}</p>
                            @if($ann->expires_at)
                                <p class="text-[9px] text-red-400 font-semibold mt-0.5 uppercase tracking-wide">
                                    <i class="fas fa-clock mr-0.5"></i> Kedaluwarsa {{ $ann->expires_at->diffForHumans() }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 hidden sm:table-cell">
                            @php
                                $badge = match($ann->type) {
                                    'urgent'  => 'bg-red-100 text-red-700',
                                    'warning' => 'bg-amber-100 text-amber-700',
                                    default   => 'bg-blue-100 text-blue-700',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $badge }}">
                                @if($ann->type === 'urgent')<i class="fas fa-bell text-[8px]"></i>@elseif($ann->type === 'warning')<i class="fas fa-exclamation-triangle text-[8px]"></i>@else<i class="fas fa-info-circle text-[8px]"></i>@endif
                                {{ $ann->type_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <p class="text-xs font-semibold text-gray-700 capitalize">
                                {{ $ann->target_role === 'all' ? 'Semua Pengguna' : ucfirst($ann->target_role) }}
                            </p>
                            @if($ann->target_class_id)
                                <p class="text-[10px] text-gray-400 mt-0.5">Kelas: {{ $ann->target_class_id }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 hidden lg:table-cell">
                            <p class="text-xs font-semibold text-gray-700">{{ $ann->sender->formatted_name }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $ann->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($ann->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-wide">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-gray-100 text-gray-500 text-[10px] font-black uppercase tracking-wide">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 hidden sm:table-cell">
                            @if($ann->show_on_login)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-wide">
                                    <i class="fas fa-check text-[8px]"></i> Ya
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-400 text-[10px] font-black uppercase tracking-wide">
                                    <i class="fas fa-minus text-[8px]"></i> Tidak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                {{-- EDIT BUTTON --}}
                                @can('update', $ann)
                                <a href="{{ route('communication.announcements.edit', $ann) }}"
                                   class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                   title="Edit Pengumuman">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                @endcan

                                @can('toggleActive', \App\Models\Announcement::class)
                                <form action="{{ route('communication.announcements.toggle', $ann) }}" method="POST" class="no-loading">
                                    @csrf
                                    <button type="submit" class="p-2 text-gray-400 hover:text-[var(--brand-primary)] hover:bg-[var(--brand-glow)] rounded-lg transition" title="{{ $ann->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas {{ $ann->is_active ? 'fa-eye-slash' : 'fa-eye' }} text-sm"></i>
                                    </button>
                                </form>
                                @endcan

                                @can('delete', $ann)
                                <form action="{{ route('communication.announcements.destroy', $ann) }}" method="POST" class="no-loading"
                                      onsubmit="return confirm('Nonaktifkan pengumuman ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition" title="Nonaktifkan">
                                        <i class="fas fa-ban text-sm"></i>
                                    </button>
                                </form>
                                @endcan

                                @can('permanentDelete', $ann)
                                <form id="force-delete-{{ $ann->id }}"
                                      action="{{ route('communication.announcements.force-delete', $ann) }}"
                                      method="POST" class="no-loading"
                                      onsubmit="return confirm('Hapus PERMANEN? Data tidak bisa dikembalikan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                            title="Hapus Permanen">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($announcements->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $announcements->links() }}
        </div>
        @endif
        @endif
    </div>

    <script>
    function confirmPermanentDelete(id, title) {
        Swal.fire({
            icon: 'warning',
            title: 'Hapus Permanen?',
            html: `
                <p class="text-sm text-gray-600 mt-1">Pengumuman berikut akan <strong class="text-red-600">dihapus dari database secara permanen</strong> dan tidak dapat dikembalikan:</p>
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-xl text-left">
                    <p class="text-sm font-black text-red-800">${title}</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash mr-2"></i> Ya, Hapus Permanen',
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
                document.getElementById(`force-delete-${id}`).submit();
            }
        });
    }
    </script>
</div>
@endsection
