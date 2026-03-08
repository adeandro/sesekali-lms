@extends('layouts.app')

@section('title', 'Buat Achievement Baru — Gamification Center')
@section('page-title', 'Buat Achievement Baru')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 animate-fadeIn pb-12" x-data="achievementForm()">

    {{-- Breadcrumb nav --}}
    <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-gray-400">
        <a href="{{ route('admin.gamification.achievements') }}" class="hover:text-purple-600 transition-colors">Achievement Manager</a>
        <i class="fas fa-chevron-right text-[8px]"></i>
        <span style="color: var(--brand-primary);">Buat Baru</span>
    </div>

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-500/20"
             style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
            <i class="fas fa-magic text-2xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-black tracking-tight" style="color: var(--brand-text);">Pencapaian Baru</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Desain tantangan baru untuk memotivasi siswa</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if($errors->any())
    <div class="flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm">
        <i class="fas fa-exclamation-triangle mt-0.5"></i>
        <ul class="space-y-1">@foreach($errors->all() as $e)<li class="text-[11px] font-bold">{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Left Form Column --}}
        <div class="lg:col-span-8 space-y-6">
            <form action="{{ route('admin.gamification.achievements.store') }}" method="POST" enctype="multipart/form-data" id="achievementForm">
                @csrf

                {{-- Display Information --}}
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden hover:shadow-xl hover:shadow-purple-500/5 transition-all duration-500">
                    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                                <i class="fas fa-pen text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Detail Pencarian</h3>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sistem otomatis membuat slug dari judul</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Title --}}
                        <div class="space-y-2 md:col-span-2">
                            <label for="title" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Judul Achievement <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" id="title" x-model="title" required
                                   class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-purple-500/10 transition-all"
                                   placeholder="e.g. The Legend">
                        </div>

                        {{-- Description --}}
                        <div class="space-y-2 md:col-span-2">
                            <label for="description" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Deskripsi Singkat <span class="text-rose-500">*</span></label>
                            <textarea name="description" id="description" rows="2" x-model="description" required
                                      class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-purple-500/10 transition-all resize-none"
                                      placeholder="Apa yang harus dilakukan siswa untuk mendapatkan ini?"></textarea>
                        </div>

                        {{-- Lore Text --}}
                        <div class="space-y-2 md:col-span-2">
                            <label for="lore_text" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Lore Text <span class="normal-case italic text-gray-300">(Opsional)</span></label>
                            <textarea name="lore_text" id="lore_text" rows="2" x-model="loreText"
                                      class="w-full bg-gray-50 border-transparent rounded-2xl px-6 py-4 text-sm italic text-gray-600 focus:bg-white focus:ring-4 focus:ring-purple-500/10 transition-all resize-none"
                                      placeholder="Langkah nyata menuju puncak intelektual..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- Logic Engine Configuration --}}
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden hover:shadow-xl hover:shadow-cyan-500/5 transition-all duration-500 mt-6">
                    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center text-cyan-600">
                                <i class="fas fa-cogs text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Kriteria Sistem (Logic Engine)</h3>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pilih trigger kriteria & target yang harus dicapai</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/50">
                        {{-- Criteria Type Box --}}
                        <div class="space-y-3">
                            <label for="criteria_type" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Jenis Kriteria <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="criteria_type" id="criteria_type" x-model="criteriaType" required
                                        class="w-full h-14 bg-white border border-gray-200 rounded-2xl px-6 appearance-none text-sm font-bold text-gray-900 focus:ring-4 focus:border-cyan-500 focus:ring-cyan-500/10 transition-all cursor-pointer">
                                    <option value="" disabled selected>Pilih Kriteria Deteksi</option>
                                    @foreach($criteriaTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }} ({{ $key }})</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-6 text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Criteria Value Box --}}
                        <div class="space-y-3">
                            <label for="criteria_value" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nilai Target (Threshold) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="any" name="criteria_value" id="criteria_value" x-model="criteriaValue" required
                                       class="w-full h-14 bg-white border border-gray-200 rounded-2xl pl-14 pr-6 text-xl font-black text-cyan-600 focus:ring-4 focus:border-cyan-500 focus:ring-cyan-500/10 transition-all"
                                       placeholder="e.g. 50">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-6 text-gray-300">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                            </div>
                        </div>

                        {{-- XP Reward Box --}}
                        <div class="space-y-3 md:col-span-2">
                            <label for="xp_reward" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">XP Reward (Leveling) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="xp_reward" id="xp_reward" x-model="xpReward" required min="0"
                                       class="w-full h-14 bg-amber-50 border border-amber-200 rounded-2xl pl-14 pr-6 text-xl font-black text-amber-600 focus:ring-4 focus:border-amber-500 focus:ring-amber-500/20 transition-all"
                                       placeholder="e.g. 100">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-6 text-amber-400">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Visual Theme Setup --}}
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-500 mt-6">
                    <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <i class="fas fa-paint-brush text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Tema & Ikon</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Warna pendaran dan aset visual</p>
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        {{-- Color Picker --}}
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Pilih Warna Warna Dasar</label>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="c in colors" :key="c.hex">
                                    <button type="button" @click="color = c.hex; icon = c.icon"
                                            class="w-10 h-10 rounded-xl shadow-sm border-2 transition-all duration-300 flex items-center justify-center will-change-transform hover:scale-110"
                                            :class="color === c.hex ? 'border-gray-800 scale-110' : 'border-transparent opacity-60 hover:opacity-100'"
                                            :style="`background-color: ${c.hex}`">
                                        <i class="fas fa-check text-white text-xs" x-show="color === c.hex"></i>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="color" :value="color">
                            <input type="hidden" name="icon" :value="icon">
                        </div>

                        {{-- Icon Upload (Overrides default FontAwesome) --}}
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Atau Upload Ikon Kustom (PNG)</label>
                            <div class="flex items-center gap-4">
                                <input type="file" name="icon_file" id="iconFile" accept="image/*" class="hidden" x-ref="iconInput"
                                       @change="handleFileUpload($event)">
                                <button type="button" @click="$refs.iconInput.click()"
                                        class="px-5 py-3 bg-white border border-gray-200 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-50 hover:border-amber-200 transition-all shadow-sm flex items-center">
                                    <i class="fas fa-upload mr-2"></i>Pilih Gambar
                                </button>
                                <button type="button" @click="previewSrc = ''; $refs.iconInput.value = ''" x-show="previewSrc"
                                        class="text-[9px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-700">
                                    Hapus
                                </button>
                            </div>
                            <p class="text-[9px] text-gray-400">Rasio 1:1, Latar Transparan. Maks 1MB.</p>
                        </div>
                    </div>
                </div>

                {{-- Status & Save --}}
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden mt-6">
                    <div class="p-8 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-5 h-5 rounded text-purple-600 focus:ring-purple-500 border-gray-300">
                            <div>
                                <label for="is_active" class="text-sm font-black text-gray-900 uppercase tracking-tight cursor-pointer">Langsung Aktifkan?</label>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa bisa langsung mendapatkan achievement ini</p>
                            </div>
                        </div>
                        <button type="submit"
                                class="h-14 px-10 rounded-2xl text-[11px] font-black uppercase tracking-widest text-white shadow-lg hover:scale-105 transition-all duration-300 hover:shadow-purple-200 will-change-transform whitespace-nowrap"
                                style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                            <i class="fas fa-save mr-2"></i> Simpan Achievement
                        </button>
                    </div>
                </div>

            </form>
        </div>

        {{-- Right Preview Column (Sticky) --}}
        <div class="lg:col-span-4 relative">
            <div class="sticky top-8 space-y-4">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Live Preview Kartu</p>
                
                {{-- Preview Card --}}
                <div class="group relative bg-white rounded-[2rem] border border-gray-100 shadow-xl overflow-hidden will-change-transform" x-data="{ pulse: false }" x-effect="pulse = true; setTimeout(() => pulse = false, 500)">
                    
                    {{-- Status ribbon --}}
                    <div class="absolute top-3 right-3 z-10">
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[8px] font-black uppercase tracking-widest rounded-md">Aktif</span>
                    </div>

                    {{-- Icon header --}}
                    <div class="relative h-32 flex items-center justify-center overflow-hidden transition-colors duration-500"
                         :style="`background: linear-gradient(135deg, ${color}22, ${color}11);`">
                        <div class="absolute inset-0 transition-opacity duration-500 opacity-100"
                             :style="`background: radial-gradient(circle at 50% 50%, ${color}33 0%, transparent 70%);`"
                             :class="pulse ? 'scale-110 opacity-50' : 'scale-100 opacity-100'"></div>
                        
                        <template x-if="previewSrc">
                            <img :src="previewSrc" alt="Preview" class="w-20 h-20 object-contain drop-shadow-xl z-10 will-change-transform animate-float-slow">
                        </template>
                        <template x-if="!previewSrc">
                            <div class="w-20 h-20 rounded-[1.5rem] flex items-center justify-center text-white text-4xl shadow-xl z-10 will-change-transform animate-float-slow"
                                 :style="`background: linear-gradient(135deg, ${color}, ${color}cc); box-shadow: 0 10px 30px ${color}60;`">
                                <i :class="icon"></i>
                            </div>
                        </template>
                    </div>

                    {{-- Content --}}
                    <div class="p-6 space-y-3">
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1" x-text="criteriaType || 'Kriteria Belum Dipilih'"></p>
                            <h3 class="text-base font-black text-gray-900 tracking-tight leading-tight" x-text="title || 'Pencapaian Baru'"></h3>
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed min-h-[2.5rem]" x-text="description || 'Deskripsi pencapaian akan muncul di sini saat Anda mengetik.'"></p>

                        {{-- Criteria badge --}}
                        <div class="flex items-center gap-1.5 pt-1 transition-all duration-300" :class="criteriaValue ? 'opacity-100' : 'opacity-0 scale-95'">
                            <span class="text-[9px] font-black uppercase tracking-widest text-white px-3 py-1 rounded-lg shadow-sm"
                                  :style="`background-color: ${color};`" x-text="`Nilai: ${criteriaValue}`">
                            </span>
                            <span class="text-[9px] font-black uppercase tracking-widest text-amber-500 bg-amber-50 px-3 py-1 rounded-lg shadow-sm"
                                  x-show="xpReward" x-text="`+${xpReward} XP`">
                            </span>
                        </div>

                        {{-- Lore text --}}
                        <div class="border-t border-gray-50 pt-3 transition-all duration-300" :class="loreText ? 'opacity-100 mt-2' : 'hidden'">
                            <p class="text-[10px] italic text-gray-400 leading-relaxed">
                                "<span x-text="loreText"></span>"
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-purple-50/50 rounded-2xl border border-purple-100 text-center">
                    <p class="text-[9px] font-bold text-purple-600 uppercase tracking-widest"><i class="fas fa-magic mr-1"></i> Engine Otomatis 100%</p>
                    <p class="text-[9px] text-purple-500 mt-1">Sistem kami akan mendeteksi pencapaian ini secara otomatis saat siswa menyelesaikan ujian. Tidak perlu coding tambahan!</p>
                </div>
            </div>
        </div>

