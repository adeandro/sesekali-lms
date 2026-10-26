@extends('election.booth.layout')

@section('title', $step === 'pin' ? 'Kunci Bilik Suara' : 'Masuk Bilik Suara')

@section('content')

@if($step === 'pin')
    {{-- STEP 1: FORM PIN BILIK OLEH PANITIA --}}
    <div class="bg-slate-800/90 border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8 backdrop-blur">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-3xl">
                🔐
            </div>
            <h2 class="text-xl font-bold text-white">Bilik Terkunci</h2>
            <p class="text-xs text-slate-400 mt-1">
                Panitia, silakan masukkan PIN Bilik Suara untuk mengaktifkan komputer ini.
            </p>
        </div>

        <form method="POST" action="{{ route('election.pin.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2 text-center">
                    PIN Bilik Suara
                </label>
                <input type="password" name="booth_pin" required autofocus maxlength="8"
                       autocomplete="off"
                       placeholder="••••••"
                       class="w-full px-4 py-3 text-center text-2xl font-mono tracking-[0.5em] bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white font-semibold rounded-xl transition text-sm shadow-lg shadow-indigo-600/30">
                Buka Kunci Komputer Bilik
            </button>
        </form>

        <p class="mt-4 text-center text-xs text-slate-400">
            PIN bilik diatur oleh panitia di panel admin Pilketos.
        </p>
    </div>

@elseif($step === 'verify')
    {{-- STEP 2: FORM NIS + TOKEN OLEH SISWA --}}
    @if($period->status !== 'open')
        <div class="bg-slate-800/90 border border-slate-700 rounded-2xl shadow-2xl p-8 text-center backdrop-blur">
            <div class="text-5xl mb-3">⏳</div>
            <h2 class="text-xl font-bold text-white">Bilik Siap &bull; Belum Dibuka</h2>
            <p class="text-sm text-slate-300 mt-2">
                Komputer bilik suara telah aktif, namun pemilihan belum dibuka secara resmi oleh panitia.
            </p>
            <p class="text-xs text-slate-400 mt-4">
                Silakan tunggu aba-aba dari panitia sebelum memberikan suara.
            </p>
        </div>
    @else
        <div class="bg-slate-800/90 border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8 backdrop-blur">
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-3xl">
                    🎫
                </div>
                <h2 class="text-xl font-bold text-white">Silakan Masuk Pemilih</h2>
                <p class="text-xs text-slate-400 mt-1">
                    Ketikkan NIS dan Kode Token yang tertera pada kartu kertas Anda.
                </p>
            </div>

            <form method="POST" action="{{ route('election.verify') }}" class="space-y-4"
                  x-data="{
                      token: '',
                      formatTokenInput(e) {
                          // Bersihkan dari spasi/strip lalu format ABCD-EFGH
                          let val = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                          if (val.length > 4) {
                              val = val.substring(0, 4) + '-' + val.substring(4, 8);
                          }
                          this.token = val.substring(0, 9);
                      }
                  }">
                @csrf

                {{-- Input NIS --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                        Nomor Induk Siswa (NIS)
                    </label>
                    <input type="text" name="nis" required autofocus
                           value="{{ old('nis') }}"
                           placeholder="Contoh: 20241001"
                           autocomplete="off"
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                </div>

                {{-- Input Token --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                        Kode Token Pemilih
                    </label>
                    <input type="text" name="token" required
                           x-model="token"
                           @input="formatTokenInput($event)"
                           placeholder="ABCD-EFGH"
                           maxlength="9"
                           autocomplete="off"
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-center text-xl tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase transition">
                    <p class="text-[11px] text-slate-400 mt-1 text-center">
                        8 karakter kode pada kartu fisik Anda (huruf besar/angka)
                    </p>
                </div>

                <button type="submit"
                        class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white font-bold rounded-xl transition text-sm shadow-lg shadow-indigo-600/30 mt-2">
                    Buka Lembar Suara &rarr;
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-700/60 text-center">
                <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Komputer Bilik Aktif &bull; Sesi Terverifikasi
                </span>
            </div>
        </div>
    @endif
@endif

@endsection
