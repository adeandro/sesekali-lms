@extends('layouts.app')

@section('title', 'Pengaturan - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Pengaturan Sistem')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-fadeIn pb-12" x-data="{ activeTab: '{{ Session::get('active_tab', 'identity') }}' }">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-2">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
            <i class="fas fa-cog text-xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Konfigurasi Sistem</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelola identitas instansi dan kebijakan keamanan akun administrator</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex p-1.5 bg-white border border-gray-100 rounded-3xl shadow-sm space-x-2">
        <button @click="activeTab = 'identity'" 
                :class="activeTab === 'identity' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-gray-500 hover:bg-gray-50'"
                class="flex-1 flex items-center justify-center gap-3 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300">
            <i class="fas fa-school text-sm"></i>
            <span>Identitas Instansi</span>
        </button>
        <button @click="activeTab = 'profile'" 
                :class="activeTab === 'profile' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-gray-500 hover:bg-gray-50'"
                class="flex-1 flex items-center justify-center gap-3 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300">
            <i class="fas fa-user-circle text-sm"></i>
            <span>Profil Admin</span>
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="relative min-h-[400px]">
        <!-- Identitas Instansi Tab -->
        <div x-show="activeTab === 'identity'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-8">
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
                        <div class="space-y-4">
                            <label for="school_name" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Sekolah / Instansi</label>
                            <input type="text" name="school_name" id="school_name" value="{{ old('school_name', $allSettings['school_name'] ?? '') }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                        </div>
                        <div class="space-y-4">
                            <label for="school_phone" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">WhatsApp / Phone</label>
                            <input type="text" name="school_phone" id="school_phone" value="{{ old('school_phone', $allSettings['school_phone'] ?? '') }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        <div class="space-y-4 col-span-full">
                            <label for="school_address" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Alamat Instansi / Sekolah</label>
                            <input type="text" name="school_address" id="school_address" value="{{ old('school_address', $allSettings['school_address'] ?? '') }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        <div class="space-y-4">
                            <label for="report_header_subtitle" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Subtitle Kop Laporan</label>
                            <input type="text" name="report_header_subtitle" id="report_header_subtitle" value="{{ old('report_header_subtitle', $allSettings['report_header_subtitle'] ?? 'Official Exam Results Certificate') }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        <div class="space-y-3" x-data="{ showHeader: {{ (old('show_report_header', $allSettings['show_report_header'] ?? '1') == '1') ? 'true' : 'false' }} }">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tampilkan Kop Laporan</label>
                            <div class="flex items-center justify-between w-full h-14 bg-gray-50 rounded-2xl px-6 border border-transparent transition-all group-hover:border-indigo-100">
                                <span class="text-xs font-bold text-gray-900 uppercase tracking-widest" x-text="showHeader ? 'Tampilkan' : 'Sembunyikan'"></span>
                                <input type="hidden" name="show_report_header" :value="showHeader ? '1' : '0'">
                                <button type="button" @click="showHeader = !showHeader" 
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                    :class="showHeader ? 'bg-indigo-600' : 'bg-gray-200'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="showHeader ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-3" x-data="{ loginHeader: {{ (old('show_login_header', $allSettings['show_login_header'] ?? '1') == '1') ? 'true' : 'false' }} }">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Sekolah di Login</label>
                            <div class="flex items-center justify-between w-full h-14 bg-gray-50 rounded-2xl px-6 border border-transparent transition-all group-hover:border-indigo-100">
                                <span class="text-xs font-bold text-gray-900 uppercase tracking-widest" x-text="loginHeader ? 'Tampilkan' : 'Sembunyikan'"></span>
                                <input type="hidden" name="show_login_header" :value="loginHeader ? '1' : '0'">
                                <button type="button" @click="loginHeader = !loginHeader" 
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                    :class="loginHeader ? 'bg-indigo-600' : 'bg-gray-200'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="loginHeader ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label for="logo" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Logo Instansi</label>
                            <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200 group-hover:border-indigo-200 transition-colors">
                                @if(isset($allSettings['logo']))
                                    <div class="w-20 h-20 rounded-xl bg-white p-2 border border-gray-100 shadow-sm flex items-center justify-center shrink-0">
                                        <img src="{{ asset('storage/' . $allSettings['logo']) }}" alt="Logo" class="max-w-full max-h-full object-contain">
                                    </div>
                                @endif
                                <div class="flex-1 space-y-1">
                                    <input type="file" name="logo" id="logo" accept="image/*" class="hidden" onchange="updateFileName(this, 'logo_name')">
                                    <button type="button" onclick="document.getElementById('logo').click()" class="px-4 py-2 bg-white text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100 hover:bg-indigo-600 hover:text-white transition shadow-sm mb-1">Pilih File</button>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed" id="logo_name">Format: PNG, JPG (Maks. 2MB)</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label for="default_student_avatar" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Foto Default Siswa</label>
                            <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200 group-hover:border-indigo-200 transition-colors">
                                @if(isset($allSettings['default_student_avatar']))
                                    <div class="w-20 h-20 rounded-xl bg-white p-2 border border-gray-100 shadow-sm flex items-center justify-center shrink-0">
                                        <img src="{{ asset('storage/' . $allSettings['default_student_avatar']) }}" alt="Default Avatar" class="max-w-full max-h-full object-contain">
                                    </div>
                                @endif
                                <div class="flex-1 space-y-1">
                                    <input type="file" name="default_student_avatar" id="default_student_avatar" accept="image/*" class="hidden" onchange="updateFileName(this, 'avatar_name')">
                                    <button type="button" onclick="document.getElementById('default_student_avatar').click()" class="px-4 py-2 bg-white text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100 hover:bg-indigo-600 hover:text-white transition shadow-sm mb-1">Pilih File</button>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed" id="avatar_name">Format: PNG, JPG (Maks. 2MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keamanan & Anti-Cheat -->
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
                            <input type="number" name="max_violations" id="max_violations" value="{{ old('max_violations', $allSettings['max_violations'] ?? '3') }}" 
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                        </div>
                        <div class="space-y-3" x-data="{ antiCheat: {{ (old('anti_cheat_active', $allSettings['anti_cheat_active'] ?? '1') == '1') ? 'true' : 'false' }} }">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Status Anti-Cheat Global</label>
                            <div class="flex items-center justify-between w-full h-14 bg-gray-50 rounded-2xl px-6 border border-transparent transition-all group-hover:border-rose-100">
                                <span class="text-xs font-bold text-gray-900 uppercase tracking-widest" x-text="antiCheat ? 'Aktif' : 'Non-Aktif'"></span>
                                <input type="hidden" name="anti_cheat_active" :value="antiCheat ? '1' : '0'">
                                <button type="button" @click="antiCheat = !antiCheat" 
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2"
                                    :class="antiCheat ? 'bg-rose-600' : 'bg-gray-200'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="antiCheat ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
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
                                class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                        </div>
                    </div>
                </div>

                <!-- Fitur Lainnya -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
                    <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 transition-colors">
                            <i class="fas fa-plus-circle text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Fitur Tambahan</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Aktifkan atau nonaktifkan fitur gamifikasi untuk seluruh siswa</p>
                        </div>
                    </div>
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3" x-data="{ gamification: {{ (old('enable_gamification', $allSettings['enable_gamification'] ?? '1') == '1') ? 'true' : 'false' }} }">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Sistem Gamifikasi (Achievement)</label>
                            <div class="flex items-center justify-between w-full h-14 bg-gray-50 rounded-2xl px-6 border border-transparent transition-all group-hover:border-purple-100">
                                <span class="text-xs font-bold text-gray-900 uppercase tracking-widest" x-text="gamification ? 'Aktif' : 'Non-Aktif'"></span>
                                <input type="hidden" name="enable_gamification" :value="gamification ? '1' : '0'">
                                <button type="button" @click="gamification = !gamification" 
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-600 focus:ring-offset-2"
                                    :class="gamification ? 'bg-purple-600' : 'bg-gray-200'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="gamification ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-3" x-data="{ leaderboard: {{ (old('enable_leaderboard', $allSettings['enable_leaderboard'] ?? '1') == '1') ? 'true' : 'false' }} }">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Leaderboard & Peringkat</label>
                            <div class="flex items-center justify-between w-full h-14 bg-gray-50 rounded-2xl px-6 border border-transparent transition-all group-hover:border-purple-100">
                                <span class="text-xs font-bold text-gray-900 uppercase tracking-widest" x-text="leaderboard ? 'Aktif' : 'Non-Aktif'"></span>
                                <input type="hidden" name="enable_leaderboard" :value="leaderboard ? '1' : '0'">
                                <button type="button" @click="leaderboard = !leaderboard" 
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-600 focus:ring-offset-2"
                                    :class="leaderboard ? 'bg-purple-600' : 'bg-gray-200'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="leaderboard ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" class="group relative h-14 px-12 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-3">
                        <i class="fas fa-save text-[10px] group-hover:scale-110 transition-transform"></i> Simpan Konfigurasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Profil Super Admin Tab -->
        <div x-show="activeTab === 'profile'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-8"
             style="display: none;">
            <form action="{{ route('admin.settings.update-profile') }}" method="POST" enctype="multipart/form-data" id="profileForm" class="space-y-8">
                @csrf
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
                    <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white transition-colors">
                            <i class="fas fa-user-circle text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Profil Saya</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Kelola identitas personal dan keamanan akun administrator</p>
                        </div>
                    </div>
                    <div class="p-8 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Foto Profil -->
                            <div class="space-y-4">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Foto Profil</label>
                                <div class="flex items-center gap-6 p-6 bg-gray-50 rounded-[2rem] border border-dashed border-gray-200 group-hover:border-indigo-200 transition-colors">
                                    <div class="relative shrink-0">
                                        <img id="profilePreview" src="{{ auth()->user()->photo_url }}" alt="Profile" class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-sm font-black text-[10px] flex items-center justify-center bg-white text-gray-300">
                                        <button type="button" onclick="document.getElementById('profilePhoto').click()" class="absolute -bottom-2 -right-2 w-8 h-8 bg-indigo-600 text-white rounded-xl shadow-lg flex items-center justify-center hover:scale-110 transition-transform">
                                            <i class="fas fa-camera text-[10px]"></i>
                                        </button>
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <input type="file" name="photo" id="profilePhoto" accept="image/*" class="hidden" onchange="previewImage(this)">
                                        <p class="text-[10px] font-black text-gray-900 uppercase tracking-tight">Ganti Foto</p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Rasio 1:1, Maks. 2MB</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Nama & Email -->
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label for="name" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Lengkap</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" 
                                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                                </div>
                                <div class="space-y-2">
                                    <label for="email" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Alamat Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" 
                                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all" required>
                                </div>
                            </div>
                        </div>

                        <!-- Digital Signature Section -->
                        <div class="pt-8 border-t border-gray-100">
                            <div class="flex flex-col md:flex-row items-center gap-10">
                                <!-- Signature Preview -->
                                <div class="w-full md:w-64 h-32 bg-gray-50 rounded-[2rem] border-2 border-dashed border-gray-200 flex items-center justify-center relative overflow-hidden group">
                                    @if(auth()->user()->signature)
                                        <img id="sigPreview" src="{{ auth()->user()->signature_url }}" alt="Signature" class="max-w-full max-h-full object-contain p-4">
                                    @else
                                        <div id="sigPlaceholder" class="text-center space-y-2">
                                            <i class="fas fa-signature text-2xl text-gray-300"></i>
                                            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest">Belum Ada Tanda Tangan</p>
                                        </div>
                                        <img id="sigPreview" src="" alt="Signature" class="hidden max-w-full max-h-full object-contain p-4">
                                    @endif
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer" onclick="document.getElementById('sigInput').click()">
                                        <i class="fas fa-upload text-white text-xl"></i>
                                    </div>
                                </div>

                                <div class="flex-1 space-y-6">
                                    <div class="space-y-1">
                                        <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                                            Tanda Tangan Digital 
                                            @if(auth()->user()->signature)
                                                <span class="px-2 py-0.5 {{ auth()->user()->is_signature_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }} rounded-md text-[8px] font-black uppercase">
                                                    {{ auth()->user()->is_signature_active ? 'Aktif' : 'Non-Aktif' }}
                                                </span>
                                            @endif
                                        </h3>
                                        <p class="text-[10px] font-bold text-gray-400 leading-relaxed italic uppercase tracking-wider">Gunakan format PNG transparan untuk hasil cetak laporan yang paling jernih dan profesional.</p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4">
                                        <input type="file" name="signature" id="sigInput" accept="image/*" class="hidden" onchange="previewSignature(this)">
                                        <button type="button" onclick="document.getElementById('sigInput').click()" class="px-5 py-2.5 bg-white border border-gray-200 text-indigo-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-indigo-50 hover:border-indigo-100 transition-all shadow-sm">
                                            {{ auth()->user()->signature ? 'Ganti Tanda Tangan' : 'Upload Tanda Tangan' }}
                                        </button>

                                        @if(auth()->user()->signature)
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm">
                                                    <input type="hidden" name="is_signature_active" value="0">
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" name="is_signature_active" value="1" {{ auth()->user()->is_signature_active ? 'checked' : '' }} class="sr-only peer">
                                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                                        <span class="ml-3 text-[9px] font-black text-gray-500 uppercase tracking-widest">Aktif</span>
                                                    </label>
                                                </div>

                                                <button type="button" onclick="confirmDeleteSignature()" class="w-9 h-9 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-100 transition-colors shadow-sm border border-red-100">
                                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keamanan / Password -->
                        <div class="pt-8 border-t border-gray-50 space-y-6">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] flex items-center gap-2">
                                <i class="fas fa-shield-alt"></i> Keamanan Akun (Opsional)
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label for="current_password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Password Saat Ini</label>
                                    <input type="password" name="current_password" id="current_password" 
                                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label for="new_password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Password Baru</label>
                                    <input type="password" name="new_password" id="new_password" 
                                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label for="new_password_confirmation" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Konfirmasi Password</label>
                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" 
                                        class="w-full h-14 bg-gray-50 border-transparent rounded-2xl px-6 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                </div>
                            </div>
                        </div>
                        <!-- Tema Dashboard Section -->
                        <div class="pt-8 border-t border-gray-50 space-y-6">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] flex items-center gap-2">
                                <i class="fas fa-palette"></i> Personalisasi Tema
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" id="admin-theme-picker">
                                @php
                                    $proThemes = [
                                        ['key' => 'indigo', 'name' => 'Indigo Pro',  'color' => '#4f46e5', 'desc' => 'Elegan & Formal'],
                                        ['key' => 'slate',  'name' => 'Slate Dark',  'color' => '#475569', 'desc' => 'Minimalis & Tegas'],
                                        ['key' => 'ocean',  'name' => 'Ocean Blue',  'color' => '#0284c7', 'desc' => 'Segar & Profesional'],
                                    ];
                                    $activeProTheme = Auth::user()->ui_theme ?? 'indigo';
                                @endphp
                                @foreach($proThemes as $pt)
                                    <button onclick="switchAdminTheme('{{ $pt['key'] }}')"
                                            id="admin-theme-btn-{{ $pt['key'] }}"
                                            type="button"
                                            class="group relative rounded-2xl p-4 border-2 text-left transition-all duration-300 hover:shadow-xl {{ $activeProTheme === $pt['key'] ? 'shadow-md border-indigo-600 bg-indigo-50/50' : 'border-gray-100 hover:border-gray-200' }}">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl shadow-md transition-transform group-hover:scale-110 shrink-0" style="background-color: {{ $pt['color'] }};"></div>
                                            <div>
                                                <p class="text-[10px] font-black uppercase tracking-wider mb-0.5 text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $pt['name'] }}</p>
                                                <p class="text-[9px] font-bold text-gray-400">{{ $pt['desc'] }}</p>
                                            </div>
                                        </div>
                                        @if($activeProTheme === $pt['key'])
                                            <div class="check-badge absolute top-3 right-3 w-6 h-6 rounded-full flex items-center justify-center bg-indigo-600">
                                                <i class="fas fa-check text-[10px] text-white"></i>
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="p-8 bg-gray-50/50 flex items-center justify-between gap-6">
                        <div class="flex items-center gap-3 text-amber-600">
                            <i class="fas fa-info-circle text-xs"></i>
                            <p class="text-[9px] font-black uppercase tracking-widest">Kosongkan password jika tidak ingin mengganti</p>
                        </div>
                        <button type="submit" class="h-14 px-12 bg-gray-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-600 transition shadow-lg flex items-center justify-center gap-3">
                            <i class="fas fa-user-edit text-[10px]"></i> Perbarui Profil
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
    function updateFileName(input, targetId) {
        const fileName = input.files[0]?.name || 'Format: PNG, JPG (Maks. 2MB)';
        const target = document.getElementById(targetId);
        target.textContent = fileName;
        if (input.files[0]) {
            target.classList.add('text-indigo-600', 'font-black');
        }
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewSignature(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('sigPreview');
                const placeholder = document.getElementById('sigPlaceholder');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function confirmDeleteSignature() {
        if (confirm('Apakah Anda yakin ingin menghapus tanda tangan digital Anda?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('admin.settings.delete-signature') }}";
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);

            document.body.appendChild(form);
            form.submit();
        }
    }

    document.getElementById('settingsForm').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    });

    document.getElementById('profileForm').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    });

    document.getElementById('profileForm').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    });

    function switchAdminTheme(theme) {
        fetch('{{ route("profile.update-theme") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ theme })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Apply immediately to current view
                document.body.className = document.body.className.replace(/theme-\w+/, 'theme-' + theme);
                
                // Update button visuals
                document.querySelectorAll('[id^="admin-theme-btn-"]').forEach(btn => {
                    btn.className = 'group relative rounded-2xl p-4 border-2 text-left transition-all duration-300 hover:shadow-xl border-gray-100 hover:border-gray-200';
                    const badge = btn.querySelector('.check-badge');
                    if (badge) badge.remove();
                });
                
                const activeBtn = document.getElementById('admin-theme-btn-' + theme);
                if (activeBtn) {
                    activeBtn.className = 'group relative rounded-2xl p-4 border-2 text-left transition-all duration-300 hover:shadow-xl shadow-md border-indigo-600 bg-indigo-50/50';
                    const badge = document.createElement('div');
                    badge.className = 'check-badge absolute top-3 right-3 w-6 h-6 rounded-full flex items-center justify-center bg-indigo-600';
                    badge.innerHTML = '<i class="fas fa-check text-[10px] text-white"></i>';
                    activeBtn.appendChild(badge);
                }
            }
        });
    }
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