</div>

<script>
function achievementForm() {
    return {
        title: '',
        description: '',
        loreText: '',
        criteriaType: '',
        criteriaValue: '',
        xpReward: '100',
        color: '#eab308', // Default to Gold
        icon: 'fas fa-crown',
        previewSrc: '',
        colors: [
            { hex: '#eab308', icon: 'fas fa-crown' },      // Gold
            { hex: '#6366f1', icon: 'fas fa-bolt' },       // Indigo
            { hex: '#f97316', icon: 'fas fa-fire' },       // Orange
            { hex: '#ec4899', icon: 'fas fa-heart' },      // Pink
            { hex: '#06b6d4', icon: 'fas fa-wind' },       // Cyan
            { hex: '#22c55e', icon: 'fas fa-leaf' },       // Green
            { hex: '#8b5cf6', icon: 'fas fa-star' },       // Violet
            { hex: '#ef4444', icon: 'fas fa-shield-alt' }, // Red
            { hex: '#1e293b', icon: 'fas fa-moon' },       // Slate
            { hex: '#64748b', icon: 'fas fa-gem' }         // Gray
        ],
        handleFileUpload(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => this.previewSrc = e.target.result;
                reader.readAsDataURL(file);
            }
        }
    }
}
</script>

<style>
.animate-fadeIn { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-float-slow { animation: floatSlow 4s ease-in-out infinite; }
@keyframes floatSlow { 0% { transform: translateY(0); } 50% { transform: translateY(-5px); } 100% { transform: translateY(0); } }
</style>
@endsection
