@extends('layouts.app')

@section('title', 'Tambah Tema Baru — Gamification Center')
@section('page-title', 'Tambah Tema Baru')

@section('content')
<div x-data="{ 
    name: '',
    primary: '#6366f1',
    secondary: '#818cf8',
    glow: 'rgba(99, 102, 241, 0.2)',
    bg: '#f8faff',
    text: '#1e293b',
    isUnlocked: true,
    isActive: true
}" class="max-w-6xl mx-auto pb-12 animate-fadeIn">

    <form action="{{ route('admin.gamification.themes.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        @csrf

        {{-- Left: Form Controls --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- Form Header --}}
            <div class="flex items-center gap-4 mb-2">
                <a href="{{ route('admin.gamification.themes') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-gray-900">Konfigurasi Tema Baru</h2>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tentukan identitas visual dan variabel warna</p>
                </div>
            </div>

            {{-- Basic Info --}}
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Tema *</label>
                    <input type="text" name="name" x-model="name" required placeholder="e.g. Cyberpunk Purple"
                           class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-gray-900 shadow-inner">
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-emerald-600">Min. Student Level</label>
                        <input type="number" name="min_level" value="0" min="0" 
                               class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-gray-900 shadow-inner">
                        <p class="text-[8px] text-gray-400 mt-1">Level minimal untuk menggunakan tema ini.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-amber-600">Required Achievement</label>
                        <select name="required_achievement_id" 
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-bold text-gray-900 shadow-inner appearance-none cursor-pointer">
                            <option value="">-- Tanpa Achievement --</option>
                            @foreach($achievements as $achievement)
                                <option value="{{ $achievement->id }}">{{ $achievement->title }} ({{ $achievement->slug }})</option>
                            @endforeach
                        </select>
                        <p class="text-[8px] text-gray-400 mt-1">Gunakan piala/pencapaian sebagai syarat unlock.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="is_unlocked_by_default" x-model="isUnlocked" class="peer sr-only">
                                <div class="w-12 h-6 bg-gray-200 rounded-full peer-checked:bg-emerald-500 transition-all duration-300"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-6 transition-all duration-300 shadow-sm"></div>
                            </div>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest group-hover:text-emerald-600 transition-colors">Tema Gratis (Public)</span>
                        </label>
                        <p class="text-[8px] text-gray-400 mt-2 font-medium">Jika aktif, sistem mengabaikan Level & Achievement check.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="is_active" x-model="isActive" checked class="peer sr-only">
                                <div class="w-12 h-6 bg-gray-200 rounded-full peer-checked:bg-blue-500 transition-all duration-300"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-6 transition-all duration-300 shadow-sm"></div>
                            </div>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest group-hover:text-blue-600 transition-colors">Status Aktif</span>
                        </label>
                        <p class="text-[8px] text-gray-400 mt-2 font-medium">Tentukan apakah tema ini muncul sebagai opsi di profil siswa.</p>
                    </div>
                </div>
            </div>

            {{-- Color Palette --}}
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500">
                        <i class="fas fa-eye-dropper"></i>
                    </div>
                    <h3 class="font-black text-gray-900 tracking-tight">Variabel Warna Utama</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Primary --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Brand Primary</label>
                        <div class="flex gap-3">
                            <input type="color" name="primary_color" x-model="primary" class="h-14 w-14 rounded-xl cursor-pointer bg-white border border-gray-100 p-1 shadow-sm">
                            <input type="text" x-model="primary" class="flex-1 px-5 py-3 bg-gray-50 border border-gray-100 rounded-xl font-mono text-sm uppercase font-bold text-gray-600">
                        </div>
                    </div>

                    {{-- Secondary --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Brand Secondary</label>
                        <div class="flex gap-3">
                            <input type="color" name="secondary_color" x-model="secondary" class="h-14 w-14 rounded-xl cursor-pointer bg-white border border-gray-100 p-1 shadow-sm">
                            <input type="text" x-model="secondary" class="flex-1 px-5 py-3 bg-gray-50 border border-gray-100 rounded-xl font-mono text-sm uppercase font-bold text-gray-600">
                        </div>
                    </div>

                    {{-- Glow --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Glow / Shadow Color</label>
                        <div class="flex gap-3">
                            <input type="color" name="glow_color" x-model="glow" class="h-14 w-14 rounded-xl cursor-pointer bg-white border border-gray-100 p-1 shadow-sm">
                            <input type="text" x-model="glow" class="flex-1 px-5 py-3 bg-gray-50 border border-gray-100 rounded-xl font-mono text-sm uppercase font-bold text-gray-600">
                        </div>
                        <p class="text-[8px] text-gray-400 pl-1 font-medium italic">* Disarankan menggunakan warna transparan / lebih muda dari Primary.</p>
                    </div>

                    {{-- Text --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Primary Text Color</label>
                        <div class="flex gap-3">
                            <input type="color" name="text_color" x-model="text" class="h-14 w-14 rounded-xl cursor-pointer bg-white border border-gray-100 p-1 shadow-sm">
                            <input type="text" x-model="text" class="flex-1 px-5 py-3 bg-gray-50 border border-gray-100 rounded-xl font-mono text-sm uppercase font-bold text-gray-600">
                        </div>
                    </div>
                    
                    {{-- Background --}}
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Dashboard Background</label>
                        <div class="flex gap-3">
                            <input type="color" name="bg_color" x-model="bg" class="h-14 w-14 rounded-xl cursor-pointer bg-white border border-gray-100 p-1 shadow-sm">
                            <input type="text" x-model="bg" class="flex-1 px-5 py-3 bg-gray-50 border border-gray-100 rounded-xl font-mono text-sm uppercase font-bold text-gray-600">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 py-5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-[2rem] font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-200 hover:scale-[1.02] active:scale-100 transition-all">
                    Simpan & Publikasikan Tema
                </button>
            </div>

        </div>

        {{-- Right: Live Preview --}}
        <div class="lg:col-span-5">
            <div class="sticky top-12 space-y-6">
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Live Real-time Preview</h3>
                    
                    {{-- Student Dashboard Mockup --}}
                    <div class="rounded-[2.5rem] border border-gray-100 shadow-2xl overflow-hidden bg-white transition-all duration-500"
                         :style="`background-color: ${bg}; border-color: ${glow};`"
                         style="min-height: 500px;">
                        
                        {{-- Mock Header --}}
                        <div class="p-6 flex flex-col gap-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-lg flex items-center justify-center border transition-all"
                                         :style="`border-color: ${glow};` font-size: 20px;">
                                        <i class="fas fa-crown" :style="`color: ${primary};` text-shadow: 0 0 10px ${glow};"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="h-4 w-32 rounded-lg opacity-80" :style="`background-color: ${primary};` opacity: 0.1"></div>
                                        <div class="h-3 w-20 rounded-lg opacity-80" :style="`background-color: ${text};` opacity: 0.2"></div>
                                    </div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100"></div>
                            </div>

                            {{-- Mock Welcome Card --}}
                            <div class="p-8 rounded-[2rem] border relative overflow-hidden transition-all duration-500 group"
                                 :style="`background-color: #ffffff; border-color: ${glow}; shadow: 0 20px 40px ${glow};` 
                                         box-shadow: 0 20px 40px -10px ${glow};">
                                
                                <div class="relative z-10 space-y-3">
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest text-white" 
                                              :style="`background: linear-gradient(135deg, ${primary}, ${secondary}); shadow: 0 8px 15px ${glow};` 
                                                      box-shadow: 0 8px 15px ${glow};">PRO THEME</span>
                                    </div>
                                    <h4 class="text-2xl font-black italic tracking-tighter" :style="`color: ${text};`"><span x-text="name || 'Nama Tema Baru'"></span></h4>
                                    <p class="text-[9px] font-bold opacity-60 leading-relaxed max-w-[80%]" :style="`color: ${text};` opacity: 0.6">
                                        "Kekuatan barumu baru saja bangkit. Kuasai medan ujian dengan aura yang baru."
                                    </p>
                                </div>
                                <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full blur-3xl opacity-20" :style="`background-color: ${primary};` opacity: 0.3"></div>
                            </div>

                            {{-- Mock Stats --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-5 rounded-3xl bg-white border border-gray-50 shadow-sm flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" :style="`background-color: ${glow};` background: ${glow};">
                                        <i class="fas fa-bolt text-xs" :style="`color: ${primary};` opacity: 0.8"></i>
                                    </div>
                                    <div>
                                        <div class="h-2 w-12 rounded-full mb-1" :style="`background-color: ${text};` opacity: 0.1"></div>
                                        <div class="h-3 w-8 rounded-full" :style="`background-color: ${primary};` opacity: 0.6"></div>
                                    </div>
                                </div>
                                <div class="p-5 rounded-3xl bg-white border border-gray-50 shadow-sm flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors" :style="`background-color: ${primary};` background: ${primary};">
                                        <i class="fas fa-trophy text-xs text-white"></i>
                                    </div>
                                    <div>
                                        <div class="h-2 w-10 rounded-full mb-1" :style="`background-color: ${text};` opacity: 0.1"></div>
                                        <div class="h-3 w-6 rounded-full" :style="`background-color: ${secondary};` opacity: 0.8"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Mock Footer Bar --}}
                        <div class="mt-8 mx-6 p-1 bg-white/50 backdrop-blur-md rounded-2xl border border-gray-100 flex gap-1">
                            <div class="flex-1 h-12 rounded-xl flex items-center justify-center" :style="`background-color: ${glow}; color: ${primary};` opacity: 0.9">
                                <i class="fas fa-house-user text-sm"></i>
                            </div>
                            <div class="flex-1 h-12 rounded-xl flex items-center justify-center text-gray-300">
                                <i class="fas fa-book-open text-sm"></i>
                            </div>
                            <div class="flex-1 h-12 rounded-xl flex items-center justify-center text-gray-300">
                                <i class="fas fa-user-circle text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 border border-indigo-100 p-6 rounded-[2rem] space-y-3">
                    <div class="flex items-center gap-2 text-indigo-600">
                        <i class="fas fa-info-circle"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest">Penting</span>
                    </div>
                    <p class="text-xs text-indigo-950/70 leading-relaxed font-medium">
                        Warna yang Anda pilih akan disuntikkan sebagai **CSS Variables** global di dashboard siswa. Pastikan warna kontras tetap terjaga agar teks mudah dibaca.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
