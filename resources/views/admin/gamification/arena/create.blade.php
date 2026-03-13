@extends('layouts.app')

@section('title', 'Buat Battle Room')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.gamification.arena.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-gray-700 transition mb-4">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Buat Battle Room</h1>
        <p class="text-sm text-gray-500 mt-1">Konfigurasikan pertarungan epik berikutnya.</p>
    </div>

    <form action="{{ route('admin.gamification.arena.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Room Name --}}
        <div class="bg-white rounded-[1.5rem] border border-gray-100 shadow-sm p-6 space-y-5">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-400">Identitas Room</h2>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Nama Sesi <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Grand Battle Semester Ganjil"
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" required>
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Mode Pertarungan <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['individual' => ['icon'=>'fa-user','label'=>'Individual','desc'=>'Siswa vs Siswa'], 'group' => ['icon'=>'fa-users','label'=>'Group','desc'=>'Kelompok Acak'], 'class' => ['icon'=>'fa-ship','label'=>'Fleet Mode','desc'=>'Perang Antar Kelas']] as $val => $opt)
                    <label class="cursor-pointer">
                        <input type="radio" name="mode" value="{{ $val }}" {{ old('mode', 'class') === $val ? 'checked' : '' }} class="peer sr-only" required>
                        <div class="peer-checked:ring-2 peer-checked:ring-orange-400 peer-checked:bg-orange-50 border border-gray-200 rounded-2xl p-4 text-center hover:border-orange-300 transition-all">
                            <i class="fas {{ $opt['icon'] }} text-2xl text-gray-400 peer-checked:text-orange-500 mb-2 block"></i>
                            <p class="text-xs font-black text-gray-900">{{ $opt['label'] }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $opt['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Source --}}
        <div class="bg-white rounded-[1.5rem] border border-gray-100 shadow-sm p-6 space-y-4">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-400">Sumber Soal</h2>
            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Tipe Sumber</label>
                <select name="source_type" id="source_type"
                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-400 transition bg-white">
                    <option value="exam" {{ old('source_type', 'exam') === 'exam' ? 'selected' : '' }}>📄 Dari Ujian (Exam)</option>
                </select>
            </div>
            <div id="exam-select">
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Ujian <span class="text-red-500">*</span></label>
                <select name="source_id"
                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-400 transition bg-white">
                    <option value="">— Pilih Ujian —</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ old('source_id') == $exam->id ? 'selected' : '' }}>
                        {{ $exam->title }} ({{ $exam->total_questions }} soal)
                    </option>
                    @endforeach
                </select>
                @error('source_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Settings --}}
        <div class="bg-white rounded-[1.5rem] border border-gray-100 shadow-sm p-6 space-y-5">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-400">Pengaturan Battle</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Target Juara</label>
                    <input type="number" name="winner_count" value="{{ old('winner_count', 3) }}" min="1" max="10"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-400 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Durasi (menit)</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" min="5" max="180"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-400 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                    Penalti HP per Jawaban Salah
                    <span class="ml-2 text-orange-500">-{{ old('penalty_hp', 20) }} HP</span>
                </label>
                <input type="range" name="penalty_hp" value="{{ old('penalty_hp', 20) }}" min="5" max="50" step="5"
                       class="w-full accent-orange-500" oninput="this.previousElementSibling.querySelector('span').textContent = '-' + this.value + ' HP'">
                <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                    <span>-5 HP</span><span>-50 HP</span>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                <div>
                    <p class="text-xs font-black text-gray-900">Kunci Room Saat Dimulai</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Siswa tidak bisa join setelah battle berlangsung</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="lock_on_start" value="1" {{ old('lock_on_start', '1') ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
            </div>
        </div>

        {{-- Reward Settings --}}
        <div class="bg-white rounded-[1.5rem] border border-gray-100 shadow-sm p-6 space-y-5">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-400">Pengaturan Hadiah (Reward)</h2>

            {{-- Rank 1 --}}
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-3">
                <p class="text-xs font-black text-amber-800 uppercase tracking-widest"><i class="fas fa-crown text-amber-500 mr-1"></i> Juara 1</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">EXP</label>
                        <input type="number" name="rewards[rank_1][exp]" value="{{ old('rewards.rank_1.exp', 500) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-amber-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Gold</label>
                        <input type="number" name="rewards[rank_1][gold]" value="{{ old('rewards.rank_1.gold', 1000) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-amber-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Tema Avatar</label>
                        <select name="rewards[rank_1][theme]" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-amber-400 bg-white">
                            <option value="">-- Tanpa Tema --</option>
                            @foreach($themes as $theme)
                                <option value="{{ $theme->slug }}" {{ old('rewards.rank_1.theme', 'legendary-golden') === $theme->slug ? 'selected' : '' }}>{{ $theme->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Rank 2 --}}
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <p class="text-xs font-black text-slate-600 uppercase tracking-widest"><i class="fas fa-medal text-slate-400 mr-1"></i> Juara 2</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">EXP</label>
                        <input type="number" name="rewards[rank_2][exp]" value="{{ old('rewards.rank_2.exp', 300) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-slate-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Gold</label>
                        <input type="number" name="rewards[rank_2][gold]" value="{{ old('rewards.rank_2.gold', 500) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-slate-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Tema Avatar</label>
                        <select name="rewards[rank_2][theme]" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-slate-400 bg-white">
                            <option value="">-- Tanpa Tema --</option>
                            @foreach($themes as $theme)
                                <option value="{{ $theme->slug }}" {{ old('rewards.rank_2.theme', 'elite-silver') === $theme->slug ? 'selected' : '' }}>{{ $theme->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Rank 3 --}}
            <div class="p-4 bg-orange-50 border border-orange-200 rounded-2xl space-y-3">
                <p class="text-xs font-black text-orange-800 uppercase tracking-widest"><i class="fas fa-award text-orange-500 mr-1"></i> Juara 3</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">EXP</label>
                        <input type="number" name="rewards[rank_3][exp]" value="{{ old('rewards.rank_3.exp', 200) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Gold</label>
                        <input type="number" name="rewards[rank_3][gold]" value="{{ old('rewards.rank_3.gold', 250) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Tema Avatar</label>
                        <select name="rewards[rank_3][theme]" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-orange-400 bg-white">
                            <option value="">-- Tanpa Tema --</option>
                            @foreach($themes as $theme)
                                <option value="{{ $theme->slug }}" {{ old('rewards.rank_3.theme', 'master-bronze') === $theme->slug ? 'selected' : '' }}>{{ $theme->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Participant --}}
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl space-y-3">
                <p class="text-xs font-black text-gray-600 uppercase tracking-widest"><i class="fas fa-users text-gray-400 mr-1"></i> Partisipan Finish</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">EXP</label>
                        <input type="number" name="rewards[participant][exp]" value="{{ old('rewards.participant.exp', 100) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-gray-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Gold</label>
                        <input type="number" name="rewards[participant][gold]" value="{{ old('rewards.participant.gold', 50) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-gray-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Tema Avatar</label>
                        <select name="rewards[participant][theme]" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-gray-400 bg-white">
                            <option value="">-- Tanpa Tema --</option>
                            @foreach($themes as $theme)
                                <option value="{{ $theme->slug }}" {{ old('rewards.participant.theme', 'survivor-common') === $theme->slug ? 'selected' : '' }}>{{ $theme->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            {{-- Physical Reward (Voucher) --}}
            <div class="px-4 py-5 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-2xl border border-yellow-200" x-data="{ physicalReward: {{ old('physical_reward.enabled', 0) ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-yellow-200/50">
                    <div>
                        <h3 class="text-sm font-black text-amber-800 flex items-center gap-2">
                            <i class="fas fa-ticket-alt text-amber-500"></i> Include Physical Reward
                        </h3>
                        <p class="text-[10px] text-amber-700/70 mt-0.5">Berikan voucher hadiah fisik (misal: Voucher Kantin) kepada pemenang.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="physical_reward[enabled]" value="1" class="sr-only peer" x-model="physicalReward">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                    </label>
                </div>

                <div x-show="physicalReward" x-collapse>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-amber-800 uppercase tracking-wide mb-1">Deskripsi Reward</label>
                            <input type="text" name="physical_reward[description]" value="{{ old('physical_reward.description') }}" placeholder="Contoh: Voucher Jajan Kantin Rp 5.000" class="w-full px-4 py-2.5 rounded-xl border border-yellow-300 text-sm focus:ring-amber-400 focus:border-amber-400 bg-white placeholder:text-yellow-600/40">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-amber-800 uppercase tracking-wide mb-1">Penerima Voucher</label>
                            <select name="physical_reward[eligibility]" class="w-full px-4 py-2.5 rounded-xl border border-yellow-300 text-sm focus:ring-amber-400 focus:border-amber-400 bg-white">
                                <option value="rank_1" {{ old('physical_reward.eligibility') == 'rank_1' ? 'selected' : '' }}>Hanya Juara 1 (Satu Pemenang)</option>
                                <option value="top_3" {{ old('physical_reward.eligibility') == 'top_3' ? 'selected' : '' }}>Juara 1, 2, dan 3 (Tiga Pemenang)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit"
                class="w-full py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white font-black text-sm uppercase tracking-widest rounded-2xl shadow-lg hover:shadow-orange-500/30 hover:-translate-y-0.5 transition-all duration-200">
            <i class="fas fa-rocket mr-2"></i> Buat Battle Room & Buka Lobby
        </button>
    </form>
</div>
@endsection
