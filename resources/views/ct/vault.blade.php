@extends('ct.layout')
@section('title', 'Brankas Tim ' . $team->number . ' — Kelas ' . $class->name)
@section('container_width', 'max-w-lg')

@section('header_right')
    Kelas {{ $class->name }} · Tim {{ $team->number }}
@endsection

@section('content')

{{-- Cooldown alert --}}
@if($cooldownLeft > 0)
<div x-data="{ seconds: {{ $cooldownLeft }} }"
     x-init="let t=setInterval(()=>{seconds--;if(seconds<=0){clearInterval(t);window.location.reload();}},1000)"
     class="mb-5 p-5 bg-amber-900/30 border border-amber-700/50 rounded-2xl text-center">
    <div class="text-3xl mb-1">⏳</div>
    <p class="font-semibold text-amber-300">Kode salah. Tunggu sebentar.</p>
    <p class="text-amber-400 text-sm mt-1">Coba lagi dalam <span class="font-mono font-bold text-lg" x-text="seconds"></span> detik.</p>
    <p class="text-xs text-amber-600 mt-2">Percobaan salah: {{ $team->wrong_count }}</p>
</div>
@endif

@php $oldNames = old('names', ['','','','']); @endphp

<div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-xl"
     x-data="vaultForm({{ Js::from($oldNames) }}, '{{ old('code','') }}')"
     x-on:submit.prevent="showConfirm">

    {{-- Header --}}
    <div class="bg-red-900/30 border-b border-red-800/40 p-5 text-center">
        <div class="text-4xl mb-2">🔒</div>
        <h2 class="text-lg font-bold text-white">Brankas Tim {{ $team->number }}</h2>
        <p class="text-sm text-slate-400">Kelas {{ $class->name }}</p>
    </div>

    <form id="vault-form" method="POST" action="{{ route('ct.open') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $class->id }}">
        <input type="hidden" name="team_id" value="{{ $team->id }}">

        <div class="p-5 space-y-5">
            {{-- Member names --}}
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
                    Nama Anggota Tim (isi semua 4 nama)
                </p>
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Anggota 1</label>
                        <input type="text" name="names[]" x-model="n0"
                               placeholder="Nama lengkap anggota 1" maxlength="60"
                               class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition @error('names.0') border-red-500 @enderror">
                        @error('names.0') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Anggota 2</label>
                        <input type="text" name="names[]" x-model="n1"
                               placeholder="Nama lengkap anggota 2" maxlength="60"
                               class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition @error('names.1') border-red-500 @enderror">
                        @error('names.1') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Anggota 3</label>
                        <input type="text" name="names[]" x-model="n2"
                               placeholder="Nama lengkap anggota 3" maxlength="60"
                               class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition @error('names.2') border-red-500 @enderror">
                        @error('names.2') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Anggota 4</label>
                        <input type="text" name="names[]" x-model="n3"
                               placeholder="Nama lengkap anggota 4" maxlength="60"
                               class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition @error('names.3') border-red-500 @enderror">
                        @error('names.3') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Code --}}
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kode Brankas (4 digit)</label>
                <input type="text" name="code" x-model="code"
                       inputmode="numeric" maxlength="4"
                       placeholder="••••" autocomplete="off"
                       class="w-full px-4 py-4 bg-slate-900 border border-slate-700 rounded-xl text-white text-3xl font-mono text-center placeholder-slate-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition @error('code') border-red-500 @enderror">
                @error('code') <p class="text-red-400 text-xs mt-1 text-center">{{ $message }}</p> @enderror
            </div>

            @if($team->wrong_count > 0)
            <p class="text-center text-xs text-slate-500">
                Percobaan salah sebelumnya: <span class="text-amber-400 font-semibold">{{ $team->wrong_count }}</span>
            </p>
            @endif
        </div>

        {{-- Submit button --}}
        <div class="px-5 pb-5">
            <button type="button" @click="showConfirm"
                    @if($cooldownLeft > 0) disabled @endif
                    class="w-full py-3 bg-red-700 hover:bg-red-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl transition text-sm">
                Buka Brankas
            </button>
        </div>
    </form>

    {{-- Confirmation modal (outside <form> to avoid nested submit) --}}
    <div x-show="confirming" x-cloak
         class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 border border-slate-600 rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="text-3xl text-center mb-3">⚠️</div>
            <h3 class="text-center font-bold text-white mb-2">Konfirmasi Nama Tim</h3>
            <p class="text-center text-xs text-amber-400 mb-4">
                Pastikan ejaan nama sudah benar.<br>
                <strong>Nama tidak dapat diubah setelah brankas terbuka.</strong>
            </p>
            <ul class="bg-slate-900 rounded-xl p-3 mb-4 space-y-1">
                <li class="text-sm text-white flex gap-2"><span class="text-slate-500 text-xs">1.</span><span x-text="n0 || '(kosong)'"></span></li>
                <li class="text-sm text-white flex gap-2"><span class="text-slate-500 text-xs">2.</span><span x-text="n1 || '(kosong)'"></span></li>
                <li class="text-sm text-white flex gap-2"><span class="text-slate-500 text-xs">3.</span><span x-text="n2 || '(kosong)'"></span></li>
                <li class="text-sm text-white flex gap-2"><span class="text-slate-500 text-xs">4.</span><span x-text="n3 || '(kosong)'"></span></li>
                <li class="text-sm text-white mt-2 pt-2 border-t border-slate-700 flex gap-2">
                    <span class="text-slate-500 text-xs">Kode:</span>
                    <span class="font-mono font-bold">••••</span>
                </li>
            </ul>
            <div class="flex gap-3">
                <button type="button" @click="confirming = false"
                        class="flex-1 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl transition text-sm font-medium">
                    Koreksi
                </button>
                <button type="button" @click="document.getElementById('vault-form').submit()"
                        class="flex-1 py-2.5 bg-red-700 hover:bg-red-600 text-white rounded-xl transition text-sm font-bold">
                    Ya, Buka!
                </button>
            </div>
        </div>
    </div>
