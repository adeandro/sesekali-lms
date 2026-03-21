@extends('layouts.app')

@section('title', 'Generator Surat - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Generator Surat')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 animate-fadeIn pb-20">
    <!-- Header -->
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-magic text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Generator Surat</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Pilih template surat untuk mulai generate dokumen</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.letters.history') }}" 
                   class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-100 text-gray-500 text-[9px] font-black uppercase tracking-widest rounded-xl hover:text-indigo-600 hover:border-indigo-100 transition shadow-sm">
                    <i class="fas fa-history text-[10px]"></i> Arsip Surat
                </a>
                @if(auth()->user()->role === 'superadmin')
                <a href="{{ route('admin.letters.templates.index') }}" 
                   class="flex items-center gap-3 px-6 py-3 bg-white border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                    <i class="fas fa-cog"></i>
                    Kelola Template
                </a>
                @endif
            </div>
        </div>
    </div>

    @if($templates->isEmpty())
    <div class="bg-white rounded-[3.5rem] p-20 border border-gray-100 shadow-sm flex flex-col items-center text-center space-y-6">
        <div class="w-24 h-24 rounded-full bg-gray-50 flex items-center justify-center text-gray-200">
            <i class="fas fa-file-invoice text-5xl"></i>
        </div>
        <div class="space-y-2">
            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Belum Ada Template Aktif</h3>
            <p class="text-sm text-gray-400 font-medium max-w-sm">
                Sistem tidak menemukan template surat yang aktif. @if(auth()->user()->role === 'superadmin') Silakan buat template baru terlebih dahulu. @else Hubungi administrator untuk menambahkan template. @endif
            </p>
        </div>
        @if(auth()->user()->role === 'superadmin')
        <a href="{{ route('admin.letters.templates.create') }}" class="px-8 py-4 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 flex items-center gap-3">
            <i class="fas fa-plus"></i> Buat Template Pertama
        </a>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($templates as $template)
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500 group overflow-hidden flex flex-col">
            <div class="p-8 flex-1 space-y-6">
                <div class="flex items-start justify-between">
                    @php
                        $categoryColor = match($template->category) {
                            'siswa' => 'indigo',
                            'guru' => 'amber',
                            'umum' => 'emerald',
                            default => 'slate'
                        };
                        $categoryLabel = match($template->category) {
                            'siswa' => 'Administrasi Siswa',
                            'guru' => 'Administrasi Guru',
                            'umum' => 'Umum',
                            default => 'Lainnya'
                        };
                    @endphp
                    <span class="px-4 py-1.5 bg-{{ $categoryColor }}-50 text-{{ $categoryColor }}-600 rounded-full text-[9px] font-black uppercase tracking-widest">
                        {{ $categoryLabel }}
                    </span>
                    <span class="text-[10px] font-black text-gray-300 uppercase letter-spacing-widest">#{{ $template->code }}</span>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-black text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-tight uppercase tracking-tight">
                        {{ $template->name }}
                    </h3>
                    <p class="text-[10px] font-medium text-gray-400 line-clamp-2">
                        {{ Str::limit(strip_tags($template->body), 100) }}
                    </p>
                </div>
            </div>

            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-50 grid grid-cols-2 gap-4">
                <a href="{{ route('admin.letters.form', $template) }}" 
                   class="h-12 bg-white border border-gray-200 text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-file-signature text-[12px]"></i>
                    Tunggal
                </a>
                <a href="{{ route('admin.letters.bulk.form', $template) }}" 
                   class="h-12 bg-white border border-gray-200 text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-600 hover:text-white hover:border-amber-600 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-copy text-[12px]"></i>
                    Massal
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
