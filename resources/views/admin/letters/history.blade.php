@extends('layouts.app')

@section('title', 'Arsip Surat - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Arsip Surat')

@section('content')
<div class="space-y-8 animate-fadeIn pb-20" x-data="{ showFilters: false }">
    <!-- Header & Stats -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1">
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-gray-400">📍 Administrasi — Arsip Surat</p>
            <h2 class="text-2xl font-black tracking-tight" style="color: var(--brand-text);">Riwayat <span style="color: var(--brand-primary);">Terbit Surat</span></h2>
            <p class="text-sm font-bold text-gray-500 italic uppercase tracking-tighter">Total {{ $letters->total() }} surat telah diterbitkan tahun ini</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button @click="showFilters = !showFilters" 
                    class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-100 rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-indigo-600 transition-all shadow-sm">
                <i class="fas fa-filter" :class="{ 'text-indigo-600': showFilters }"></i> 
                <span x-text="showFilters ? 'Tutup Filter' : 'Buka Filter'">Buka Filter</span>
            </button>
            @if($letters->count() > 0)
                <form id="deleteAllHistoryForm" action="{{ route('admin.letters.history.deleteAll') }}" 
                    method="POST" class="no-loading hidden">
                    @csrf
                    @method('DELETE')
                </form>
                <button type="button" @click="confirmDeleteAllHistory()"
                        class="flex items-center gap-2 px-5 py-3 bg-rose-50 text-rose-600 border border-rose-100 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                    <i class="fas fa-trash-alt"></i> Hapus Semua
                </button>
            @endif
            <a href="{{ route('admin.letters.index') }}" 
               class="flex items-center gap-2 px-5 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                <i class="fas fa-plus-circle"></i> Buat Surat Baru
            </a>
        </div>
    </div>

    <!-- Filter Bar (Alpine.js Collapsible) -->
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8"
         style="border-color: var(--brand-glow);">
        
        <form action="{{ route('admin.letters.history') }}" method="GET" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Search -->
                <div class="space-y-2 lg:col-span-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Pencarian</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Nama penerima atau nomor surat..."
                               class="w-full pl-12 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300">
                    </div>
                </div>

                <!-- Template -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Template</label>
                    <select name="template_id" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all">
                        <option value="">Semua Template</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}" {{ request('template_id') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jenis -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Jenis Penerima</label>
                    <select name="recipient_type" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all">
                        <option value="">Semua Jenis</option>
                        <option value="student" {{ request('recipient_type') == 'student' ? 'selected' : '' }}>Siswa</option>
                        <option value="teacher" {{ request('recipient_type') == 'teacher' ? 'selected' : '' }}>Guru / Pegawai</option>
                    </select>
                </div>

                <!-- Tahun -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Tahun</label>
                    <select name="year" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all">
                </div>

                <!-- Date To -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route('admin.letters.history') }}" class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-rose-500 transition-all">Reset Filter</a>
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Archive Table -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden" style="border-color: var(--brand-glow);">
        <div class="overflow-x-auto overflow-y-visible">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-50">
                        <th class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Nomor Surat</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Penerima</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Template</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Terbit</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Petugas</th>
                        <th class="px-8 py-6 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($letters as $letter)
                        <tr class="group hover:bg-gray-50/50 transition-all">
                            <td class="px-8 py-6">
                                <span class="text-sm font-black text-gray-900 font-mono tracking-tighter bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200">
                                    {{ $letter->letter_number }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black uppercase {{ $letter->recipient_type === 'student' ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600' }}">
                                        {{ substr($letter->recipient_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 leading-tight">{{ $letter->recipient_name }}</p>
                                        <span class="text-[10px] font-black uppercase tracking-widest {{ $letter->recipient_type === 'student' ? 'text-indigo-400' : 'text-amber-400' }}">
                                            {{ $letter->recipient_type === 'student' ? 'Siswa' : 'Guru / Pegawai' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-sm font-bold text-gray-500 uppercase tracking-tighter">
                                {{ $letter->template->name ?? 'Template Dihapus' }}
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-bold text-gray-900">{{ $letter->issued_date->locale('id')->translatedFormat('d F Y') }}</p>
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 leading-relaxed">{{ $letter->created_at->format('H:i') }} WIB</span>
                            </td>
                            <td class="px-8 py-6 text-sm font-bold text-gray-500 tracking-tight">
                                {{ $letter->creator->full_name ?? 'System' }}
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.letters.redownload', $letter) }}" 
                                       class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm"
                                       title="Download PDF">
                                        <i class="fas fa-download text-sm"></i>
                                    </a>
                                    
                                    <form id="delete-form-{{ $letter->id }}" 
                                          action="{{ route('admin.letters.delete', $letter) }}" 
                                          method="POST" class="hidden no-loading">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" onclick="confirmDeleteLetter({{ $letter->id }}, '{{ $letter->letter_number }}')"
                                            class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm"
                                            title="Hapus">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center">
                                <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mx-auto text-gray-200 mb-6">
                                    <i class="fas fa-history text-3xl"></i>
                                </div>
                                <h3 class="text-lg font-black text-gray-400 uppercase tracking-widest">Belum Ada Riwayat</h3>
                                <p class="text-sm text-gray-400 mt-2 italic">Data surat yang Anda terbitkan akan muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($letters->hasPages())
            <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/30">
                {{ $letters->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function confirmDeleteLetter(id, number) {
        Swal.fire({
            title: 'Hapus Riwayat Surat?',
            html: `Anda akan menghapus rekaman surat nomor <br><b class="text-rose-600 font-mono text-sm">${number}</b> secara permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                if(document.getElementById('loading-overlay')) document.getElementById('loading-overlay').style.display = 'block';
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    function confirmDeleteAllHistory() {
        Swal.fire({
            title: 'HAPUS SEMUA RIWAYAT?',
            html: 'Tindakan ini akan menghapus <b>SELURUH</b> rekaman terbit surat selamanya. Data tidak dapat dipulihkan!',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#be123c',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'HAPUS SELAMANYA',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                if(document.getElementById('loading-overlay')) document.getElementById('loading-overlay').style.display = 'block';
                document.getElementById('deleteAllHistoryForm').submit();
            }
        });
    }
</script>

<style>
    .no-loading { pointer-events: auto !important; }
</style>
@endsection
