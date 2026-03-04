<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $configs['school_name'] ?? 'SesekaliCBT' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .animated-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="animated-bg min-h-screen flex items-center justify-center p-6">
    <!-- Main Login Container -->
    <div class="w-full max-w-lg">
        <div class="glass-card rounded-[3rem] shadow-2xl p-8 md:p-12 space-y-10 relative overflow-hidden">
            <!-- Decoration -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-pink-500/10 rounded-full blur-2xl"></div>

            <!-- Header Section -->
            <div class="text-center space-y-8">
                <!-- Branding: Pure Logo with Drop Shadow -->
                <div class="group inline-block">
                    @if(isset($configs['logo']))
                        <img src="{{ asset('storage/' . $configs['logo']) }}" alt="Logo" class="h-28 w-auto object-contain drop-shadow-[0_20px_35px_rgba(0,0,0,0.15)] group-hover:drop-shadow-[0_25px_50px_rgba(0,0,0,0.25)] group-hover:scale-105 transition-all duration-700 ease-out">
                    @else
                        <div class="h-24 w-24 bg-white/10 backdrop-blur-sm rounded-[2rem] flex items-center justify-center text-white drop-shadow-2xl group-hover:scale-110 transition-all duration-700">
                            <i class="fas fa-graduation-cap text-5xl"></i>
                        </div>
                    @endif
                </div>
                
                @if($configs['show_login_header'] ?? true)
                <div class="space-y-2">
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase leading-none">
                        {{ $configs['school_name'] ?? 'SesekaliCBT' }}
                    </h1>
                    <div class="flex items-center justify-center gap-3">
                        <span class="h-px w-8 bg-gradient-to-r from-transparent to-gray-200"></span>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.4em]">Integrated CBT</p>
                        <span class="h-px w-8 bg-gradient-to-l from-transparent to-gray-200"></span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Validation/Alert Area -->
            @if ($errors->any())
                <div class="p-5 bg-rose-50 border border-rose-100 rounded-[2rem] flex items-start gap-4">
                    <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-rose-600"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-1">Terjadi Kesalahan</p>
                        @foreach ($errors->all() as $error)
                            <p class="text-xs font-bold text-rose-800">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-500 leading-relaxed max-w-xs mx-auto">Selamat datang kembali! Silakan masukkan kredensial Anda untuk melanjutkan sesi.</p>
                </div>
            @endif

            <!-- Form Section -->
            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="username" class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] pl-4">Identitas (NIS / NIP / Email)</label>
                    <div class="group relative">
                        <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-gray-300 group-focus-within:text-indigo-600 transition-colors">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="{{ old('username') }}"
                            class="w-full bg-gray-50/50 pl-14 pr-8 py-5 border-2 border-gray-100 rounded-3xl focus:border-indigo-600 focus:bg-white focus:outline-none transition-all font-bold text-gray-900 placeholder:text-gray-200"
                            placeholder="Contoh: 12345678"
                            required
                        >
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] pl-4">Kata Sandi</label>
                    <div class="group relative">
                        <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-gray-300 group-focus-within:text-indigo-600 transition-colors">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full bg-gray-50/50 pl-14 pr-14 py-5 border-2 border-gray-100 rounded-3xl focus:border-indigo-600 focus:bg-white focus:outline-none transition-all font-bold text-gray-900 placeholder:text-gray-200"
                            placeholder="••••••••"
                            required
                        >
                        <button
                            type="button"
                            onclick="togglePasswordVisibility()"
                            class="absolute inset-y-0 right-5 flex items-center text-gray-300 hover:text-indigo-600 transition-colors"
                        >
                            <i id="eyeIcon" class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between px-2 pt-2">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" name="remember" class="peer hidden">
                        <div class="w-5 h-5 border-2 border-gray-100 rounded-lg flex items-center justify-center group-hover:border-indigo-600 peer-checked:border-indigo-600 transition-colors mr-3">
                            <div class="w-2.5 h-2.5 bg-indigo-600 rounded-sm opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        </div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-gray-600 peer-checked:text-indigo-600 transition-colors">Ingat Saya</span>
                    </label>
                    
                    <a href="#" class="text-[10px] font-black text-indigo-500 uppercase tracking-widest hover:text-indigo-700 transition-colors">Lupa Akses?</a>
                </div>

                <button 
                    type="submit"
                    class="w-full py-5 bg-indigo-600 text-white rounded-[2rem] text-[11px] font-black uppercase tracking-[0.3em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3 group"
                >
                    Akses Sekarang <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>

            <!-- Footer Text -->
            <div class="text-center pt-4">
                <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.3em] leading-relaxed">
                    Sistem Ujian Terintegrasi • <span class="text-indigo-400 font-bold">{{ date('Y') }}</span>
                </p>
            </div>
        </div>

        <!-- System Status Labels -->
        <div class="mt-8 flex flex-wrap justify-center gap-6">
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-[9px] font-black text-white/70 uppercase tracking-widest">Server Pusat Aktif</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-white/30 rounded-full"></span>
                <span class="text-[9px] font-black text-white/70 uppercase tracking-widest">Enkripsi SHA-256</span>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Sidebar and UI logic could go here if needed 
    </script>
</body>
</html>
