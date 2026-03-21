@extends('layouts.app')

@section('title', 'Template Surat - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Manajemen Template Surat')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                <i class="fas fa-file-alt text-2xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Template Surat</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelola format dan struktur surat otomatis untuk administrasi sekolah</p>
            </div>
        </div>
        <a href="{{ route('admin.letters.templates.create') }}" 
           class="flex items-center justify-center gap-3 px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 group">
            <i class="fas fa-plus text-xs group-hover:rotate-90 transition-transform duration-300"></i>
            <span>Tambah Template Baru</span>
        </a>
    </div>

    {{-- Card Pengaturan Kop Surat --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 
                overflow-hidden" 
         x-data="{
            foundation: '{{ $allSettings['letterhead_foundation'] ?? '' }}',
            program: '{{ $allSettings['letterhead_program'] ?? '' }}',
            email: '{{ $allSettings['letterhead_email'] ?? '' }}',
            website: '{{ $allSettings['letterhead_website'] ?? '' }}',
            borderStyle: '{{ $allSettings['letterhead_border_style'] ?? 'double' }}',
            schoolName: '{{ $configs['school_name'] ?? '' }}',
            schoolAddress: '{{ $configs['school_address'] ?? '' }}',
            logo: '{{ isset($configs['logo']) ? asset('storage/' . $configs['logo']) : '' }}'
         }">
        
        {{-- Header card --}}
        <div class="p-8 border-b border-gray-50 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center 
                        justify-center text-indigo-600">
                <i class="fas fa-heading text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">
                    Kop Surat
                </h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    Konfigurasi kop surat untuk Generator Surat
                </p>
            </div>
        </div>

        <div class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-10">
            
            {{-- Form kiri --}}
            <form action="{{ route('admin.settings.update') }}" 
                  method="POST" class="space-y-6 no-loading">
                @csrf

                {{-- Nama Yayasan --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 
                                  uppercase tracking-widest ml-1">
                        Nama Yayasan 
                        <span class="text-gray-300 font-normal">(Opsional)</span>
                    </label>
                    <input type="text" name="letterhead_foundation"
                           x-model="foundation"
                           placeholder="Contoh: Nama yayasan jika perlu atau lainya"
                           class="w-full h-14 bg-gray-50 border-transparent 
                                  rounded-2xl px-6 text-sm font-bold text-gray-900 
                                  focus:bg-white focus:ring-4 
                                  focus:ring-indigo-500/10 transition-all">
                </div>

                {{-- Program Keahlian --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 
                                  uppercase tracking-widest ml-1">
                        Program Keahlian 
                        <span class="text-gray-300 font-normal">(Opsional)</span>
                    </label>
                    <input type="text" name="letterhead_program"
                           x-model="program"
                           placeholder="Contoh: Teknik Komputer dan Jaringan"
                           class="w-full h-14 bg-gray-50 border-transparent 
                                  rounded-2xl px-6 text-sm font-bold text-gray-900 
                                  focus:bg-white focus:ring-4 
                                  focus:ring-indigo-500/10 transition-all">
                </div>

                {{-- Email --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 
                                  uppercase tracking-widest ml-1">
                        Email Sekolah
                        <span class="text-gray-300 font-normal">(Opsional)</span>
                    </label>
                    <input type="text" name="letterhead_email"
                           x-model="email"
                           placeholder="Contoh: info@sekolah.sch.id"
                           class="w-full h-14 bg-gray-50 border-transparent 
                                  rounded-2xl px-6 text-sm font-bold text-gray-900 
                                  focus:bg-white focus:ring-4 
                                  focus:ring-indigo-500/10 transition-all">
                </div>

                {{-- Website --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 
                                  uppercase tracking-widest ml-1">
                        Website
                        <span class="text-gray-300 font-normal">(Opsional)</span>
                    </label>
                    <input type="text" name="letterhead_website"
                           x-model="website"
                           placeholder="Contoh: https://www.sekolah.sch.id"
                           class="w-full h-14 bg-gray-50 border-transparent 
                                  rounded-2xl px-6 text-sm font-bold text-gray-900 
                                  focus:bg-white focus:ring-4 
                                  focus:ring-indigo-500/10 transition-all">
                </div>

                {{-- Style garis --}}
                <div class="space-y-3">
                    <label class="block text-[10px] font-black text-gray-400 
                                  uppercase tracking-widest ml-1">
                        Garis Pemisah Kop
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach([
                            'single' => ['label' => 'Single', 'preview' => 'border-b border-gray-900'],
                            'double' => ['label' => 'Double', 'preview' => 'border-b-4 border-double border-gray-900'],
                            'thick'  => ['label' => 'Tebal', 'preview' => 'border-b-4 border-gray-900'],
                        ] as $style => $opts)
                            <label class="cursor-pointer">
                                <input type="radio" name="letterhead_border_style" 
                                       value="{{ $style }}"
                                       x-model="borderStyle"
                                       class="hidden">
                                <div class="p-3 rounded-xl border-2 transition-all text-center"
                                     :class="borderStyle === '{{ $style }}' 
                                         ? 'border-indigo-600 bg-indigo-50' 
                                         : 'border-gray-100 bg-gray-50'">
                                    <div class="h-4 mb-2 {{ $opts['preview'] }}"></div>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-gray-600">
                                        {{ $opts['label'] }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" 
                        class="w-full h-14 bg-indigo-600 text-white text-[10px] 
                               font-black uppercase tracking-[0.2em] rounded-2xl 
                               hover:bg-indigo-700 transition shadow-lg 
                               shadow-indigo-100 flex items-center justify-center gap-3">
                    <i class="fas fa-save"></i> Simpan Kop Surat
                </button>
            </form>

            {{-- Preview kanan --}}
            <div class="space-y-4">
                <label class="block text-[10px] font-black text-gray-400 
                              uppercase tracking-widest ml-1">
                    Preview Kop Surat
                </label>
                <div class="bg-white border border-gray-100 rounded-2xl p-6 
                            shadow-sm font-serif text-black" 
                     style="font-family: Arial, sans-serif;">
                    
                    {{-- Preview container --}}
                    <div class="flex items-center gap-4 pb-3"
                         :class="{
                             'border-b border-black': borderStyle === 'single',
                             'border-b-4 border-double border-black': borderStyle === 'double',
                             'border-b-4 border-black': borderStyle === 'thick'
                         }">
                        
                        {{-- Logo --}}
                        <div class="shrink-0">
                            @if(isset($configs['logo']) && $configs['logo'])
                                <img src="{{ asset('storage/' . $configs['logo']) }}" 
                                     class="w-16 h-16 object-contain">
                            @else
                                <div class="w-16 h-16 bg-gray-100 rounded flex 
                                            items-center justify-center text-gray-300">
                                    <i class="fas fa-image text-2xl"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Teks kop --}}
                        <div class="flex-1 text-center">
                            <p x-show="foundation" 
                               x-text="foundation"
                               class="text-[9pt] font-medium text-gray-700 
                                      uppercase leading-tight mb-0.5">
                            </p>
                            <p x-text="schoolName" 
                               class="text-[13pt] font-black text-black 
                                      uppercase leading-tight">
                            </p>
                            <p x-show="program"
                               x-text="'PROGRAM KEAHLIAN : ' + program"
                               class="text-[9pt] font-bold text-black 
                                      uppercase leading-tight mt-0.5">
                            </p>
                            <p x-show="email || website"
                               class="text-[8pt] text-gray-600 mt-0.5">
                                <span x-show="email" x-text="'Pos-El: ' + email"></span>
                                <span x-show="email && website"> | </span>
                                <span x-show="website" x-text="'Laman: ' + website"></span>
                            </p>
                            <p x-text="schoolAddress"
                               class="text-[8pt] text-gray-700 mt-0.5">
                            </p>
                        </div>
                    </div>

                    <p class="text-[8pt] text-gray-400 text-center mt-3 italic">
                        Preview kop surat — akan tampil di semua surat yang digenerate
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($templates->isEmpty())
        <div class="bg-white rounded-[2.5rem] p-16 text-center shadow-sm border border-gray-100">
            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-file-invoice text-4xl text-gray-200"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight mb-2">Belum Ada Template</h3>
            <p class="text-xs font-medium text-gray-400 max-w-md mx-auto leading-relaxed mb-8">
                Anda belum memiliki template surat apa pun. Tambahkan template untuk mulai menghasilkan surat secara otomatis.
            </p>
            <a href="{{ route('admin.letters.templates.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-colors">
                Buat Template Pertama
            </a>
        </div>
    @else
        <div class="space-y-12">
            @foreach($templates as $category => $categoryTemplates)
                <div class="space-y-6">
                    <div class="flex items-center gap-4 px-2">
                        <h3 class="text-xs font-black text-indigo-600 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="w-8 h-[2px] bg-indigo-100"></span>
                            {{ $category === 'siswa' ? 'Administrasi Siswa' : ($category === 'guru' ? 'Administrasi Guru' : 'Kategori Lainnya') }}
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($categoryTemplates as $template)
                            <div class="group bg-white rounded-[2rem] border border-gray-100 p-8 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500 relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/50 rounded-bl-[5rem] -mr-16 -mt-16 transition-all group-hover:scale-110"></div>
                                
                                <div class="relative">
                                    <div class="flex items-start justify-between mb-6">
                                        <div class="w-12 h-12 rounded-xl {{ $template->is_active ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-50 text-gray-400' }} flex items-center justify-center transition-colors">
                                            <i class="fas fa-file-word text-lg"></i>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('admin.letters.templates.toggle', $template) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-8 h-8 rounded-lg {{ $template->is_active ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="Toggle Status">
                                                    <i class="fas fa-{{ $template->is_active ? 'check' : 'times' }} text-[10px]"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.letters.templates.edit', $template) }}" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="Edit Template">
                                                <i class="fas fa-edit text-[10px]"></i>
                                            </a>
                                            <form action="{{ route('admin.letters.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:scale-110 transition-transform shadow-sm" title="Hapus Template">
                                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight leading-tight group-hover:text-indigo-600 transition-colors">{{ $template->name }}</h4>
                                        <div class="flex items-center gap-3">
                                            <span class="px-3 py-1 bg-gray-100 text-[8px] font-black text-gray-500 uppercase tracking-widest rounded-md">{{ $template->code }}</span>
                                            <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest italic">{{ $template->letters_count ?? 0 }} Surat Dibuat</span>
                                        </div>
                                    </div>

                                    <div class="mt-8 pt-6 border-t border-gray-50 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ $template->is_active ? 'bg-green-500 animate-pulse' : 'bg-gray-300' }}"></div>
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $template->is_active ? 'Aktif & Siap' : 'Non-Aktif' }}</span>
                                        </div>
                                        <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:translate-x-1 transition-transform"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
