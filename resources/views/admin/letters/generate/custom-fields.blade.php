@extends('layouts.app')

@section('title', 'Lengkapi Data Surat - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Lengkapi Data Surat')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header & Breadcrumbs --}}
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-gray-400">📍 Generator — Custom Fields</p>
            <h2 class="text-2xl font-black tracking-tight" style="color: var(--brand-text);">Lengkapi <span style="color: var(--brand-primary);">Data Surat</span></h2>
        </div>
        <a href="{{ route('admin.letters.form', $template) }}" 
           class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-gray-100 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-rose-500 hover:border-rose-100 transition-all">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Template & Recipient Info Card --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8" style="border-color: var(--brand-glow);">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-3xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-file-invoice text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black" style="color: var(--brand-text);">{{ $template->name }}</h3>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-tighter">{{ $template->code }} • Kategori: {{ ucfirst($template->category) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 px-6 py-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                <div class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Penerima</p>
                    <p class="font-bold" style="color: var(--brand-text);">{{ $recipient->name }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom Fields Form --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden" style="border-color: var(--brand-glow);">
        <div class="p-8 border-b border-gray-50" style="background-color: var(--brand-bg);">
            <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs flex items-center gap-2">
                <i class="fas fa-edit"></i> Field Manual yang Diperlukan
            </h4>
            <p class="text-sm text-gray-500 mt-1 font-medium italic">Beberapa bagian dalam template ini perlu dilengkapi secara manual sebelum diproses.</p>
        </div>

        <form action="{{ route('admin.letters.preview', $template) }}" method="POST" class="p-8">
            @csrf
            <input type="hidden" name="recipient_id" value="{{ $recipient->id }}">
            <input type="hidden" name="format_type" value="{{ $formatType ?? 'simple' }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach($unreplacedPlaceholders as $placeholder)
                    @php
                        $label = str_replace('_', ' ', ucwords($placeholder, '_'));
                        $lowerKey = strtolower($placeholder);
                        
                        // Deteksi tipe input otomatis
                        $isDate = str_contains($lowerKey, 'tanggal') || 
                                  str_contains($lowerKey, 'date');
                        $isTime = str_contains($lowerKey, 'waktu') || 
                                  str_contains($lowerKey, 'time') || 
                                  str_contains($lowerKey, 'jam');
                        $isDay  = str_contains($lowerKey, 'hari') || 
                                  str_contains($lowerKey, 'day');

                        $hariOptions = [
                            'Senin', 'Selasa', 'Rabu', 'Kamis', 
                            'Jumat', 'Sabtu', 'Minggu'
                        ];
                    @endphp
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">
                            {{ $label }}
                            @if($isDate)
                                <span class="text-indigo-400 normal-case font-bold ml-1">(Tanggal)</span>
                            @elseif($isTime)
                                <span class="text-indigo-400 normal-case font-bold ml-1">(Waktu)</span>
                            @elseif($isDay)
                                <span class="text-indigo-400 normal-case font-bold ml-1">(Pilih Hari)</span>
                            @endif
                        </label>

                        @if($isDate)
                            {{-- Date picker --}}
                            <input type="date" 
                                   name="custom_fields[{{ $placeholder }}]"
                                   class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-amber-500/10 text-sm font-bold transition-all"
                                   required>

                        @elseif($isTime)
                            {{-- Time picker --}}
                            <input type="time" 
                                   name="custom_fields[{{ $placeholder }}]"
                                   class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-amber-500/10 text-sm font-bold transition-all"
                                   required>

                        @elseif($isDay)
                            {{-- Dropdown hari --}}
                            <div class="relative">
                                <select name="custom_fields[{{ $placeholder }}]"
                                        class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-amber-500/10 text-sm font-bold appearance-none cursor-pointer"
                                        required>
                                    <option value="">-- Pilih Hari --</option>
                                    @foreach($hariOptions as $hari)
                                        <option value="{{ $hari }}">{{ $hari }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-[10px]"></i>
                            </div>

                        @else
                            {{-- Text input biasa --}}
                            <input type="text" 
                                   name="custom_fields[{{ $placeholder }}]" 
                                   class="block w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-amber-500/10 text-sm font-bold transition-all placeholder:text-gray-300"
                                   placeholder="Masukkan {{ strtolower($label) }}..."
                                   required>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-12 flex items-center justify-end gap-4">
                <button type="submit" 
                        class="flex items-center gap-3 px-8 py-4 bg-indigo-600 text-white text-xs font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                    Lanjut ke Preview <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
