@extends('layouts.app')

@section('title', 'Halaman Pengaturan - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="min-h-screen bg-[var(--brand-bg)] py-8 px-4 sm:px-6 lg:px-8" 
     x-data="avatarGenerator()">
    
    <div class="max-w-4xl mx-auto">
        <!-- Dashboard Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tighter uppercase italic leading-none mb-3">Pengaturan Profil</h1>
                <p class="text-sm text-gray-400 font-bold uppercase tracking-widest flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--brand-primary)] animate-ping"></span>
                    Personalisasi Dashboard Kursus
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="group px-6 py-3 bg-white border border-gray-100 text-gray-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 hover:shadow-xl transition-all flex items-center gap-2">
                    <i class="fas fa-chevron-left group-hover:-translate-x-1 transition-transform"></i> Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-between group animate-fade-in-down">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
                        <i class="fas fa-check text-emerald-500"></i>
                    </div>
                    <span class="text-xs font-black uppercase tracking-wide">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-300 hover:text-emerald-500 transition-colors p-2">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        @endif

        <div class="space-y-10">
            <!-- Section 1: Identitas Formal -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-[var(--brand-primary)] to-[var(--brand-dark)] rounded-[2.5rem] blur opacity-10 group-hover:opacity-20 transition duration-1000"></div>
                <div class="relative bg-white/40 backdrop-blur-2xl border-l-4 border-[var(--brand-primary)] rounded-[2.5rem] p-8 sm:p-10 shadow-md shadow-[var(--brand-glow)] overflow-hidden group/formal transition-all duration-500">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <i class="fas fa-id-card text-9xl"></i>
                    </div>
                    
                    <div class="flex flex-col md:flex-row items-center gap-8 relative z-10 text-center md:text-left">
                        <div class="relative">
                            <img src="{{ Auth::user()->photo_url }}" alt="Formal" loading="lazy" class="w-32 h-32 sm:w-40 sm:h-40 rounded-[2rem] border-4 border-white shadow-2xl object-cover grayscale-[0.2]">
                            <div class="absolute -top-3 -left-3 bg-gray-900 text-white text-[8px] font-black px-3 py-2 rounded-lg uppercase tracking-widest shadow-lg rotate-[-5deg]">
                                Foto Formal Admin
                            </div>
                        </div>
                        
                        <div class="flex-1 space-y-4">
                            <div>
                                <h3 class="text-[10px] font-black text-[var(--brand-primary)] uppercase tracking-[0.3em] mb-1">Data Terverifikasi</h3>
                                <p class="text-3xl font-black text-gray-900 uppercase tracking-tight">{{ Auth::user()->name }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-100/50">
                                <div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 leading-none">Nomor Induk</p>
                                    <p class="text-sm font-black text-gray-800 tracking-tight">{{ Auth::user()->nis }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 leading-none">Kelas</p>
                                    <p class="text-sm font-black text-gray-800 tracking-tight">{{ Auth::user()->grade }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 leading-none">Rombel</p>
                                    <p class="text-sm font-black text-gray-800 tracking-tight uppercase">{{ Auth::user()->class_group }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 leading-none">Status Sesi</p>
                                    <span class="inline-flex px-2 py-1 bg-emerald-50 text-emerald-600 rounded-md text-[9px] font-black">AKTIF</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Section 2: Informasi Pribadi -->
                <div class="lg:col-span-1 bg-white border-l-4 border-[var(--brand-primary)] rounded-[2.5rem] p-8 sm:p-10 shadow-md shadow-[var(--brand-glow)] relative overflow-hidden flex flex-col transition-all duration-500">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-[var(--brand-primary)] rounded-full opacity-10 blur-3xl"></div>
                    
                    <div class="mb-10 text-center sm:text-left">
                        <h3 class="text-xl font-black uppercase tracking-tighter italic mb-2">Persona Dashboard</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Generate karakter unik atau upload foto kamu.</p>
                    </div>

                    <div class="space-y-8">
                        <!-- Preview Generator (Client-Side) -->
                        <div class="bg-white/5 rounded-3xl p-8 border border-white/5 text-center flex flex-col items-center gap-6">
                            <div class="relative group/preview">
                                <template x-if="seed">
                                    <div x-html="getAvatar(seed)" 
                                         class="w-32 h-32 sm:w-40 sm:h-40 rounded-full border-4 border-white bg-[var(--brand-glow)]/20 shadow-2xl relative overflow-hidden transition-all duration-300">
                                    </div>
                                </template>
                                
                                <button @click="generateNew()" 
                                        class="absolute bottom-0 right-0 w-12 h-12 bg-white text-gray-900 rounded-2xl shadow-xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all group overflow-hidden">
                                    <i class="fas fa-dice text-xl group-hover:rotate-180 transition-transform duration-500"></i>
                                </button>
                            </div>
                            
                            <div class="flex flex-col gap-4 w-full">
                                <form action="{{ route('student.profile.avatar.multiavatar') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="seed" :value="seed">
                                    <button type="submit" 
                                            class="w-full py-4 bg-[var(--brand-primary)] text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:opacity-90 transition-all flex items-center justify-center gap-2">
                                        SIMPAN AVATAR <i class="fas fa-check-circle"></i>
                                    </button>
                                </form>
                                <button @click="$refs.fileInput.click()" class="w-full py-4 bg-gray-100 text-gray-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition-all flex items-center justify-center gap-2">
                                    UPLOAD FOTO <i class="fas fa-cloud-upload-alt"></i>
                                </button>
                                <form x-ref="uploadForm" action="{{ route('student.profile.avatar.upload') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                    @csrf
                                    <input type="file" name="avatar_file" x-ref="fileInput" @change="$refs.uploadForm.submit()">
                                </form>
                            </div>
                        </div>

                        <!-- Reset Area -->
                        <div class="pt-6 border-t border-gray-100 space-y-4">
                            <p class="text-[9px] font-black text-gray-500 uppercase tracking-widest text-center">Bosan dengan kustomisasi?</p>
                            <form action="{{ route('student.profile.avatar.reset') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-rose-500/10 text-rose-500 border border-rose-500/20 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] hover:bg-rose-500 hover:text-white transition-all duration-300">
                                    GUNAKAN FOTO INSTANSI <i class="fas fa-undo-alt ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Futuristic Theme Selection -->
                @if(($configs['enable_theme_customization'] ?? '1') == '1' && ($configs['enable_gamification'] ?? '1') == '1')
                <div x-data="{ 
                    currentTheme: '{{ auth()->user()->ui_theme ?? 'indigo' }}',
                    isLoading: null,
                    themeDebounce: null,
                    async switchTheme(themeId, isLocked) {
                        if (isLocked) {
                            Swal.fire({
                                icon: 'lock',
                                title: 'Akses Terkunci',
                                text: 'Capai persyaratan untuk membuka skema warna ini!',
                                background: 'white',
                                color: '#111827',
                                confirmButtonColor: 'var(--brand-primary)'
                            });
                            return;
                        }

                        if (this.isLoading === themeId) return;
                        this.isLoading = themeId;
                        clearTimeout(this.themeDebounce);

                        this.themeDebounce = setTimeout(async () => {
                            try {
                                const response = await fetch('{{ route('student.profile.theme') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ theme: themeId })
                                });

                                const data = await response.json();

                                if (response.ok) {
                                    this.currentTheme = themeId;
                                    document.body.className = document.body.className.replace(/theme-\w+/g, 'theme-' + themeId);
                                    
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true
                                    });
                                    
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Tema Berhasil Terpasang'
                                    });
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Akses Ditolak!', text: data.message });
                                }
                            } catch (e) {
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memperbarui tema.' });
                            } finally {
                                this.isLoading = null;
                            }
                        }, 400);
                    }
                }" class="lg:col-span-2 relative bg-white border-l-4 border-[var(--brand-primary)] rounded-[2.5rem] p-8 md:p-12 shadow-md shadow-[var(--brand-glow)] overflow-hidden transition-all duration-700">
                    
                    <!-- Futuristic Background Elements -->
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-[var(--brand-primary)] opacity-10 rounded-full blur-[80px]"></div>
                    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-[var(--brand-dark)] opacity-10 rounded-full blur-[80px]"></div>
                    
                    <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
                        <div>
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[var(--brand-primary)] to-[var(--brand-dark)] flex items-center justify-center shadow-lg shadow-[var(--brand-glow)]">
                                    <i class="fas fa-palette text-white text-xl"></i>
                                </div>
                                <h3 class="text-3xl font-black text-gray-900 uppercase tracking-tighter italic">Pilih Tema</h3>
                            </div>
                            <p class="text-[11px] text-gray-500 font-bold uppercase tracking-[0.2em] ml-1">Sesuaikan gravitasi visual dashboard kamu.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-6 relative z-10">
                        @php
                            $user = Auth::user();
                            $examsCount = $user->examAttempts()->whereNotNull('submitted_at')->count();
                            $hasFlash = $user->achievements()->where('slug', 'the_flash')->exists();

                            $themes = [
                                ['id' => 'indigo', 'name' => 'Classic Indigo', 'primary' => '#4f46e5', 'dark' => '#4338ca', 'locked' => false, 'desc' => 'Default'],
                                ['id' => 'slate', 'name' => 'Modern Slate', 'primary' => '#475569', 'dark' => '#1e293b', 'locked' => false, 'desc' => 'Free'],
                                ['id' => 'ocean', 'name' => 'Calm Ocean', 'primary' => '#0ea5e9', 'dark' => '#075985', 'locked' => false, 'desc' => 'Free'],
                                ['id' => 'violet', 'name' => 'Ethereal Violet', 'primary' => '#8b5cf6', 'dark' => '#6d28d9', 'locked' => false, 'desc' => 'Free'],
                                ['id' => 'emerald', 'name' => 'Nature Emerald', 'primary' => '#10b981', 'dark' => '#065f46', 'locked' => $user->current_level < 5, 'desc' => 'Level 5'],
                                ['id' => 'volcano', 'name' => 'Active Volcano', 'primary' => '#ef4444', 'dark' => '#7f1d1d', 'locked' => $user->current_level < 15, 'desc' => 'Level 15'],
                                ['id' => 'rose', 'name' => 'Sakura Rose', 'primary' => '#f43f5e', 'dark' => '#9f1239', 'locked' => $user->current_level < 25, 'desc' => 'Level 25'],
                                ['id' => 'amber', 'name' => 'Royal Amber', 'primary' => '#f59e0b', 'dark' => '#92400e', 'locked' => $user->current_level < 35, 'desc' => 'Level 35'],
                                ['id' => 'midnight', 'name' => 'Midnight Dark', 'primary' => '#6366f1', 'dark' => '#0f172a', 'locked' => $user->current_level < 45, 'desc' => 'Level 45'],
                                ['id' => 'cyberpunk', 'name' => 'Cyberpunk FX', 'primary' => '#ff00ff', 'dark' => '#2d002d', 'locked' => !$hasFlash, 'desc' => 'The Flash'],
                            ];
                        @endphp
                        @foreach($themes as $theme)
                            <div class="flex flex-col items-center gap-3">
                                <button 
                                    @click="switchTheme('{{ $theme['id'] }}', {{ $theme['locked'] ? 'true' : 'false' }})"
                                    :class="currentTheme === '{{ $theme['id'] }}' ? 'ring-4 ring-[var(--brand-primary)] shadow-[0_0_20px_var(--brand-glow)] scale-105' : 'hover:scale-110 opacity-80 hover:opacity-100'"
                                    class="relative w-full aspect-square rounded-2xl border-2 border-white shadow-xl transition-all duration-300 overflow-hidden {{ $theme['locked'] ? 'cursor-not-allowed grayscale' : 'cursor-pointer' }}"
                                    style="background: linear-gradient(135deg, {{ $theme['primary'] }} 50%, {{ $theme['dark'] }} 50%)">
                                    
                                    @if($theme['locked'])
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-[1px]">
                                            <i class="fas fa-lock text-white text-base"></i>
                                        </div>
                                    @endif

                                    <!-- Loading Spinner -->
                                    <div x-show="isLoading === '{{ $theme['id'] }}'" class="absolute inset-0 bg-white/40 flex items-center justify-center">
                                        <div class="w-6 h-6 border-4 border-[var(--brand-primary)] border-t-transparent rounded-full animate-spin"></div>
                                    </div>
                                </button>
                                <div class="text-center space-y-0.5">
                                    <p class="text-[9px] font-black uppercase tracking-tighter" :class="currentTheme === '{{ $theme['id'] }}' ? 'text-[var(--brand-primary)]' : 'text-gray-900'">{{ $theme['name'] }}</p>
                                    @if($theme['locked'])
                                        <p class="text-[7px] font-bold text-gray-400 uppercase">{{ $theme['desc'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @elseif(($configs['enable_gamification'] ?? '1') == '0')
                    <div class="lg:col-span-2 bg-[var(--brand-glow)]/20 rounded-[2.5rem] p-12 text-center border-l-4 border-[var(--brand-primary)] shadow-md shadow-[var(--brand-glow)]">
                        <i class="fas fa-lock text-[var(--brand-primary)]/30 text-4xl mb-4"></i>
                        <h3 class="text-xl font-black text-[var(--brand-primary)] uppercase italic mb-2">Gelar & Tema Tidak Tersedia</h3>
                        <p class="text-sm text-[var(--brand-primary)]/60 font-medium max-w-md mx-auto">Admin menonaktifkan fitur Gamifikasi. Hubungi Admin untuk mengaktifkan kembali skema warna kustom.</p>
                    </div>
                @endif

                <!-- Section 4: Avatar Spesial (Reward) -->
                @if(($configs['enable_gamification'] ?? '1') == '1')
                <div class="lg:col-span-3 bg-gray-900 rounded-[2.5rem] p-8 sm:p-10 shadow-2xl text-white relative overflow-hidden">
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-amber-500 rounded-full opacity-10 blur-3xl"></div>
                    
                    <div class="mb-10 text-center sm:text-left flex flex-col md:flex-row md:items-end justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-black uppercase tracking-tighter italic mb-2 text-amber-400">Avatar Spesial Rewards</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Gunakan karakter eksklusif yang kamu buka!</p>
                        </div>
                        <div class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl">
                            <p class="text-[8px] font-black text-gray-500 uppercase tracking-widest leading-none mb-1">Koleksi Terbuka</p>
                            <p class="text-sm font-black text-white leading-none">
                                @php
                                    $hasPerfect = Auth::user()->achievements()->where('slug', 'perfect_score')->exists();
                                    $hasLevel20 = Auth::user()->current_level >= 20;
                                    $count = ($hasPerfect ? 1 : 0) + ($hasLevel20 ? 1 : 0);
                                @endphp
                                {{ $count }}/2
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                        <!-- Special A: King of Exams -->
                        <div class="group/special p-6 rounded-[2rem] border transition-all duration-500 {{ $hasPerfect ? 'bg-white/5 border-white/10 hover:bg-white/10' : 'bg-black/20 border-white/5 opacity-50' }}">
                            <div class="flex flex-col items-center gap-4">
                                <div class="relative w-24 h-24 sm:w-28 sm:h-28">
                                    @if($hasPerfect)
                                        <div x-html="getAvatar('KingCBT')" class="w-full h-full"></div>
                                    @else
                                        <div class="w-full h-full bg-gray-800 rounded-full flex items-center justify-center border-4 border-gray-700">
                                            <i class="fas fa-question text-4xl text-gray-600"></i>
                                        </div>
                                    @endif
                                    @if(!$hasPerfect)
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="bg-black/60 backdrop-blur-sm w-full h-full rounded-full flex items-center justify-center">
                                                <i class="fas fa-lock text-white/40"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] {{ $hasPerfect ? 'text-amber-400' : 'text-gray-500' }}">King of Exams</p>
                                    <p class="text-[8px] font-bold text-gray-400 mt-1 uppercase italic">Unlock with 'Perfect Score'</p>
                                </div>
                                @if($hasPerfect)
                                    <form action="{{ route('student.profile.avatar.multiavatar') }}" method="POST" class="w-full mt-2">
                                        @csrf
                                        <input type="hidden" name="seed" value="KingCBT">
                                        <button type="submit" class="w-full py-2.5 bg-amber-500 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-amber-400 hover:scale-95 transition-all">PAKAI KARAKTER</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Special B: Cyber Master -->
                        <div class="group/special p-6 rounded-[2rem] border transition-all duration-500 {{ $hasLevel20 ? 'bg-white/5 border-white/10 hover:bg-white/10' : 'bg-black/20 border-white/5 opacity-50' }}">
                            <div class="flex flex-col items-center gap-4">
                                <div class="relative w-24 h-24 sm:w-28 sm:h-28">
                                    @if($hasLevel20)
                                        <div x-html="getAvatar('CyberPro')" class="w-full h-full"></div>
                                    @else
                                        <div class="w-full h-full bg-gray-800 rounded-full flex items-center justify-center border-4 border-gray-700">
                                            <i class="fas fa-question text-4xl text-gray-600"></i>
                                        </div>
                                    @endif
                                    @if(!$hasLevel20)
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="bg-black/60 backdrop-blur-sm w-full h-full rounded-full flex items-center justify-center">
                                                <i class="fas fa-lock text-white/40"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] {{ $hasLevel20 ? 'text-[var(--brand-primary)]' : 'text-gray-500' }}">Cyber Master</p>
                                    <p class="text-[8px] font-bold text-gray-400 mt-1 uppercase italic">Unlock at Level 20</p>
                                </div>
                                @if($hasLevel20)
                                    <form action="{{ route('student.profile.avatar.multiavatar') }}" method="POST" class="w-full mt-2">
                                        @csrf
                                        <input type="hidden" name="seed" value="CyberPro">
                                        <button type="submit" class="w-full py-2.5 bg-[var(--brand-primary)] text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-[var(--brand-dark)] hover:scale-95 transition-all">PAKAI KARAKTER</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Section 4: Keamanan -->
                <div class="lg:col-span-3 bg-white border-l-4 border-[var(--brand-primary)] rounded-[2.5rem] p-8 sm:p-10 shadow-md shadow-[var(--brand-glow)] relative overflow-hidden flex flex-col group/security transition-all duration-500">
                    <div class="mb-10 text-center sm:text-left">
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tighter italic mb-2">Akses Keamanan</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Update kata sandi untuk melindungi akun kamu.</p>
                    </div>

                    <form action="{{ route('student.profile.password') }}" method="POST" class="flex-1 space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="group">
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1 transition-colors group-focus-within:text-[var(--brand-primary)]">Password Saat Ini</label>
                                <div class="relative">
                                    <i class="fas fa-shield-alt absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-[var(--brand-primary)] transition-colors"></i>
                                    <input type="password" name="current_password" required class="w-full pl-12 pr-6 py-4 bg-gray-50 border-transparent rounded-xl text-sm font-bold focus:bg-white focus:border-[var(--brand-primary)] focus:ring-4 focus:ring-[var(--brand-glow)] transition-all outline-none">
                                </div>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1 transition-colors group-focus-within:text-[var(--brand-primary)]">Password Baru</label>
                                <div class="relative">
                                    <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-[var(--brand-primary)] transition-colors"></i>
                                    <input type="password" name="password" required class="w-full pl-12 pr-6 py-4 bg-gray-50 border-transparent rounded-xl text-sm font-bold focus:bg-white focus:border-[var(--brand-primary)] focus:ring-4 focus:ring-[var(--brand-glow)] transition-all outline-none">
                                </div>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1 transition-colors group-focus-within:text-[var(--brand-primary)]">Konfirmasi Password Baru</label>
                                <div class="relative">
                                    <i class="fas fa-check-double absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-[var(--brand-primary)] transition-colors"></i>
                                    <input type="password" name="password_confirmation" required class="w-full pl-12 pr-6 py-4 bg-gray-50 border-transparent rounded-xl text-sm font-bold focus:bg-white focus:border-[var(--brand-primary)] focus:ring-4 focus:ring-[var(--brand-glow)] transition-all outline-none">
                                </div>
                            </div>
                        </div>
                        <div class="pt-4 mt-auto">
                            <button type="submit" class="w-full py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] hover:bg-black hover:shadow-xl transition-all active:scale-95 shadow-lg shadow-gray-100">UPDATE PASSWORD</button>
                        </div>
                   </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function avatarGenerator() {
    return {
        seed: '{{ Auth::user()->custom_avatar ?: (Auth::user()->id . "-" . Str::random(7)) }}',
        cache: {},
        
        getAvatar(seed) {
            if (!this.cache[seed]) {
                this.cache[seed] = multiavatar(seed);
            }
            return this.cache[seed];
        },
        
        generateNew() {
            this.seed = Math.random().toString(36).substring(7);
        }
    }
}
</script>

<style>
@keyframes fade-in-down {
    0% { opacity: 0; transform: translateY(-10px); }
    100% { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fade-in-down 0.5s ease-out forwards;
}
@keyframes bounce-short {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
.animate-bounce-short {
    animation: bounce-short 1.5s ease-in-out infinite;
}
</style>
@endsection