</div>

<div class="mt-5 text-center space-y-3">

    {{-- Hint button (shows after 3 wrong attempts) --}}
    @if($team->wrong_count >= 3 && !$team->isOpened())
    <div x-data="hintPanel({{ $class->id }}, {{ $team->id }}, {{ $team->hint_count }}, '{{ csrf_token() }}')"
         class="bg-slate-800/60 border border-slate-700 rounded-2xl p-4 text-left">

        <div class="flex items-center justify-between gap-3 mb-3">
            <div>
                <p class="text-xs font-bold text-amber-400 uppercase tracking-widest">💡 Petunjuk Tersedia</p>
                <p class="text-xs text-slate-500 mt-0.5">
                    Petunjuk digunakan: <span x-text="hintCount"></span>/3
                </p>
            </div>
            <button type="button" @click="requestHint()"
                    :disabled="loading || hintCount >= 3"
                    class="px-4 py-2 bg-amber-700 hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition">
                <span x-show="!loading">Minta Petunjuk</span>
                <span x-show="loading" x-cloak>⏳</span>
            </button>
        </div>

        {{-- Previously received hints --}}
        @if($team->hint_count > 0)
        <div class="space-y-2 mb-3">
            @foreach($hints as $h)
            <div class="bg-amber-900/20 border border-amber-800/40 rounded-xl px-3 py-2 text-xs text-amber-200 leading-relaxed">
                {{ $h }}
            </div>
            @endforeach
        </div>
        @endif

        {{-- New hint display --}}
        <div x-show="latestHint" x-cloak
             class="bg-amber-900/20 border border-amber-800/40 rounded-xl px-3 py-2 text-xs text-amber-200 leading-relaxed">
            <span x-text="latestHint"></span>
        </div>

        <div x-show="errorMsg" x-cloak class="mt-2 text-xs text-red-400" x-text="errorMsg"></div>
    </div>
    @endif

    <a href="{{ route('ct.teams', $class) }}" class="text-sm text-slate-500 hover:text-slate-300 transition">
        &larr; Ganti kelompok
    </a>

    <a href="{{ route('ct.leaderboard', $class) }}" class="block text-xs text-slate-600 hover:text-slate-400 transition">
        🏆 Papan Skor Kelas {{ $class->name }}
    </a>
</div>
@endsection

@push('scripts')
<script>
function vaultForm(oldNames, oldCode) {
    return {
        confirming: false,
        n0: oldNames[0] || '',
        n1: oldNames[1] || '',
        n2: oldNames[2] || '',
        n3: oldNames[3] || '',
        code: oldCode || '',
        showConfirm() { this.confirming = true; }
    }
}

function hintPanel(classId, teamId, initialCount, csrfToken) {
    return {
        hintCount:  initialCount,
        loading:    false,
        latestHint: '',
        errorMsg:   '',

        async requestHint() {
            if (this.hintCount >= 3 || this.loading) return;
            this.loading  = true;
            this.errorMsg = '';
            try {
                const res  = await fetch('/ct/petunjuk', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ class_id: classId, team_id: teamId }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.errorMsg = data.error || 'Gagal meminta petunjuk.';
                } else {
                    this.hintCount  = data.hint_count;
                    this.latestHint = data.hint;
                }
            } catch (e) {
                this.errorMsg = 'Terjadi kesalahan jaringan.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
