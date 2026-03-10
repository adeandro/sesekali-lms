@extends('layouts.app')

@section('title', 'Manajemen Pengguna - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('page-title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background: var(--brand-glow)">
                    <i class="fas fa-users-cog text-[var(--brand-primary)]"></i>
                </div>
                Manajemen Pengguna
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun Admin dan Guru.</p>
        </div>
        <a href="{{ route('superadmin.teachers.create') }}"
           class="inline-flex items-center gap-2 theme-primary-btn px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm">
            <i class="fas fa-user-plus"></i> Tambah Pengguna
        </a>
    </div>

    {{-- Search Bar --}}
    <div class="theme-surface-card rounded-2xl p-4 theme-soft-shadow">
        <form action="{{ route('superadmin.teachers.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" placeholder="Cari berdasarkan nama, email, atau NIP/ID..." value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent transition">
            </div>
            <button type="submit" class="theme-secondary-btn px-6 py-2.5 rounded-xl text-sm font-bold shrink-0 shadow-sm border border-gray-100">
                <i class="fas fa-filter mr-1"></i> Cari
            </button>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="theme-surface-card rounded-2xl overflow-hidden theme-soft-shadow border-0">
        @if($teachers->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background: var(--brand-glow)">
                <i class="fas fa-users-slash text-[var(--brand-primary)] text-3xl"></i>
            </div>
            <p class="text-base font-bold text-gray-700">Tidak ada data ditemukan</p>
            <p class="text-sm text-gray-400 mt-1">@if(request('search')) Coba kata kunci pencarian yang lain. @else Belum ada data pengguna yang terdaftar. @endif</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest">NIS/ID</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest">Nama & Email</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest">Peran & Status</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest hidden md:table-cell">Mata Pelajaran</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($teachers as $teacher)
                    <tr class="hover:bg-blue-50/20 transition-colors group">
                        <td class="px-6 py-4 align-top">
                            <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold tracking-wide mt-1">
                                {{ $teacher->nis }}
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="flex items-start gap-4">
                                <img src="{{ $teacher->photo_url }}" alt="{{ $teacher->name }}" class="w-10 h-10 rounded-full object-cover shadow-sm border border-gray-100 mt-1">
                                <div>
                                    <p class="font-bold text-gray-900 text-sm group-hover:text-[var(--brand-primary)] transition-colors">{{ $teacher->full_name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-envelope mr-1 text-gray-300"></i> {{ $teacher->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="flex flex-col items-start gap-1.5 mt-1">
                                @if($teacher->role === 'superadmin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 text-[10px] font-black uppercase tracking-widest">
                                        <i class="fas fa-crown text-[8px]"></i> Super Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 border border-sky-100 text-[10px] font-black uppercase tracking-widest">
                                        <i class="fas fa-chalkboard-teacher text-[8px]"></i> Guru
                                    </span>
                                @endif

                                @if($teacher->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest">
                                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span> Nonaktif
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top hidden md:table-cell">
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @forelse($teacher->subjects as $subject)
                                    <span class="px-2.5 py-1 inline-flex text-[10px] items-center gap-1 leading-tight font-bold rounded-lg border border-gray-200 text-gray-600 bg-white shadow-sm">
                                        <i class="fas fa-book text-gray-300"></i> {{ $subject->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400 italic">Belum diatur</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top text-right">
                            <div class="flex items-center justify-end gap-1 mt-1">
                                <a href="{{ route('superadmin.teachers.edit', $teacher) }}"
                                   class="p-2 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition" title="Edit Pengguna">
                                    <i class="fas fa-pen text-sm"></i>
                                </a>
                                <form id="delete-form-{{ $teacher->id }}" action="{{ route('superadmin.teachers.destroy', $teacher) }}" method="POST" class="no-loading inline">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDeleteTeacher({{ $teacher->id }}, '{{ addslashes($teacher->full_name) }}')"
                                            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus Pengguna">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($teachers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $teachers->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

<script>
function confirmDeleteTeacher(id, name) {
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Pengguna?',
        html: `<p class="text-sm text-gray-600 mt-1">Anda yakin ingin menghapus akun <strong class="text-gray-900">${name}</strong>? Tindakan ini tidak dapat dibatalkan.</p>`,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash-alt mr-1.5"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup:   'rounded-3xl shadow-2xl border-0',
            confirm: 'rounded-xl px-5 py-2.5 font-black text-xs uppercase tracking-wide shadow-sm hover:shadow-md transition-shadow',
            cancel:  'rounded-xl px-5 py-2.5 font-bold text-xs',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    });
}
</script>
@endsection
