@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="text-center">
        <div class="w-16 h-16 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3 text-2xl shadow-xs">
            <i class="fas fa-file-circle-check"></i>
        </div>
        <h1 class="text-2xl font-black text-gray-900">Konfirmasi Upload Project</h1>
        <p class="text-xs text-gray-500 mt-1">Periksa kembali berkas project sebelum disimpan secara permanen.</p>
    </div>

    {{-- File & Content Card --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-5">
        {{-- Status index.html Valid --}}
        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-emerald-900">File index.html Ditemukan!</p>
                <p class="text-[11px] text-emerald-700 mt-0.5">Struktur berkas web valid dan siap dipublikasikan ke Galeri.</p>
            </div>
        </div>

        {{-- Info Project --}}
        <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-100 text-xs">
            <div>
                <span class="text-gray-400 block mb-0.5">Judul Project:</span>
                <span class="font-bold text-gray-800 text-sm">{{ $preview['title'] }}</span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Slot Penugasan:</span>
                <span class="font-bold text-gray-800 text-sm">Slot #{{ $preview['slot'] }}</span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Nama Berkas:</span>
                <span class="font-mono text-gray-700">{{ $preview['filename'] }}</span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Ukuran Berkas:</span>
                <span class="font-mono text-gray-700">{{ number_format($preview['size'] / 1024, 1) }} KB</span>
            </div>
        </div>

        {{-- Content Badges --}}
        <div>
            <span class="text-xs font-bold text-gray-700 block mb-2">Komponen Terdeteksi:</span>
            <div class="flex flex-wrap gap-2">
                @if($preview['meta']['has_css'] ?? false)
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        <i class="fab fa-css3-alt mr-1"></i> CSS Stylesheet
                    </span>
                @endif
                @if($preview['meta']['has_js'] ?? false)
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <i class="fab fa-js mr-1"></i> JavaScript
                    </span>
                @endif
                @if($preview['meta']['has_images'] ?? false)
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fas fa-image mr-1"></i> Gambar / Aset Visual
                    </span>
                @endif
                @if($preview['meta']['has_audio'] ?? false)
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        <i class="fas fa-music mr-1"></i> Audio
                    </span>
                @endif
                @if($preview['meta']['has_video'] ?? false)
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <i class="fas fa-film mr-1"></i> Video
                    </span>
                @endif
            </div>
        </div>

        {{-- Daftar File di dalam ZIP --}}
        <div>
            <div class="flex items-center justify-between text-xs mb-2">
                <span class="font-bold text-gray-700">Daftar File ({{ $preview['meta']['total_files'] ?? 0 }} file):</span>
            </div>
            <div class="max-h-48 overflow-y-auto rounded-2xl bg-slate-50 border border-slate-200/70 p-3 text-xs divide-y divide-slate-100 font-mono">
                @foreach(($preview['meta']['file_list'] ?? []) as $f)
                <div class="py-1.5 flex items-center justify-between">
                    <span class="flex items-center gap-2 truncate text-slate-700">
                        @if($f['ext'] === 'html')
                            <i class="fab fa-html5 text-orange-500"></i>
                        @elseif($f['ext'] === 'css')
                            <i class="fab fa-css3-alt text-blue-500"></i>
                        @elseif($f['ext'] === 'js')
                            <i class="fab fa-js text-amber-500"></i>
                        @elseif(in_array($f['ext'], ['jpg','jpeg','png','gif','webp','svg']))
                            <i class="fas fa-image text-emerald-500"></i>
                        @elseif(in_array($f['ext'], ['mp3','wav','ogg']))
                            <i class="fas fa-volume-high text-purple-500"></i>
                        @elseif(in_array($f['ext'], ['mp4','webm','ogv']))
                            <i class="fas fa-video text-rose-500"></i>
                        @else
                            <i class="fas fa-file text-slate-400"></i>
                        @endif
                        <span class="truncate">{{ $f['name'] }}</span>
                    </span>
                    <span class="text-[10px] text-slate-400 pl-2 flex-shrink-0">
                        {{ number_format($f['size'] / 1024, 1) }} KB
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Warning jika replace --}}
        @if($existingSubmission)
        <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5">
            <i class="fas fa-exclamation-triangle text-rose-600 mt-0.5"></i>
            <div>
                <p class="font-bold">Peringatan Penggantian Berkas:</p>
                <p class="text-[11px] mt-0.5 text-rose-700">
                    Anda sudah memiliki tugas di Slot #{{ $preview['slot'] }} ("{{ $existingSubmission->title }}"). Konfirmasi ini akan <strong>MENGHAPUS file lama secara permanen</strong> dan menggantikannya dengan project baru ini.
                </p>
            </div>
        </div>
        @endif

        {{-- Action buttons --}}
        <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="POST" action="{{ route('student.projects.cancel', $assignment) }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-2xl text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="fas fa-times mr-1"></i> Batalkan
                </button>
            </form>

            <form method="POST" action="{{ route('student.projects.confirm', $assignment) }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-2xl text-xs font-bold text-white shadow-md transition hover:opacity-90"
                        style="background: var(--brand-primary)">
                    <i class="fas fa-check"></i> Konfirmasi & Simpan Project
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
