@extends('layouts.app')

@section('title', 'Grand Migration — ' . $season->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <a href="{{ route('admin.gamification.seasons.index') }}" class="text-xs text-gray-400 hover:text-gray-700 font-bold"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
        <h1 class="text-2xl font-black text-gray-900 mt-2 uppercase tracking-tight flex items-center gap-3">
            <i class="fas fa-graduation-cap text-rose-500"></i> Grand Migration
        </h1>
        <p class="text-sm text-gray-500 mt-1">{{ $season->name }} — Tahun Ajaran {{ $season->academic_year }}</p>
    </div>

    {{-- Warning Banner --}}
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 flex gap-4">
        <i class="fas fa-exclamation-triangle text-rose-500 mt-0.5 text-lg flex-shrink-0"></i>
        <div>
            <p class="font-black text-rose-700 text-sm">Operasi ini TIDAK BISA dibatalkan!</p>
            <p class="text-rose-600 text-xs mt-1">Kelas 12 akan diarsipkan sebagai Alumni dan tidak dapat login. Kelas 10 & 11 akan naik satu tingkat. Pastikan semua data sudah benar.</p>
        </div>
    </div>

    <form action="{{ route('admin.gamification.seasons.migration.execute', $season) }}" method="POST" id="migrationForm">
        @csrf

        {{-- Kelas 12 — Graduation --}}
        <div class="bg-white rounded-[2rem] border border-rose-100 shadow-sm overflow-hidden mb-5">
            <div class="bg-gradient-to-r from-rose-500 to-pink-500 px-6 py-4">
                <h2 class="font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-graduation-cap"></i> Kelas 12 — Akan Diwisuda ({{ $grade12->count() }} siswa)
                </h2>
                <p class="text-rose-200 text-xs mt-1">Centang "Tahan" untuk siswa yang TIDAK naik kelas (tinggal kelas).</p>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($grade12 as $student)
                    <div class="flex items-center px-6 py-3 gap-3 hover:bg-gray-50">
                        <div class="flex-1">
                            <p class="font-bold text-gray-900 text-sm">{{ $student->name }}</p>
                            <p class="text-[10px] text-gray-400">NIS: {{ $student->nis ?? '-' }} · Kelas {{ $student->grade_level }}</p>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="stay_behind_ids[]" value="{{ $student->id }}"
                                   class="rounded border-gray-300 text-rose-500 focus:ring-rose-400">
                            <span class="text-xs font-bold text-rose-600">Tahan (Tinggal Kelas)</span>
                        </label>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-check-circle text-2xl text-gray-200 mb-2 block"></i>
                        Tidak ada siswa Kelas 12.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Kelas 10 & 11 — Promotion --}}
        <div class="bg-white rounded-[2rem] border border-emerald-100 shadow-sm overflow-hidden mb-5">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-4">
                <h2 class="font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-arrow-up"></i> Kelas 10 & 11 — Akan Naik ({{ $gradeToPromote->count() }} siswa)
                </h2>
                <p class="text-emerald-200 text-xs mt-1">Centang "Tahan" untuk menghentikan kenaikan kelas siswa tertentu.</p>
            </div>
            <div class="divide-y divide-gray-50 max-h-96 overflow-y-auto custom-scrollbar">
                @forelse($gradeToPromote as $student)
                    <div class="flex items-center px-6 py-3 gap-3 hover:bg-gray-50">
                        <div class="flex-1">
                            <p class="font-bold text-gray-900 text-sm">{{ $student->name }}</p>
                            <p class="text-[10px] text-gray-400">NIS: {{ $student->nis ?? '-' }} · Kelas {{ $student->grade_level }} → {{ $student->grade_level + 1 }}</p>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="stay_behind_ids[]" value="{{ $student->id }}"
                                   class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                            <span class="text-xs font-bold text-orange-600">Tahan (Tinggal Kelas)</span>
                        </label>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">Tidak ada siswa Kelas 10/11.</div>
                @endforelse
            </div>
        </div>

        {{-- Confirmation --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 space-y-4">
            <h2 class="font-black text-gray-900 uppercase tracking-widest text-sm">Konfirmasi Eksekusi</h2>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                    Tahun Ajaran Alumni <span class="text-red-500">*</span>
                </label>
                <input type="text" name="academic_year" value="{{ $season->academic_year }}" placeholder="2025/2026"
                       class="w-full max-w-xs px-4 py-2.5 rounded-2xl border border-gray-200 text-sm font-bold focus:ring-2 focus:ring-rose-400 focus:outline-none"
                       required>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                    Ketik <span class="font-mono bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded">KONFIRMASI</span> untuk melanjutkan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="confirm" placeholder="KONFIRMASI"
                       class="w-full max-w-xs px-4 py-2.5 rounded-2xl border border-gray-200 text-sm font-bold font-mono focus:ring-2 focus:ring-rose-400 focus:outline-none uppercase tracking-widest"
                       required>
                @error('confirm')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    onclick="return confirm('⚠️ PERHATIAN: Tindakan ini permanen. Yakin ingin menjalankan Grand Migration?')"
                    class="w-full py-4 bg-gradient-to-r from-rose-500 to-pink-600 text-white font-black text-sm uppercase tracking-widest rounded-2xl shadow-lg hover:shadow-rose-500/30 hover:-translate-y-0.5 transition-all">
                <i class="fas fa-graduation-cap mr-2"></i> Jalankan Grand Migration 🎓
            </button>
        </div>

    </form>
</div>
@endsection
