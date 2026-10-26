@extends('election.booth.layout')

@section('title', 'Lembar Suara — ' . $period->title)

@section('container_width', 'max-w-4xl')

@section('header_extra')
    {{-- Info Pemilih & Timer Sesi (3 Menit) --}}
    <div class="flex items-center gap-4 text-xs"
         x-data="{
             timeLeft: 180,
             init() {
                 let timer = setInterval(() => {
                     this.timeLeft--;
                     if (this.timeLeft <= 0) {
                         clearInterval(timer);
                         alert('Waktu sesi bilik suara habis (3 menit). Silakan masukkan kembali kredensial Anda.');
                         window.location.href = '{{ route('election.index') }}';
                     }
                 }, 1000);
             },
             formatTime(seconds) {
                 const m = Math.floor(seconds / 60);
                 const s = seconds % 60;
                 return m + ':' + (s < 10 ? '0' : '') + s;
             }
         }">
        <div class="hidden sm:block text-right">
            <div class="font-bold text-white">{{ $voter->student_name }}</div>
            <div class="text-slate-400">{{ $voter->class_name === 'GURU & STAF' ? 'ID' : 'NIS' }}: {{ $voter->nis }} &bull; {{ $voter->class_name ?? '-' }}</div>
        </div>
        <div class="px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 font-mono font-bold flex items-center gap-1.5 shadow">
            <span>⏱️</span>
            <span x-text="formatTime(timeLeft)">3:00</span>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6"
     x-data="{
         selectedId: null,
         selectedLabel: '',
         showConfirmModal: false,
         isSubmitting: false,

         selectCandidate(id, label) {
             this.selectedId = id;
             this.selectedLabel = label;
         },

         submitVote() {
             this.isSubmitting = true;
             this.$refs.voteForm.submit();
         }
     }">

    {{-- Title & Guide --}}
    <div class="text-center">
        <h2 class="text-2xl font-black text-white tracking-tight">KARTU SUARA DIGITAL</h2>
        <p class="text-sm text-slate-300 mt-1">
            Klik pada salah satu kartu pasangan calon untuk menentukan pilihan Anda, lalu tekan tombol konfirmasi di bagian bawah.
        </p>
    </div>

    {{-- Candidates Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ count($candidates) > 2 ? '3' : '2' }} gap-5">
        @foreach($candidates as $candidate)
            <div @click="selectCandidate({{ $candidate->id }}, 'Paslon {{ $candidate->number }} — {{ addslashes($candidate->chairman_name) }} & {{ addslashes($candidate->vice_name) }}')"
                 class="cursor-pointer group relative rounded-2xl border-4 transition-all duration-200 overflow-hidden shadow-xl flex flex-col justify-between select-none"
                 :class="selectedId === {{ $candidate->id }}
                     ? 'border-emerald-500 bg-slate-800 ring-4 ring-emerald-500/50 shadow-2xl shadow-emerald-500/20 scale-[1.02]'
                     : 'border-slate-700 bg-slate-800/90 hover:border-indigo-400 hover:bg-slate-800'">

                {{-- Header Paslon: Nomor Urut & Status --}}
                <div class="p-4 bg-slate-900/60 border-b border-slate-700/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full font-black text-base flex items-center justify-center shadow transition-colors"
                             :class="selectedId === {{ $candidate->id }} ? 'bg-emerald-500 text-slate-950 font-black' : 'bg-indigo-600 text-white'">
                            {{ $candidate->number }}
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Paslon Nomor {{ $candidate->number }}</span>
                    </div>

                    <div>
                        <span x-cloak x-show="selectedId === {{ $candidate->id }}"
                              class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-500 text-slate-950 font-black text-xs shadow-md animate-pulse">
                            ✓ TERPILIH
                        </span>
                        <span x-cloak x-show="selectedId !== {{ $candidate->id }}"
                              class="text-xs text-slate-400 font-medium group-hover:text-indigo-300 transition-colors">
                            Klik untuk memilih
                        </span>
                    </div>
                </div>

                {{-- Foto Paslon --}}
                <div class="p-4 flex flex-col items-center">
                    @if($candidate->hasCustomPhoto())
                        {{-- Foto Khusus Paslon Bersama (Upload) --}}
                        <div class="text-center">
                            <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->label }}"
                                 class="w-48 h-36 rounded-2xl object-cover border-2 shadow-md group-hover:scale-105 transition-transform duration-300"
                                 :class="selectedId === {{ $candidate->id }} ? 'border-emerald-500' : 'border-slate-700'">
                        </div>
                    @else
                        {{-- Foto Otomatis: Ketua & Wakil Berdampingan --}}
                        <div class="flex items-center justify-center gap-3 sm:gap-4 my-1">
                            {{-- Foto Ketua --}}
                            <div class="text-center">
                                <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->chairman_name }}"
                                     class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-2 shadow-md group-hover:scale-105 transition-transform duration-300 bg-slate-900"
                                     :class="selectedId === {{ $candidate->id }} ? 'border-emerald-500' : 'border-indigo-500'">
                                <span class="inline-block text-[10px] font-bold text-indigo-400 mt-1 uppercase tracking-wider">Ketua</span>
                            </div>
                            <span class="text-xl font-black text-slate-500 mb-4">&amp;</span>
                            {{-- Foto Wakil --}}
                            <div class="text-center">
                                <img src="{{ $candidate->vice_photo_url }}" alt="{{ $candidate->vice_name }}"
                                     class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-2 shadow-md group-hover:scale-105 transition-transform duration-300 bg-slate-900"
                                     :class="selectedId === {{ $candidate->id }} ? 'border-emerald-500' : 'border-emerald-500'">
                                <span class="inline-block text-[10px] font-bold text-emerald-400 mt-1 uppercase tracking-wider">Wakil</span>
                            </div>
                        </div>
                    @endif

                    {{-- Nama Ketua & Wakil --}}
                    <div class="mt-4 text-center">
                        <div class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Calon Ketua OSIS</div>
                        <div class="text-base font-bold text-white mt-0.5">{{ $candidate->chairman_name }}</div>
                        <div class="text-xs text-slate-400">Kelas {{ $candidate->chairman_class ?? '-' }}</div>

                        <div class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mt-3">Calon Wakil Ketua OSIS</div>
                        <div class="text-base font-bold text-white mt-0.5">{{ $candidate->vice_name }}</div>
                        <div class="text-xs text-slate-400">Kelas {{ $candidate->vice_class ?? '-' }}</div>
                    </div>

                    {{-- Visi & Misi --}}
                    @if($candidate->vision || $candidate->mission)
                        <div class="mt-4 w-full text-left p-3 rounded-xl bg-slate-900/50 border border-slate-700/50 text-xs space-y-1.5">
                            @if($candidate->vision)
                                <div>
                                    <span class="font-bold text-slate-300">Visi:</span>
                                    <p class="text-slate-400 line-clamp-2 mt-0.5">{{ $candidate->vision }}</p>
                                </div>
                            @endif
                            @if($candidate->mission)
                                <div>
                                    <span class="font-bold text-slate-300">Misi:</span>
                                    <p class="text-slate-400 line-clamp-3 mt-0.5">{{ $candidate->mission }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Bottom Select Action Bar --}}
                <div class="p-3 border-t border-slate-700/60 text-center transition-all duration-200"
                     :class="selectedId === {{ $candidate->id }} ? 'bg-emerald-600 text-white font-extrabold shadow-inner' : 'bg-slate-900/50 text-slate-400 group-hover:bg-slate-900 group-hover:text-slate-200'">
                    <div x-cloak x-show="selectedId === {{ $candidate->id }}" class="flex items-center justify-center gap-2 text-sm font-black text-white tracking-wide">
                        <span class="w-5 h-5 rounded-full bg-white text-emerald-600 flex items-center justify-center text-xs font-black shadow">✓</span>
                        <span>PASLON INI SUDAH ANDA PILIH</span>
                    </div>
                    <div x-cloak x-show="selectedId !== {{ $candidate->id }}" class="text-xs font-semibold py-0.5">
                        👉 Klik di sini untuk memilih Paslon {{ $candidate->number }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Bottom Floating Bar --}}
    <div class="sticky bottom-4 z-20 p-4 rounded-2xl bg-slate-950/95 border-2 border-slate-700 backdrop-blur shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-center sm:text-left">
            <div class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Pilihan Anda Saat Ini:</div>
            <div class="text-sm sm:text-base font-extrabold mt-0.5">
                <span x-cloak x-show="!selectedId" class="text-slate-400 italic font-normal">
                    ⚠️ Belum memilih (silakan klik salah satu kartu paslon di atas)
                </span>
                <span x-cloak x-show="selectedId" class="text-emerald-400 flex items-center gap-1.5">
                    <span>✓</span>
                    <span x-text="selectedLabel"></span>
                </span>
            </div>
        </div>

        <button type="button"
                :disabled="!selectedId"
                @click="if(selectedId) showConfirmModal = true"
                class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-black text-sm transition-all duration-200 shadow-xl flex items-center justify-center gap-2"
                :class="selectedId
                    ? 'bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-slate-950 shadow-emerald-500/30 cursor-pointer animate-pulse'
                    : 'bg-slate-800 text-slate-500 border border-slate-700 cursor-not-allowed opacity-60'">
            <span x-cloak x-show="!selectedId">Pilih Paslon Terlebih Dahulu</span>
            <span x-cloak x-show="selectedId">Lanjut Konfirmasi Pilihan &rarr;</span>
        </button>
    </div>

    {{-- Form Submit Hidden --}}
    <form x-ref="voteForm" method="POST" action="{{ route('election.pilih') }}" class="hidden">
        @csrf
        <input type="hidden" name="candidate_id" :value="selectedId">
    </form>

    {{-- Modal Konfirmasi --}}
    <div x-cloak x-show="showConfirmModal" style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-transition>
        <div class="bg-slate-800 border-2 border-slate-700 rounded-2xl max-w-md w-full p-6 text-center shadow-2xl"
             @click.away="if(!isSubmitting) showConfirmModal = false">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 flex items-center justify-center text-3xl">
                🗳️
            </div>
            <h3 class="text-xl font-black text-white">Konfirmasi Suara Anda</h3>
            <p class="text-xs text-slate-400 mt-1">Pastikan pilihan Anda sudah benar sebelum dikirim ke kotak suara digital.</p>

            <div class="my-5 p-4 rounded-xl bg-slate-900 border border-slate-700 text-left">
                <span class="text-xs text-slate-500 block uppercase tracking-wider mb-1 font-semibold">Pilihan Anda:</span>
                <span class="text-base font-black text-emerald-400" x-text="selectedLabel"></span>
            </div>

            <p class="text-xs text-amber-300 bg-amber-500/10 border border-amber-500/20 rounded-lg p-2.5 mb-5 text-left">
                ⚠️ <strong>PENTING:</strong> Pilihan tidak dapat diubah setelah tombol kirim ditekan. Sesi bilik suara Anda akan langsung berakhir.
            </p>

            <div class="flex gap-3">
                <button type="button"
                        :disabled="isSubmitting"
                        @click="showConfirmModal = false"
                        class="w-1/2 py-3 rounded-xl border border-slate-600 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                    Ubah Pilihan
                </button>
                <button type="button"
                        :disabled="isSubmitting"
                        @click="submitVote()"
                        class="w-1/2 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 disabled:bg-slate-700 text-slate-950 font-black text-xs transition shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-1.5 cursor-pointer">
                    <span x-cloak x-show="!isSubmitting">✓ Ya, Kirim Suara</span>
                    <span x-cloak x-show="isSubmitting">Mengirim suara...</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
