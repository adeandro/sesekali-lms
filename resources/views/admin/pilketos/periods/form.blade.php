@extends('layouts.app')

@section('title', $period ? 'Edit Periode Pilketos' : 'Buat Periode Pilketos')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    pin: '{{ old('booth_pin') }}',
    generatePin() {
        this.pin = Math.floor(100000 + Math.random() * 900000).toString();
    }
}">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ $period ? route('admin.pilketos.periods.show', $period) : route('admin.pilketos.periods.index') }}"
           class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">
                {{ $period ? 'Edit Periode' : 'Buat Periode Baru' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pilketos — Pemilihan Ketua OSIS</p>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <form method="POST"
              action="{{ $period ? route('admin.pilketos.periods.update', $period) : route('admin.pilketos.periods.store') }}"
              class="space-y-5">
            @csrf
            @if($period)
                @method('PUT')
            @endif

            {{-- Title --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Judul Periode <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title"
                       value="{{ old('title', $period?->title) }}"
                       placeholder="cth. Pilketos OSIS 2026/2027"
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-500 @enderror" required>
                @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Event Date --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Tanggal Pemilihan <span class="text-xs text-slate-400">(Informasi saja)</span>
                </label>
                <input type="date" name="event_date"
                       value="{{ old('event_date', $period?->event_date?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('event_date') border-red-500 @enderror">
                @error('event_date')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Deskripsi
                </label>
                <textarea name="description" rows="3"
                          placeholder="Keterangan tambahan (opsional)..."
                          class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none @error('description') border-red-500 @enderror">{{ old('description', $period?->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Booth PIN (Hanya saat create; saat edit ada tombol tersendiri di halaman detail) --}}
            @if(!$period)
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    PIN Bilik Suara <span class="text-xs text-slate-400">(4-8 digit angka untuk login komputer bilik)</span>
                </label>
                <div class="flex gap-2">
                    <input type="password" name="booth_pin" x-model="pin"
                           placeholder="Contoh: 123456"
                           maxlength="8"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('booth_pin') border-red-500 @enderror">
                    <button type="button" @click="generatePin()"
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-xs font-medium rounded-lg transition whitespace-nowrap">
                        🎲 Acak 6 Digit
                    </button>
                </div>
                <div x-show="pin" class="mt-1 text-xs text-indigo-600 dark:text-indigo-400 font-mono">
                    PIN yang dipilih: <span x-text="pin" class="font-bold"></span> (Catat PIN ini sebelum submit)
                </div>
                @error('booth_pin')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            @endif

            {{-- Eligible Classes --}}
            <div class="pt-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Kelas yang Berhak Memilih
                </label>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                    Centang kelas yang dapat berpartisipasi. Jika tidak ada yang dicentang, <strong>semua kelas aktif</strong> diikutsertakan.
                </p>
                @php
                    $selectedClasses = old('eligible_class_ids', $period?->eligible_class_ids ?? []);
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-3 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                    @foreach($classes as $c)
                        <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="eligible_class_ids[]" value="{{ $c->id }}"
                                   {{ in_array($c->id, (array)$selectedClasses) ? 'checked' : '' }}
                                   class="rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            <span>{{ $c->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ $period ? route('admin.pilketos.periods.show', $period) : route('admin.pilketos.periods.index') }}"
                   class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    {{ $period ? 'Simpan Perubahan' : 'Buat Periode' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
