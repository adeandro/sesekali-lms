@extends('layouts.app')

@section('title', 'Inisialisasi Migrasi Tahunan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <a href="{{ route('admin.students.index') }}" class="text-xs text-gray-400 hover:text-gray-700 font-bold">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Manajemen Siswa
        </a>
        <h1 class="text-2xl font-black text-gray-900 mt-2 uppercase tracking-tight flex items-center gap-3">
            <i class="fas fa-graduation-cap text-rose-500"></i> Inisialisasi Migrasi Tahunan
        </h1>
        <p class="text-sm text-gray-500 mt-1">Naikkan kelas siswa Grade 10 & 11, serta wisudakan Grade 12 menjadi Alumni.</p>
    </div>

    {{-- Danger Banner --}}
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 flex gap-4">
        <i class="fas fa-exclamation-triangle text-rose-500 text-xl flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="font-black text-rose-700">Operasi ini TIDAK DAPAT DIBATALKAN!</p>
            <ul class="text-rose-600 text-sm mt-1 space-y-0.5 list-disc list-inside">
                <li>Grade 12 akan dinonaktifkan (<code class="bg-rose-100 px-1 rounded">status = Alumni</code>)</li>
                <li>Grade 10 → 11, Grade 11 → 12 secara massal</li>
                <li>Semua <code class="bg-rose-100 px-1 rounded">class_id</code> siswa yang dipromosikan akan di-reset ke NULL (perlu Re-mapping)</li>
                <li>Aksi ini dicatat di <strong>Migration Logs</strong> untuk audit trail</li>
            </ul>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['label'=>'Kelas 10','count'=>$stats['grade_10'],'color'=>'emerald','icon'=>'fa-arrow-up','action'=>'→ Naik ke Kelas 11'],
            ['label'=>'Kelas 11','count'=>$stats['grade_11'],'color'=>'blue','icon'=>'fa-arrow-up','action'=>'→ Naik ke Kelas 12'],
            ['label'=>'Kelas 12','count'=>$stats['grade_12'],'color'=>'rose','icon'=>'fa-graduation-cap','action'=>'→ Menjadi Alumni'],
            ['label'=>'Belum Dipetakan','count'=>$stats['unmapped'],'color'=>'amber','icon'=>'fa-exclamation','action'=>'Perlu Re-mapping'],
        ] as $stat)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
                <div class="w-10 h-10 rounded-full bg-{{ $stat['color'] }}-100 text-{{ $stat['color'] }}-600 flex items-center justify-center mx-auto mb-2">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <p class="text-3xl font-black text-gray-900">{{ number_format($stat['count']) }}</p>
                <p class="text-xs font-bold text-gray-700 mt-0.5">{{ $stat['label'] }}</p>
                <p class="text-[10px] text-gray-400 mt-1">{{ $stat['action'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Migration Form --}}
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-8 space-y-6">
        <h2 class="font-black text-gray-900 uppercase tracking-widest text-sm border-b border-gray-100 pb-3">
            <i class="fas fa-sliders-h mr-2 text-indigo-500"></i> Konfigurasi Migrasi
        </h2>

        <form action="{{ route('admin.students.migration.execute') }}" method="POST" id="migrationForm">
            @csrf

            {{-- Academic Year --}}
            <div class="mb-5">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                    Tahun Ajaran Baru <span class="text-red-500">*</span>
                </label>
                <input type="text" name="academic_year"
                       value="{{ old('academic_year', date('Y') . '/' . (date('Y') + 1)) }}"
                       placeholder="2025/2026" required
                       pattern="\d{4}\/\d{4}"
                       class="w-full max-w-xs px-4 py-2.5 rounded-2xl border {{ $errors->has('academic_year') ? 'border-red-400' : 'border-gray-200' }} text-sm font-bold focus:ring-2 focus:ring-rose-400 focus:outline-none">
                @error('academic_year')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                <p class="text-[10px] text-gray-400 mt-1">Format: YYYY/YYYY, contoh: 2025/2026</p>
            </div>

            {{-- Confirmation --}}
            <div class="mb-6">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                    Ketik <span class="font-mono bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded text-[11px]">MIGRASI</span> untuk melanjutkan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="confirm" placeholder="MIGRASI" required
                       class="w-full max-w-xs px-4 py-2.5 rounded-2xl border {{ $errors->has('confirm') ? 'border-red-400' : 'border-gray-200' }} text-sm font-bold font-mono uppercase tracking-widest focus:ring-2 focus:ring-rose-400 focus:outline-none">
                @error('confirm')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    onclick="return confirm('⚠️ PERHATIAN: Migrasi tahunan akan dijalankan sekarang. Tindakan ini permanen dan tidak dapat dibatalkan. Lanjutkan?')"
                    class="w-full py-4 bg-gradient-to-r from-rose-500 to-pink-600 text-white font-black text-sm uppercase tracking-widest rounded-2xl shadow-lg hover:shadow-rose-500/40 hover:-translate-y-0.5 transition-all">
                <i class="fas fa-graduation-cap mr-2"></i> Jalankan Migrasi Tahunan 🎓
            </button>
        </form>
    </div>

    {{-- Recent Migration Logs --}}
    @if($recentLogs->isNotEmpty())
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-black text-gray-900 text-sm uppercase tracking-widest"><i class="fas fa-history mr-2 text-gray-400"></i> Riwayat Migrasi Terakhir</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($recentLogs as $log)
                <div class="px-6 py-3 flex items-center gap-4">
                    <span class="text-lg">{{ match($log->action_type) { 'graduate'=>'🎓', 'promote'=>'📈', 'remap'=>'🗂️', default=>'📋' } }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-900">{{ $log->actionLabel() }}</p>
                        <p class="text-[10px] text-gray-400">{{ $log->executor?->name }} · {{ $log->executed_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-black text-gray-700 text-sm">{{ $log->affected_count }} siswa</span>
                        <p class="text-[10px] text-gray-400">{{ $log->academic_year }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
