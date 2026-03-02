@extends('layouts.app')

@section('title', 'Kelola Token - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Manajemen Token Global')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header & Stats Overview -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="space-y-2">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight text-center md:text-left uppercase">Token Ujian Global</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center md:text-left leading-relaxed">
                Sistem Token Dinamis: <span class="text-indigo-600">Satu Ujian, Satu Akses Global</span>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-indigo-100/50">
                <i class="fas fa-key text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Ujian</p>
                <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors">{{ $exams->count() }}</p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-emerald-100/50">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Published</p>
                <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-emerald-600 transition-colors">{{ $exams->where('status', 'published')->count() }}</p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-amber-100/50">
                <i class="fas fa-file-signature text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Draft / Lainnya</p>
                <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-amber-600 transition-colors">{{ $exams->where('status', '!=', 'published')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.tokens.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
            <div class="md:col-span-5">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Cari Ujian</label>
                <div class="relative group">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors"></i>
                    <input type="text" name="search" placeholder="Masukkan nama ujian atau token..." value="{{ request('search') }}"
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl pl-12 pr-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                </div>
            </div>
            <div class="md:col-span-4">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Status Token</label>
                <select name="status" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Token Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Belum Ada Token</option>
                </select>
            </div>
            <div class="md:col-span-3 flex gap-3">
                <button type="submit" class="flex-1 h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                    <i class="fas fa-filter text-[10px]"></i> Filter
                </button>
                <a href="{{ route('admin.tokens.index') }}" class="w-14 h-14 bg-gray-50 text-gray-400 rounded-2xl hover:bg-gray-100 hover:text-gray-600 transition flex items-center justify-center">
                    <i class="fas fa-redo-alt text-xs"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            @if($exams->count() > 0)
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Informasi Ujian</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Token Aktif</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Auto Refresh</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($exams as $exam)
                        <tr class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 tracking-tight mb-1">{{ $exam->title }}</p>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">{{ $exam->subject->name ?? 'Mata Pelajaran' }}</span>
                                        <span class="text-gray-200">•</span>
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ $exam->duration_minutes }} Menit</span>
                                    </div>
                                    <p class="text-[9px] font-bold text-gray-300 uppercase tracking-widest mt-2">
                                        <i class="far fa-calendar-alt mr-1"></i> {{ $exam->start_time?->format('d M Y, H:i') ?? 'Belum dijadwalkan' }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'published' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'ongoing' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                        'finished' => 'bg-gray-50 text-gray-500 border-gray-100'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'DRAFT',
                                        'published' => 'PUBLISHED',
                                        'ongoing' => 'BERJALAN',
                                        'finished' => 'SELESAI'
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $statusClasses[$exam->status] ?? $statusClasses['draft'] }}">
                                    {{ $statusLabels[$exam->status] ?? $exam->status }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                @if($exam->token && $exam->status === 'published')
                                    <div class="bg-indigo-50/50 rounded-2xl p-4 border border-indigo-100/50 group-hover:bg-indigo-600 group-hover:border-indigo-600 transition-all duration-300">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[8px] font-black text-indigo-400 group-hover:text-indigo-200 uppercase tracking-widest transition-colors">TOKEN AKTIF</span>
                                            <button onclick="copyTokenToClipboard('{{ $exam->token }}', '{{ $exam->title }}')" class="text-indigo-400 group-hover:text-white transition-colors hover:scale-110">
                                                <i class="far fa-copy text-xs"></i>
                                            </button>
                                        </div>
                                        <p class="text-2xl font-black text-indigo-900 group-hover:text-white tracking-[0.3em] font-mono transition-colors">
                                            {{ $exam->token }}
                                        </p>
                                        <div class="mt-2 text-[8px] font-bold text-indigo-300 group-hover:text-indigo-100 uppercase tracking-widest transition-colors">
                                            Berlaku hingga: {{ $exam->tokenRefreshTime()?->format('H:i') ?? '-' }}
                                        </div>
                                    </div>
                                @elseif($exam->status === 'published')
                                    <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100">
                                        <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest leading-relaxed text-center">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Ujian belum memiliki token
                                        </p>
                                    </div>
                                @else
                                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest text-center italic">
                                            Publish untuk aktifkan token
                                        </p>
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-center">
                                @if($exam->token && $exam->status === 'published')
                                    @if($exam->tokenNeedsRefresh())
                                        <div class="space-y-1">
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 text-rose-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span> Butuh Refresh
                                            </div>
                                            <p class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ $exam->token_last_updated->diffInMinutes(now()) }}m lalu</p>
                                        </div>
                                    @else
                                        <div class="space-y-1">
                                            <p class="text-[14px] font-black text-indigo-600 tracking-tight">{{ $exam->minutesUntilTokenRefresh() }}</p>
                                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest transition-colors">Menit Tersisa</p>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center justify-center gap-2">
                                    @if($exam->token && $exam->status === 'published')
                                        <button onclick="confirmRefreshToken('{{ $exam->id }}', '{{ $exam->title }}')" 
                                            class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-600 hover:text-white transition flex items-center justify-center"
                                            title="Refresh Token">
                                            <i class="fas fa-sync-alt text-xs"></i>
                                        </button>
                                        <a href="{{ route('admin.exams.show', $exam->id) }}" 
                                            class="w-10 h-10 bg-gray-50 text-gray-400 rounded-xl hover:bg-indigo-600 hover:text-white transition flex items-center justify-center"
                                            title="Detail Ujian">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    @elseif($exam->status === 'published')
                                        <form method="POST" action="{{ route('admin.exams.generate-token', $exam->id) }}" onsubmit="return handleGenerateToken(event)">
                                            @csrf
                                            <button type="submit" class="h-10 px-4 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 hover:text-white transition flex items-center gap-2">
                                                <i class="fas fa-bolt text-[9px]"></i> Generate
                                            </button>
                                        </form>
                                    @else
                                        <div class="w-10 h-10 bg-gray-50 text-gray-200 rounded-xl flex items-center justify-center cursor-not-allowed">
                                            <i class="fas fa-lock text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-20 text-center animate-fadeIn">
                    <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center text-gray-200 mx-auto mb-6 transform rotate-6">
                        <i class="fas fa-key text-3xl"></i>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest max-w-xs mx-auto leading-relaxed italic">Tidak ada data ujian yang ditemukan untuk kriteria ini.</p>
                </div>
            @endif
        </div>

        @if($exams->hasPages())
            <div class="p-8 bg-gray-50/50 border-t border-gray-50">
                {{ $exams->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    <!-- System Info -->
    <div class="bg-indigo-600 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-indigo-200 group">
        <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
        <div class="relative z-10 flex flex-col md:flex-row gap-10">
            <div class="w-20 h-20 bg-white/20 rounded-3xl flex items-center justify-center shrink-0 border border-white/20 backdrop-blur-md">
                <i class="fas fa-info-circle text-3xl"></i>
            </div>
            <div class="space-y-6">
                <div>
                    <h3 class="text-xl font-black uppercase tracking-tight mb-2">Pusat Informasi Token</h3>
                    <p class="text-indigo-100 text-[10px] font-bold uppercase tracking-widest leading-relaxed">Panduan penggunaan kolektif sistem token global sesekalicbt</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center shrink-0 mt-0.5"><i class="fas fa-check text-[10px]"></i></div>
                        <p class="text-[11px] font-bold text-indigo-50 leading-relaxed uppercase tracking-wide">1 Token berlaku untuk satu ujian secara global di seluruh cluster.</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center shrink-0 mt-0.5"><i class="fas fa-sync text-[10px]"></i></div>
                        <p class="text-[11px] font-bold text-indigo-50 leading-relaxed uppercase tracking-wide">Auto-Refresh setiap 20-30 menit untuk keamanan sesi berlapis.</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center shrink-0 mt-0.5"><i class="fas fa-bolt text-[10px]"></i></div>
                        <p class="text-[11px] font-bold text-indigo-50 leading-relaxed uppercase tracking-wide">Refresh Manual akan menonaktifkan token lama secara instan.</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center shrink-0 mt-0.5"><i class="fas fa-shield-alt text-[10px]"></i></div>
                        <p class="text-[11px] font-bold text-indigo-50 leading-relaxed uppercase tracking-wide">Siswa cukup validasi satu kali, validitas sesi bertahan 120 menit.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function copyTokenToClipboard(token, examTitle) {
        navigator.clipboard.writeText(token).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon: 'success',
                title: `<span class="text-xs font-black uppercase tracking-widest font-sans">Token Disalin: ${token}</span>`,
                customClass: {
                    popup: 'rounded-2xl border-2 border-emerald-100 shadow-xl'
                }
            });
        }).catch(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                icon: 'error',
                title: 'Gagal menyalin token'
            });
        });
    }

    async function confirmRefreshToken(examId, examTitle) {
        const result = await Swal.fire({
            title: 'REFRESH TOKEN?',
            html: `
                <div class="text-left space-y-4 p-2">
                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-4 border-b border-amber-50 pb-4">
                        Perhatian: <span class="text-amber-600">Keamanan Ujian</span>
                    </p>
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest leading-relaxed">
                        Anda akan me-generate token baru untuk: <br>
                        <span class="text-indigo-600 font-black">${examTitle}</span>
                    </p>
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 italic">
                        <p class="text-[9px] font-black text-amber-600 uppercase tracking-[0.1em] leading-relaxed">
                            Peringatan: Token lama tidak akan dapat digunakan lagi setelah proses ini. Siswa yang sedang memuat halaman instruksi mungkin perlu memuat ulang halaman.
                        </p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'YA, REFRESH TOKEN',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: 'rounded-[1.5rem] p-8',
                title: 'text-lg font-black tracking-tight text-gray-900 uppercase',
                confirmButton: 'h-14 bg-amber-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 order-2 border-0',
                cancelButton: 'h-14 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 order-1 border-0'
            },
            buttonsStyling: false
        });

        if (result.isConfirmed) {
            Swal.fire({
                title: 'MEMPROSES...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                customClass: { popup: 'rounded-[1.5rem]' }
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/exams/${examId}/refresh-token`;
            form.innerHTML = `@csrf`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function handleGenerateToken(e) {
        e.preventDefault();
        const form = e.target;
        
        Swal.fire({
            title: 'GENERATE TOKEN?',
            text: 'Buat token baru untuk ujian ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'GENERATE',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: 'rounded-[1.5rem]',
                confirmButton: 'h-14 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 border-0',
                cancelButton: 'h-14 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 border-0 shadow-none'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'MENERBITKAN...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    customClass: { popup: 'rounded-[1.5rem]' }
                });
                form.submit();
            }
        });
        return false;
    }
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .hidden { display: none !important; }
</style>
@endsection
