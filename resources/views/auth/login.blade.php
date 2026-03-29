<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ $configs['school_name'] ?? 'SesekaliCBT' }}</title>
    <link rel="icon" type="image/x-icon"
          href="{{ isset($configs['logo']) ? asset('storage/'.$configs['logo']) : asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

@php 
    $isLeaderboardEnabled = ($configs['enable_leaderboard'] ?? '1') === '1';
@endphp

<body class="animated-bg min-h-screen flex items-center justify-center p-4 md:p-8">
<div class="w-full {{ $isLeaderboardEnabled ? 'max-w-4xl' : 'max-w-md' }}">


    {{-- STRIP MOBILE: hanya tampil di layar kecil --}}
    @if($isLeaderboardEnabled && $topStudents->count() >= 3)

    <div class="md:hidden mb-4 rounded-2xl p-4"
         style="background: linear-gradient(135deg, #312e81, #1e1b4b);">

        <p class="text-[8px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-3">
            Top Global Season Ini
        </p>

        <div class="flex items-end justify-center gap-6">

            {{-- Rank 2 --}}
            @php $p2 = $topStudents->firstWhere('rank', 2) @endphp
            <div class="flex flex-col items-center gap-1.5">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-black text-white border-[2px] border-slate-400"
                         style="background: linear-gradient(135deg, #94a3b8, #64748b);">
                        {{ $p2['initials'] }}
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-[14px] h-[14px] bg-slate-400 rounded-full flex items-center justify-center text-[7px] font-black text-white border border-[#1e1b4b]">2</span>
                </div>
                <p class="text-[9px] font-bold text-white leading-none">{{ $p2['name'] }}</p>
                <p class="text-[8px] text-indigo-300 leading-none">{{ $p2['points'] }}</p>
            </div>

            {{-- Rank 1 --}}
            @php $p1 = $topStudents->firstWhere('rank', 1) @endphp
            <div class="flex flex-col items-center gap-1.5">
                <svg width="20" height="12" viewBox="0 0 40 24" fill="none" class="crown-bounce" style="margin-bottom: -2px;">
                    <path d="M2 20L8 6l10 10L28 2l10 18" stroke="#fbbf24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="2" cy="20" r="3" fill="#fbbf24"/>
                    <circle cx="20" cy="16" r="3" fill="#fbbf24"/>
                    <circle cx="38" cy="20" r="3" fill="#fbbf24"/>
                </svg>
                <div class="relative">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-base font-black text-white border-[2px] border-yellow-400"
                         style="background: linear-gradient(135deg, #fbbf24, #f59e0b); box-shadow: 0 4px 16px rgba(251,191,36,0.4);">
                        {{ $p1['initials'] }}
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full flex items-center justify-center text-[8px] font-black text-yellow-900 border border-[#1e1b4b]">1</span>
                </div>
                <p class="text-[9px] font-black text-white leading-none">{{ $p1['name'] }}</p>
                <p class="text-[8px] text-yellow-400 font-bold leading-none">{{ $p1['points'] }}</p>
            </div>

            {{-- Rank 3 --}}
            @php $p3 = $topStudents->firstWhere('rank', 3) @endphp
            <div class="flex flex-col items-center gap-1.5">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-black text-white border-[2px] border-orange-500"
                         style="background: linear-gradient(135deg, #f97316, #ea580c);">
                        {{ $p3['initials'] }}
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-[14px] h-[14px] bg-orange-500 rounded-full flex items-center justify-center text-[7px] font-black text-white border border-[#1e1b4b]">3</span>
                </div>
                <p class="text-[9px] font-bold text-white leading-none">{{ $p3['name'] }}</p>
                <p class="text-[8px] text-indigo-300 leading-none">{{ $p3['points'] }}</p>
            </div>

        </div>
    </div>
    @endif

    {{-- MAIN CARD --}}
    <div class="flex flex-col {{ $isLeaderboardEnabled ? 'md:flex-row' : '' }} rounded-[20px] overflow-hidden"
         style="box-shadow: 0 24px 60px rgba(0,0,0,0.4);">


        {{-- KOLOM KIRI: Form --}}
        <div class="w-full {{ $isLeaderboardEnabled ? 'md:w-[46%]' : '' }} bg-white px-10 py-12 flex flex-col justify-center">


            {{-- Logo + Nama Sekolah --}}
            <div class="flex items-center gap-2.5 mb-6">
                @if(isset($configs['logo']) && $configs['logo'])
                    <img src="{{ asset('storage/' . $configs['logo']) }}"
                         alt="Logo Sekolah"
                         class="h-9 w-auto object-contain">
                @else
                    <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center shrink-0"
                         style="background: linear-gradient(135deg, #6366f1, #4338ca);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <p class="text-[15px] font-black text-gray-900 leading-tight">
                        {{ $configs['school_name'] ?? 'SesekaliCBT' }}
                    </p>
                    <p class="text-[8px] text-gray-400 font-bold uppercase tracking-[2px]">
                        Integrated CBT
                    </p>
                </div>
            </div>

            {{-- Heading --}}
            <div class="mb-7">
                <h1 class="text-[22px] font-black text-gray-900 tracking-tight leading-none mb-1">
                    Selamat datang
                </h1>
                <p class="text-[12px] text-gray-500">
                    Masuk dengan NIS, NIP, atau email kamu
                </p>
            </div>

            {{-- Error --}}
            @if($errors->any())
            <div class="mb-5 p-4 rounded-[14px] flex items-start gap-3 bg-rose-50 border border-rose-200">
                <div class="w-8 h-8 rounded-[10px] bg-rose-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-exclamation-triangle text-rose-500 text-sm"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-1">Terjadi Kesalahan</p>
                    @foreach($errors->all() as $error)
                        <p class="text-xs font-semibold text-rose-700">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('login') }}" method="POST">
                @csrf

                {{-- Username --}}
                <div class="mb-3.5">
                    <label for="username" class="block text-[9px] font-black text-gray-400 uppercase tracking-[1.5px] pl-1 mb-1.5">
                        Identitas (NIS / NIP / Email)
                    </label>
                    <div class="flex items-center h-12 px-4 rounded-[14px] border-[1.5px] border-gray-200 bg-gray-50 focus-within:border-indigo-500 focus-within:bg-white transition-all duration-200">
                        <svg class="w-[14px] h-[14px] text-gray-300 shrink-0 mr-3" viewBox="0 0 24 24" fill="none">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 11a4 4 0 100-8 4 4 0 000 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input type="text" id="username" name="username"
                               value="{{ old('username') }}"
                               placeholder="Contoh: 12345678"
                               class="flex-1 bg-transparent outline-none text-[13px] font-semibold text-gray-800 placeholder:text-gray-300"
                               required autofocus>
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-5">
                    <label for="password" class="block text-[9px] font-black text-gray-400 uppercase tracking-[1.5px] pl-1 mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="flex items-center h-12 px-4 rounded-[14px] border-[1.5px] border-gray-200 bg-gray-50 focus-within:border-indigo-500 focus-within:bg-white transition-all duration-200">
                        <svg class="w-[14px] h-[14px] text-gray-300 shrink-0 mr-3" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input type="password" id="password" name="password"
                               placeholder="••••••••"
                               class="flex-1 bg-transparent outline-none text-[13px] font-semibold text-gray-800 placeholder:text-gray-300"
                               required>
                        <button type="button" onclick="togglePassword()"
                                class="text-gray-300 hover:text-indigo-500 transition-colors ml-2 flex items-center">
                            <svg id="eyeIcon" class="w-[14px] h-[14px]" viewBox="0 0 24 24" fill="none">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember + Forgot --}}
                <div class="flex items-center justify-between mb-5">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember" id="remember" class="peer hidden">
                        <div class="w-4 h-4 rounded-[5px] border-[1.5px] border-gray-200 flex items-center justify-center transition-all duration-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-600">
                            <svg class="w-[9px] h-[9px] text-white opacity-0 peer-checked:opacity-100 transition-opacity" viewBox="0 0 12 12" fill="none">
                                <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-[11px] font-semibold text-gray-400 group-hover:text-gray-600 transition-colors">
                            Ingat saya
                        </span>
                    </label>
                    <a href="#" class="text-[11px] font-black text-indigo-500 hover:text-indigo-700 transition-colors">
                        Lupa akses?
                    </a>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full h-[50px] rounded-[14px] flex items-center justify-center gap-2.5 text-[12px] font-black text-white uppercase tracking-[2px] hover:opacity-90 active:scale-[0.99] transition-all duration-200"
                        style="background: linear-gradient(135deg, #6366f1, #4338ca); box-shadow: 0 8px 24px rgba(99,102,241,0.35);">
                    Akses Sekarang
                    <svg class="w-[13px] h-[13px]" viewBox="0 0 24 24" fill="none">
                        <path d="M5 12h14M12 5l7 7-7 7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

            </form>

            {{-- Status --}}
            <div class="flex justify-center items-center gap-4 mt-5">
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"
                          style="box-shadow: 0 0 6px #34d399;"></span>
                    <span class="text-[9px] text-gray-400 font-semibold uppercase tracking-[1px]">Server Aktif</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    <span class="text-[9px] text-gray-400 font-semibold uppercase tracking-[1px]">SHA-256</span>
                </div>
            </div>

        </div>
        {{-- END KOLOM KIRI --}}

        @if($isLeaderboardEnabled)
        {{-- KOLOM KANAN: Leaderboard (desktop only) --}}
        <div class="hidden md:flex flex-1 flex-col justify-between px-9 py-11 relative overflow-hidden"

             style="background: linear-gradient(160deg, #1e1b4b 0%, #312e81 50%, #1e1b4b 100%);">

            {{-- Dekorasi --}}
            <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full pointer-events-none"
                 style="background: rgba(139,92,246,0.07);"></div>
            <div class="absolute -bottom-16 -left-16 w-52 h-52 rounded-full pointer-events-none"
                 style="background: rgba(99,102,241,0.07);"></div>

            {{-- Header --}}
            <div class="relative mb-8">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-[14px] h-[14px]" viewBox="0 0 24 24" fill="#fbbf24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <p class="text-[9px] font-black text-indigo-300 uppercase tracking-[2.5px]">
                        Top Global Season Ini
                    </p>
                </div>
                <h2 class="text-[22px] font-black text-white tracking-tight">
                    Hall of Champions
                </h2>
            </div>

            @if($topStudents->count() >= 3)

            {{-- Podium --}}
            <div class="flex items-end justify-center gap-3.5 mb-5 flex-1">

                {{-- Rank 2 --}}
                @php $p2 = $topStudents->firstWhere('rank', 2) @endphp
                <div class="flex flex-col items-center gap-2.5">
                    <div class="relative">
                        <div class="w-[54px] h-[54px] rounded-full flex items-center justify-center text-base font-black text-white border-[3px] border-slate-400"
                             style="background: linear-gradient(135deg, #94a3b8, #64748b); box-shadow: 0 4px 16px rgba(148,163,184,0.25);">
                            {{ $p2['initials'] }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-slate-400 rounded-full flex items-center justify-center text-[9px] font-black text-white border-[2px] border-[#1e1b4b]">2</span>
                    </div>
                    <div class="text-center">
                        <p class="text-[11px] font-bold text-white leading-snug">{{ $p2['name'] }}</p>
                        <p class="text-[10px] text-indigo-300 font-semibold">{{ $p2['points'] }} APP</p>
                    </div>
                    <div class="w-[84px] h-[60px] rounded-t-lg flex items-center justify-center"
                         style="background: rgba(148,163,184,0.12); border: 1px solid rgba(148,163,184,0.18);">
                        <span class="text-[22px] font-black" style="color: rgba(148,163,184,0.3);">#2</span>
                    </div>
                </div>

                {{-- Rank 1 --}}
                @php $p1 = $topStudents->firstWhere('rank', 1) @endphp
                <div class="flex flex-col items-center gap-2.5">
                    <svg width="26" height="16" viewBox="0 0 40 24" fill="none"
                         class="crown-bounce" style="margin-bottom: -6px;">
                        <path d="M2 20L8 6l10 10L28 2l10 18" stroke="#fbbf24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="2" cy="20" r="3" fill="#fbbf24"/>
                        <circle cx="20" cy="16" r="3" fill="#fbbf24"/>
                        <circle cx="38" cy="20" r="3" fill="#fbbf24"/>
                    </svg>
                    <div class="relative">
                        <div class="w-[68px] h-[68px] rounded-full flex items-center justify-center text-[22px] font-black text-white border-[3px] border-yellow-400"
                             style="background: linear-gradient(135deg, #fbbf24, #f59e0b); box-shadow: 0 6px 28px rgba(251,191,36,0.45);">
                            {{ $p1['initials'] }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-[22px] h-[22px] bg-yellow-400 rounded-full flex items-center justify-center text-[10px] font-black text-yellow-900 border-[2px] border-[#1e1b4b]">1</span>
                    </div>
                    <div class="text-center">
                        <p class="text-[13px] font-black text-white leading-snug">{{ $p1['name'] }}</p>
                        <p class="text-[11px] text-yellow-400 font-bold">{{ $p1['points'] }} APP</p>
                    </div>
                    <div class="w-[84px] h-[88px] rounded-t-lg flex items-center justify-center"
                         style="background: rgba(251,191,36,0.10); border: 1px solid rgba(251,191,36,0.22);">
                        <span class="text-[30px] font-black" style="color: rgba(251,191,36,0.25);">#1</span>
                    </div>
                </div>

                {{-- Rank 3 --}}
                @php $p3 = $topStudents->firstWhere('rank', 3) @endphp
                <div class="flex flex-col items-center gap-2.5">
                    <div class="relative">
                        <div class="w-[54px] h-[54px] rounded-full flex items-center justify-center text-base font-black text-white border-[3px] border-orange-500"
                             style="background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 4px 16px rgba(249,115,22,0.25);">
                            {{ $p3['initials'] }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center text-[9px] font-black text-white border-[2px] border-[#1e1b4b]">3</span>
                    </div>
                    <div class="text-center">
                        <p class="text-[11px] font-bold text-white leading-snug">{{ $p3['name'] }}</p>
                        <p class="text-[10px] text-indigo-300 font-semibold">{{ $p3['points'] }} APP</p>
                    </div>
                    <div class="w-[84px] h-[44px] rounded-t-lg flex items-center justify-center"
                         style="background: rgba(249,115,22,0.10); border: 1px solid rgba(249,115,22,0.18);">
                        <span class="text-[18px] font-black" style="color: rgba(249,115,22,0.25);">#3</span>
                    </div>
                </div>

            </div>

            {{-- Garis base --}}
            <div class="h-[2px] rounded-full mb-5"
                 style="background: linear-gradient(90deg, transparent, rgba(165,180,252,0.25), transparent);"></div>

            {{-- Season card --}}
            <div class="rounded-[14px] p-[18px] flex items-center justify-between"
                 style="background: rgba(255,255,255,0.05); border: 1px solid rgba(165,180,252,0.15);">
                <div>
                    <p class="text-[8px] font-black text-indigo-300 uppercase tracking-[2px] mb-1">Season Aktif</p>
                    <p class="text-[14px] font-bold text-white mb-0.5">
                        {{ $configs['academic_year'] ?? '2025/2026' }}
                    </p>
                    <p class="text-[10px] text-indigo-300">Login untuk lihat posisi kamu</p>
                </div>
                <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center shrink-0"
                     style="background: rgba(99,102,241,0.2);">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                              stroke="#a5b4fc" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>

            @else
            {{-- Fallback --}}
            <div class="flex-1 flex items-center justify-center px-4">
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center"
                         style="background: rgba(165,180,252,0.1);">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                                  stroke="#a5b4fc" stroke-width="1.5" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <p class="text-[12px] text-indigo-300 font-semibold leading-relaxed">
                        Belum ada data leaderboard.<br>
                        <span class="text-white font-black">Jadilah yang pertama!</span>
                    </p>
                </div>
            </div>
            @endif

        </div>
        {{-- END KOLOM KANAN --}}
        @endif


    </div>
    {{-- END MAIN CARD --}}

</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="1" y1="1" x2="23" y2="23"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round"/>`;
        } else {
            input.type = 'password';
            icon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                      stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="12" r="3"
                        stroke="currentColor" stroke-width="2"/>`;
        }
        input.focus();
    }
</script>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- FLOATING ANNOUNCEMENT OVERLAY — z-index 9999, tidak menyentuh  --}}
{{-- struktur grid Login Form atau Hall of Fame yang sudah ada.      --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@php
    $hasUrgent  = isset($urgentAnnouncements)  && $urgentAnnouncements->count() > 0;
    $hasRolling = isset($rollingAnnouncements) && $rollingAnnouncements->count() > 0;
@endphp

@if($hasUrgent || $hasRolling)
{{-- Container Utama --}}
<div id="ann-overlay" class="ann-minimized"
     style="
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        max-width: 360px;
        width: calc(100vw - 2rem);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
     ">

    {{-- ─── MINI TRIGGER (Floating Bell) ────────────────────────────── --}}
    <button id="ann-trigger" onclick="toggleAnn(false)"
            style="
                width: 52px; height: 52px; border-radius: 26px;
                background: linear-gradient(135deg, #6366f1, #4338ca);
                box-shadow: 0 8px 24px rgba(99,102,241,0.4);
                display: none; align-items: center; justify-content: center;
                border: none; cursor: pointer; position: absolute; bottom: 0; right: 0;
                animation: {{ $hasUrgent ? 'ann-pulse 2s infinite' : 'none' }};
                z-index: 10;
            "
            title="Lihat Pengumuman">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M13.73 21a2 2 0 01-3.46 0" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @php $totalCount = ($urgentAnnouncements->count() + $rollingAnnouncements->count()); @endphp
        @if($totalCount > 0)
        <span style="
                position: absolute; top: -2px; right: -2px;
                background: #ef4444; color: white; font-size: 10px; font-weight: 900;
                min-width: 18px; h-18px; border-radius: 9px;
                display: flex; align-items: center; justify-content: center;
                border: 2px solid #fff;
              ">
            {{ $totalCount }}
        </span>
        @endif
    </button>

    {{-- Wrapper Konten (Hidden saat Minimized) --}}
    <div id="ann-content-wrapper" style="display: flex; flex-direction: column; gap: 10px; transition: opacity 0.3s;">

        {{-- Button Minimize (Desktop/Mobile) --}}
        <div style="display: flex; justify-content: flex-end; margin-bottom: -5px;">
            <button onclick="toggleAnn(true)"
                    style="
                        background: rgba(255,255,255,0.9); border: 1px solid rgba(0,0,0,0.05);
                        width: 32px; height: 32px; border-radius: 10px; cursor: pointer;
                        display: flex; align-items: center; justify-content: center;
                        color: #6366f1; box-shadow: 0 4px 12px rgba(0,0,0,0.05);
                    "
                    title="Sembunyikan">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                    <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        {{-- ─── URGENT CARD ─────────────────────────────────────────────── --}}
        @if($hasUrgent)
        <div id="ann-urgent-card"
             style="
                background: linear-gradient(135deg, #be123c, #9f1239);
                border-radius: 16px; padding: 14px 16px;
                display: flex; align-items: flex-start; gap: 12px;
                box-shadow: 0 8px 32px rgba(190,18,60,0.45);
                animation: ann-pulse 2.5s ease-in-out infinite;
                position: relative;
             ">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div style="flex: 1; min-width: 0;">
                <p style="font-size: 9px; font-weight: 900; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 1.5px; margin: 0 0 3px;">📢 URGENT</p>
                <p style="font-size: 13px; font-weight: 800; color: white; margin: 0 0 2px; line-height: 1.3;">{{ $urgentAnnouncements->first()->title }}</p>
                <p style="font-size: 11px; color: rgba(255,255,255,0.8); margin: 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $urgentAnnouncements->first()->content }}</p>
            </div>
        </div>
        @endif

        {{-- ─── ROLLING CARD ─────────────────────────────────────────────── --}}
        @if($hasRolling)
        <div id="ann-rolling-card"
             style="
                background: rgba(255,255,255,0.98);
                border-radius: 16px; padding: 14px 16px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.14);
                border: 1px solid rgba(99,102,241,0.15);
                overflow: hidden;
             ">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(135deg, #6366f1, #4338ca); display: flex; align-items: center; justify-content: center;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <p style="font-size: 9px; font-weight: 900; color: #6366f1; text-transform: uppercase; letter-spacing: 1.5px; margin: 0;">Info & Pengumuman</p>
                </div>
                <a href="{{ route('information.index') }}" style="font-size: 9px; font-weight: 700; color: #6366f1; text-decoration: none; padding: 3px 8px; border-radius: 6px; background: rgba(99,102,241,0.08);">Lihat Semua →</a>
            </div>

            <div style="overflow: hidden; position: relative; min-height: 58px;">
                @foreach($rollingAnnouncements as $idx => $ann)
                <div class="ann-slide" data-index="{{ $idx }}" style="display: {{ $idx === 0 ? 'block' : 'none' }};">
                    <p style="font-size: 12px; font-weight: 800; color: #111827; margin: 0 0 2px; line-height: 1.3;">{{ $ann->title }}</p>
                    <p style="font-size: 11px; color: #6b7280; margin: 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $ann->content }}</p>
                </div>
                @endforeach
            </div>

            @if($rollingAnnouncements->count() > 1)
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px;">
                <div style="display: flex; gap: 5px;" id="ann-dots">
                    @foreach($rollingAnnouncements as $idx => $ann)
                    <span class="ann-dot" onclick="annGoTo({{ $idx }})" style="width:{{ $idx === 0 ? '16px' : '5px' }}; height:5px; border-radius:3px; background:{{ $idx === 0 ? '#6366f1' : 'rgba(99,102,241,0.2)' }}; cursor:pointer; transition:all 0.3s;"></span>
                    @endforeach
                </div>
                <div style="display: flex; gap: 4px;">
                    <button onclick="annPrev()" style="width:20px; height:20px; border-radius:5px; background:rgba(99,102,241,0.05); border:none; cursor:pointer; color:#6366f1;"><svg width="8" height="8" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg></button>
                    <button onclick="annNext()" style="width:20px; height:20px; border-radius:5px; background:rgba(99,102,241,0.05); border:none; cursor:pointer; color:#6366f1;"><svg width="8" height="8" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg></button>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

<style>
@keyframes ann-pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 8px 24px rgba(99,102,241,0.4); }
    50%       { transform: scale(1.05); box-shadow: 0 12px 32px rgba(99,102,241,0.6); }
}
@keyframes ann-slidein {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* State: Minimized */
#ann-overlay.ann-minimized {
    width: 52px !important;
    height: 52px !important;
    background: transparent !important;
    box-shadow: none !important;
}
#ann-overlay.ann-minimized #ann-content-wrapper {
    display: none !important;
}
#ann-overlay.ann-minimized #ann-trigger {
    display: flex !important;
}

@media (max-width: 767px) {
    #ann-overlay {
        right: 1rem !important;
        bottom: 1.5rem !important;
    }
}
</style>

<script>
(function () {
    const slides    = document.querySelectorAll('.ann-slide');
    const dots      = document.querySelectorAll('.ann-dot');
    const overlay   = document.getElementById('ann-overlay');
    const total     = slides.length;
    let current     = 0;
    let autoTimer   = null;

    // Toggle logic
    window.toggleAnn = function(minimize) {
        if (minimize) {
            overlay.classList.add('ann-minimized');
        } else {
            overlay.classList.remove('ann-minimized');
        }
    };

    // Auto-minimize on mobile
    if (window.innerWidth < 768) {
        toggleAnn(true);
    } else {
        toggleAnn(false);
    }

    if (total <= 1) return;

    function show(index) {
        slides.forEach((s, i) => s.style.display = 'none');
        dots.forEach((d, i) => {
            d.style.width      = i === index ? '16px' : '5px';
            d.style.background = i === index ? '#6366f1' : 'rgba(99,102,241,0.2)';
        });
        slides[index].style.display = 'block';
        slides[index].style.animation = 'none';
        void slides[index].offsetWidth;
        slides[index].style.animation = 'ann-slidein 0.4s ease';
        current = index;
    }

    window.annNext = () => { clearInterval(autoTimer); show((current + 1) % total); startAuto(); };
    window.annPrev = () => { clearInterval(autoTimer); show((current - 1 + total) % total); startAuto(); };
    window.annGoTo = (i) => { clearInterval(autoTimer); show(i); startAuto(); };

    function startAuto() { autoTimer = setInterval(() => show((current + 1) % total), 5000); }
    startAuto();
})();
</script>
@endif

</body>
</html>