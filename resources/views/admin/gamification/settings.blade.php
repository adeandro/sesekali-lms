@extends('layouts.app')

@section('title', 'Gamification Center — Global Settings')
@section('page-title', 'Gamification: Global Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-8 animate-fadeIn pb-12">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-500/20"
             style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
            <i class="fas fa-trophy text-2xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-black tracking-tight" style="color: var(--brand-text);">Gamification Center</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Global Settings — Kontrol sistem gamifikasi untuk seluruh siswa</p>
        </div>
    </div>

    {{-- Sub-nav tabs --}}
    <div class="flex gap-2 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100">
        <a href="{{ route('admin.gamification.settings') }}"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300
                  bg-purple-600 text-white shadow-lg shadow-purple-200">
            <i class="fas fa-sliders-h text-sm"></i> Global Settings
        </a>
        <a href="{{ route('admin.gamification.achievements') }}"
           class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300
                  text-gray-500 hover:bg-gray-50">
            <i class="fas fa-medal text-sm"></i> Achievement Manager
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-bold">
        <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Settings Card --}}
    <form action="{{ route('admin.gamification.settings.update') }}" method="POST">
        @csrf
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-purple-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                    <i class="fas fa-gamepad text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Kontrol Fitur Gamifikasi</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Aktifkan atau nonaktifkan fitur untuk seluruh siswa secara global</p>
                </div>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">

                {{-- Gamification Toggle --}}
                <div class="space-y-3" x-data="{ gamification: {{ (old('enable_gamification', $allSettings['enable_gamification'] ?? '1') == '1') ? 'true' : 'false' }} }">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Sistem Gamifikasi (Achievement & XP)</label>
                    <div class="relative flex items-center justify-between w-full h-16 bg-gray-50 rounded-2xl px-6 border border-transparent transition-all group-hover:border-purple-100 overflow-hidden">
                        <div class="absolute inset-0 opacity-0 transition-opacity duration-300" :class="gamification ? 'opacity-10' : ''"
                             style="background: linear-gradient(135deg, #7c3aed22, #4f46e522);"></div>
                        <div class="flex items-center gap-3 z-10">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300"
                                 :class="gamification ? 'bg-purple-100 text-purple-600' : 'bg-gray-200 text-gray-400'">
                                <i class="fas fa-trophy text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-900 uppercase tracking-widest" x-text="gamification ? '🟢 Aktif' : '🔴 Non-Aktif'"></span>
                        </div>
                        <input type="hidden" name="enable_gamification" :value="gamification ? '1' : '0'">
                        <button type="button" @click="gamification = !gamification"
                                class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-none focus:ring-4 focus:ring-purple-500/20 z-10"
                                :class="gamification ? 'bg-purple-600' : 'bg-gray-300'">
                            <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition-transform duration-300 ease-in-out"
                                  :class="gamification ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    <p class="text-[9px] text-gray-400 ml-1 leading-relaxed">Mengontrol XP, level, tema premium, avatar kustom, dan semua fitur pencapaian siswa.</p>
                </div>

                {{-- Leaderboard Toggle --}}
                <div class="space-y-3" x-data="{ leaderboard: {{ (old('enable_leaderboard', $allSettings['enable_leaderboard'] ?? '1') == '1') ? 'true' : 'false' }} }">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Leaderboard & Peringkat Siswa</label>
                    <div class="relative flex items-center justify-between w-full h-16 bg-gray-50 rounded-2xl px-6 border border-transparent transition-all group-hover:border-purple-100 overflow-hidden">
                        <div class="absolute inset-0 opacity-0 transition-opacity duration-300" :class="leaderboard ? 'opacity-10' : ''"
                             style="background: linear-gradient(135deg, #06b6d422, #0284c722);"></div>
                        <div class="flex items-center gap-3 z-10">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300"
                                 :class="leaderboard ? 'bg-cyan-100 text-cyan-600' : 'bg-gray-200 text-gray-400'">
                                <i class="fas fa-ranking-star text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-900 uppercase tracking-widest" x-text="leaderboard ? '🟢 Aktif' : '🔴 Non-Aktif'"></span>
                        </div>
                        <input type="hidden" name="enable_leaderboard" :value="leaderboard ? '1' : '0'">
                        <button type="button" @click="leaderboard = !leaderboard"
                                class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-none focus:ring-4 focus:ring-cyan-500/20 z-10"
                                :class="leaderboard ? 'bg-cyan-500' : 'bg-gray-300'">
                            <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition-transform duration-300 ease-in-out"
                                  :class="leaderboard ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    <p class="text-[9px] text-gray-400 ml-1 leading-relaxed">Menampilkan papan peringkat siswa berdasarkan XP dan nilai ujian terbaik.</p>
                </div>

            </div>

            <div class="px-8 pb-8 flex items-center justify-between gap-4">
                <p class="text-[9px] text-amber-600 font-bold uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> Perubahan berlaku langsung untuk semua sesi aktif.
                </p>
                <button type="submit"
                        class="h-12 px-10 rounded-2xl text-[10px] font-black uppercase tracking-widest text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-purple-200"
                        style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                    <i class="fas fa-save mr-2"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>

    {{-- Info cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-3xl border border-gray-100 p-6 text-center shadow-sm hover:shadow-lg hover:shadow-purple-500/5 transition-all duration-300">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 mx-auto mb-3">
                <i class="fas fa-trophy text-lg"></i>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Achievement</p>
            <p class="text-3xl font-black" style="color: var(--brand-primary);">10</p>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 p-6 text-center shadow-sm hover:shadow-lg hover:shadow-purple-500/5 transition-all duration-300">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mx-auto mb-3">
                <i class="fas fa-star text-lg"></i>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Gamifikasi</p>
            <p class="text-sm font-black {{ ($allSettings['enable_gamification'] ?? '1') == '1' ? 'text-emerald-600' : 'text-rose-500' }}">
                {{ ($allSettings['enable_gamification'] ?? '1') == '1' ? 'AKTIF' : 'NON-AKTIF' }}
            </p>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 p-6 text-center shadow-sm hover:shadow-lg hover:shadow-purple-500/5 transition-all duration-300">
            <div class="w-12 h-12 rounded-2xl bg-cyan-50 flex items-center justify-center text-cyan-600 mx-auto mb-3">
                <i class="fas fa-chart-bar text-lg"></i>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Leaderboard</p>
            <p class="text-sm font-black {{ ($allSettings['enable_leaderboard'] ?? '1') == '1' ? 'text-emerald-600' : 'text-rose-500' }}">
                {{ ($allSettings['enable_leaderboard'] ?? '1') == '1' ? 'AKTIF' : 'NON-AKTIF' }}
            </p>
        </div>
    </div>

</div>

<style>
.animate-fadeIn { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
