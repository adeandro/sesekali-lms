@extends('layouts.app')

@section('title', 'Pengaturan - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Pengaturan Sistem')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-2">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
            <i class="fas fa-cog text-xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Konfigurasi Sistem</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelola identitas instansi dan kebijakan keamanan ujian global</p>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm" class="space-y-8">
        @csrf

        <!-- Identitas Sekolah -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 transition-colors">
                    <i class="fas fa-school text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Identitas Instansi</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Informasi dasar sekolah yang akan tampil di seluruh profil sistem</p>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label for="school_name" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Sekolah / Instansi</label>
                    <input type="text" name="school_name" id="school_name" value="{{ old('school_name', $allSettings['school_name'] ?? '') }}" 
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                </div>
                <div class="space-y-4">
                    <label for="logo" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Logo Instansi</label>
                    <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200 group-hover:border-indigo-200 transition-colors">
                        @if(isset($allSettings['logo']))
                            <div class="w-20 h-20 rounded-xl bg-white p-2 border border-gray-100 shadow-sm flex items-center justify-center shrink-0">
                                <img src="{{ asset('storage/' . $allSettings['logo']) }}" alt="Logo" class="max-w-full max-h-full object-contain">
                            </div>
                        @else
                            <div class="w-20 h-20 rounded-xl bg-white p-2 border border-gray-100 shadow-sm flex items-center justify-center shrink-0">
                                <i class="fas fa-image text-2xl text-gray-200"></i>
                            </div>
                        @endif
                        <div class="flex-1 space-y-1">
                            <input type="file" name="logo" id="logo" accept="image/*" class="hidden" onchange="updateFileName(this)">
                            <button type="button" onclick="document.getElementById('logo').click()" class="px-4 py-2 bg-white text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100 hover:bg-indigo-600 hover:text-white transition shadow-sm mb-1">Pilih File</button>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed" id="fileName">Format: PNG, JPG (Maks. 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Konfigurasi Ujian (Anti-Cheat) -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 transition-colors">
                    <i class="fas fa-shield-alt text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Keamanan & Anti-Cheat</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Aturan ketat untuk menjaga integritas pelaksanaan ujian online</p>
                </div>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <label for="max_violations" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Batas Pelanggaran (Strike)</label>
                    <div class="relative">
                        <input type="number" name="max_violations" id="max_violations" value="{{ old('max_violations', $allSettings['max_violations'] ?? '3') }}" 
                            min="1" max="99" class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                        <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest">Strike</span>
                    </div>
                    <p class="text-[9px] font-bold text-rose-400 uppercase tracking-widest leading-relaxed px-1">Sesi akan langsung di-submit jika mencapai batas ini.</p>
                </div>
                <div class="space-y-4">
                    <label for="anti_cheat_active" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Status Anti-Cheat Global</label>
                    <select name="anti_cheat_active" id="anti_cheat_active" 
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                        <option value="1" {{ (old('anti_cheat_active', $allSettings['anti_cheat_active'] ?? '1') == '1') ? 'selected' : '' }}>AKTIF (Enabled)</option>
                        <option value="0" {{ (old('anti_cheat_active', $allSettings['anti_cheat_active'] ?? '1') == '0') ? 'selected' : '' }}>NONAKTIF (Disabled)</option>
                    </select>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed px-1">Deteksi jendela mengambang dan split-screen secara sistem.</p>
                </div>
            </div>
        </div>

        <!-- Akademik -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 transition-colors">
                    <i class="fas fa-calendar-check text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Konfigurasi Akademik</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Pengaturan periode aktif untuk sinkronisasi data ujian</p>
                </div>
            </div>
            <div class="p-8">
                <div class="md:w-1/2 space-y-2">
                    <label for="academic_year" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran Aktif</label>
                    <input type="text" name="academic_year" id="academic_year" value="{{ old('academic_year', $allSettings['academic_year'] ?? '') }}" 
                        placeholder="Contoh: 2023/2024" 
                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-4">
            <button type="submit" class="group relative h-14 px-12 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-3">
                <i class="fas fa-save text-[10px] group-hover:scale-110 transition-transform"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-white/80 backdrop-blur-sm z-[100] hidden flex items-center justify-center animate-fadeIn">
    <div class="text-center space-y-6">
        <div class="relative w-20 h-20 mx-auto">
            <div class="absolute inset-0 border-4 border-indigo-50 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
        </div>
        <div class="space-y-1">
            <p class="text-[10px] font-black text-gray-900 uppercase tracking-[0.3em]">Menyimpan Konfigurasi</p>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Mohon tunggu sebentar...</p>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const fileName = input.files[0]?.name || 'Format: PNG, JPG (Maks. 2MB)';
        document.getElementById('fileName').textContent = fileName;
        if (input.files[0]) {
            document.getElementById('fileName').classList.add('text-indigo-600', 'font-black');
        }
    }

    document.getElementById('settingsForm').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    });
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
