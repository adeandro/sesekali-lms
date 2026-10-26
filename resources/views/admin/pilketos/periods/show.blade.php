@extends('layouts.app')

@section('title', 'Kelola Periode — ' . $period->title)

@section('content')
<div class="space-y-6" x-data="{
    showPinModal: false,
    newPin: '',
    generatePin() {
        this.newPin = Math.floor(100000 + Math.random() * 900000).toString();
    },
    copyUrl(url) {
        navigator.clipboard.writeText(url);
        alert('URL bilik berhasil disalin: ' + url);
    }
}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pilketos.periods.index') }}"
               class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $period->title }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Dibuat oleh {{ $period->createdBy?->name ?? 'Admin' }} &middot; {{ $period->created_at->format('d M Y') }}
                    @if($period->event_date)
                        &middot; Pelaksanaan: <strong>{{ $period->event_date->format('d M Y') }}</strong>
                    @endif
                </p>
            </div>
        </div>

        {{-- Status Badge --}}
        @php
            $colorMap = [
                'draft'  => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                'ready'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                'open'   => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                'closed' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
            ];
            $color = $colorMap[$period->status] ?? 'bg-gray-100 text-gray-700';
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $color }}">
            {{ $period->status_label }}
        </span>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300 text-sm">
            {{ session('error') }}
            @if(session('confirm_force'))
                <form method="POST" action="{{ route('admin.pilketos.periods.transition', $period) }}" class="mt-3 no-loading">
                    @csrf
                    <input type="hidden" name="to_status" value="{{ session('confirm_to_status') }}">
                    <input type="hidden" name="force" value="1">
                    <button type="submit" class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded shadow transition">
                        Tetap Buka Pemilihan (Force Open)
                    </button>
                </form>
            @endif
        </div>
    @endif

    {{-- Stats Row (Partisipasi - TANPA data per paslon) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 text-center">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $period->candidates->count() }}</div>
            <div class="text-xs text-slate-500 mt-1">Pasangan Calon</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $participation['total'] }}</div>
            <div class="text-xs text-slate-500 mt-1">Total Pemilih</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $participation['voted'] }}</div>
            <div class="text-xs text-slate-500 mt-1">Sudah Memilih</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 text-center">
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $participation['percentage'] }}%</div>
            <div class="text-xs text-slate-500 mt-1">Partisipasi</div>
        </div>
    </div>

    {{-- Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Kontrol Periode & Info Bilik --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- 1. Kontrol Status Periode --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
                <h2 class="font-semibold text-slate-700 dark:text-slate-300 text-sm">Status & Aksi</h2>

                @if($period->status === 'draft')
                    <form method="POST" action="{{ route('admin.pilketos.periods.transition', $period) }}" class="no-loading">
                        @csrf
                        <input type="hidden" name="to_status" value="ready">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                            ✅ Tandai Siap
                        </button>
                    </form>
                    <a href="{{ route('admin.pilketos.periods.edit', $period) }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        ✏️ Edit Periode
                    </a>

                @elseif($period->status === 'ready')
                    <form method="POST" action="{{ route('admin.pilketos.periods.transition', $period) }}" class="no-loading">
                        @csrf
                        <input type="hidden" name="to_status" value="open">
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                            🟢 Buka Pemilihan Sekarang
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.pilketos.periods.transition', $period) }}" class="no-loading">
                        @csrf
                        <input type="hidden" name="to_status" value="draft">
                        <button type="submit" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            ↩️ Kembalikan ke Draft
                        </button>
                    </form>
                    <a href="{{ route('admin.pilketos.periods.edit', $period) }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        ✏️ Edit Periode
                    </a>

                @elseif($period->status === 'open')
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-xs text-green-700 dark:text-green-300 text-center font-medium">
                        🟢 Pemilihan sedang berlangsung
                    </div>
                    <form method="POST" action="{{ route('admin.pilketos.periods.transition', $period) }}" class="no-loading">
                        @csrf
                        <input type="hidden" name="to_status" value="closed">
                        <button type="submit" onclick="return confirm('Tutup pemilihan sekarang? Setelah ditutup, bilik suara akan berhenti menerima suara dan hasil dapat dihitung.')"
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                            🔴 Tutup Pemilihan
                        </button>
                    </form>

                @elseif($period->status === 'closed')
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-xs text-red-700 dark:text-red-300 text-center font-medium">
                        🔴 Pemilihan telah ditutup
                    </div>
                    <a href="{{ route('admin.pilketos.periods.results', $period) }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                        📊 Lihat Hasil & Rekonsiliasi
                    </a>
                    <form method="POST" action="{{ route('admin.pilketos.periods.transition', $period) }}" class="no-loading">
                        @csrf
                        <input type="hidden" name="to_status" value="open">
                        <button type="submit" onclick="return confirm('Buka kembali pemilihan? Bilik suara akan dapat digunakan kembali.')"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            🔓 Buka Kembali Pemilihan
                        </button>
                    </form>
                @endif

                {{-- Hapus Periode (Hanya draft/closed) --}}
                @if(in_array($period->status, ['draft', 'closed']))
                    <form method="POST" action="{{ route('admin.pilketos.periods.destroy', $period) }}" class="pt-2 border-t border-slate-100 dark:border-slate-700 no-loading">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Hapus periode ini? Tindakan ini tidak dapat dibatalkan.')"
                                class="w-full px-3 py-1.5 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition">
                            🗑️ Hapus Periode
                        </button>
                    </form>
                @endif
            </div>

            {{-- 2. Info & Pengaturan Bilik Suara --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
                <h2 class="font-semibold text-slate-700 dark:text-slate-300 text-sm">🖥️ Bilik Suara</h2>

                <div>
                    <label class="text-xs text-slate-500 dark:text-slate-400 block mb-1">URL Bilik Suara Publik</label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $boothUrl }}"
                               class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded font-mono text-slate-700 dark:text-slate-300">
                        <button type="button" @click="copyUrl('{{ $boothUrl }}')"
                                class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs rounded transition whitespace-nowrap">
                            Salin
                        </button>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <span class="text-slate-500">PIN Bilik:</span>
                        @if($period->hasPinSet())
                            <span class="text-green-600 dark:text-green-400 font-medium">✓ Terpasang (Versi #{{ $period->booth_session_version }})</span>
                        @else
                            <span class="text-red-500 font-medium">✗ Belum diatur</span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="showPinModal = true"
                                class="px-3 py-1.5 text-xs font-medium border border-indigo-300 dark:border-indigo-600 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded transition text-center">
                            🔑 Ganti PIN
                        </button>
                        <form method="POST" action="{{ route('admin.pilketos.periods.revoke-sessions', $period) }}" class="no-loading">
                            @csrf
                            <button type="submit" onclick="return confirm('Cabut semua sesi bilik suara sekarang? Semua komputer bilik akan diminta memasukkan PIN ulang.')"
                                    class="w-full px-3 py-1.5 text-xs font-medium border border-amber-300 dark:border-amber-600 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded transition text-center">
                                🚫 Cabut Sesi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2.5 Kartu Publikasi --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-4"
                 x-data="{ confirmForcePublish: false }">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="font-semibold text-slate-700 dark:text-slate-300 text-sm">📢 Publikasi</h2>
                    <span class="text-[11px] text-slate-400">Akses Publik & Layar</span>
                </div>

                {{-- A. Publikasi Kandidat --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-800 dark:text-white">Profil Kandidat</div>
                            <div class="text-[11px] text-slate-500">Grid profil paslon & mode slide</div>
                        </div>
                        @if($period->candidates_published)
                            <form method="POST" action="{{ route('admin.pilketos.periods.publish-candidates', $period) }}" class="no-loading">
                                @csrf
                                <input type="hidden" name="on" value="0">
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 hover:bg-red-200 transition">
                                    Tarik Publikasi
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.pilketos.periods.publish-candidates', $period) }}" class="no-loading">
                                @csrf
                                <input type="hidden" name="on" value="1">
                                <button type="submit"
                                        {{ in_array($period->status, ['ready', 'open', 'closed']) && $period->candidates->count() >= 1 ? '' : 'disabled' }}
                                        class="px-2.5 py-1 text-xs font-medium rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:text-slate-400 dark:disabled:bg-slate-700 text-white transition">
                                    Publikasikan
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($period->candidates_published)
                        <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-[11px] text-green-700 dark:text-green-300 flex items-center justify-between">
                            <span>✓ Sedang dipublikasikan</span>
                            <span class="font-mono text-[10px]">/pilketos/kandidat</span>
                        </div>
                    @else
                        <div class="text-[11px] text-slate-400 italic">
                            @if($period->status === 'draft')
                                Kandidat belum dapat dipublikasikan (status Draft).
                            @elseif($period->candidates->count() < 1)
                                Daftarkan minimal 1 paslon untuk publikasi.
                            @else
                                Belum dipublikasikan ke publik.
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center gap-2 pt-1">
                        <input type="text" readonly value="{{ url('/pilketos/kandidat') }}"
                               class="w-full px-2 py-1 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded font-mono text-slate-700 dark:text-slate-300">
                        <button type="button" @click="copyUrl('{{ url('/pilketos/kandidat') }}')"
                                class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs rounded transition whitespace-nowrap">
                            Salin
                        </button>
                    </div>

                    <a href="{{ url('/pilketos/kandidat?tampilan=slide') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-1.5 w-full py-1.5 px-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold hover:bg-indigo-100 transition">
                        📽️ Buka Tampilan Proyektor (Slide)
                    </a>
                </div>

                {{-- B. Publikasi Hasil --}}
                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-800 dark:text-white">Hasil Pemilihan</div>
                            <div class="text-[11px] text-slate-500">Papan pengumuman hasil publik</div>
                        </div>
                        @if($period->results_published)
                            <form method="POST" action="{{ route('admin.pilketos.periods.publish-results', $period) }}" class="no-loading">
                                @csrf
                                <input type="hidden" name="on" value="0">
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 hover:bg-red-200 transition">
                                    Tarik Hasil
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($period->results_published)
                        <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-[11px] text-green-700 dark:text-green-300">
                            <div>✓ Hasil sedang dipublikasikan ke publik</div>
                            @if($period->results_published_at)
                                <div class="text-[10px] text-green-600 dark:text-green-400 mt-0.5">Sejak {{ $period->results_published_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </div>
                    @else
                        {{-- Syarat Publikasi --}}
                        @if($period->status !== 'closed')
                            <div class="p-2 rounded-lg bg-slate-100 dark:bg-slate-900 text-[11px] text-slate-500">
                                🔒 Hasil hanya dapat dipublikasikan setelah pemilihan ditutup (Closed).
                            </div>
                        @elseif($period->collected_cards_count === null)
                            <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-700 dark:text-amber-300">
                                ⚠️ Isi <strong>Jumlah Kertas Fisik Terkumpul</strong> di menu Hasil sebelum mempublikasikan.
                            </div>
                        @else
                            {{-- Closed & Kertas Terkumpul Sudah Diisi --}}
                            @php
                                $hasMismatch = ! ($consistency['match'] ?? true) || (($consistency['diff'] ?? 0) !== 0);
                            @endphp

                            @if($hasMismatch)
                                <div class="p-2.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-[11px] text-red-700 dark:text-red-300 space-y-1.5">
                                    <div class="font-bold">⚠️ Perhatian: Terdapat Selisih Rekonsiliasi!</div>
                                    <div>Suara Digital: <strong>{{ $consistency['votes_count'] }}</strong> &bull; Kertas Fisik: <strong>{{ $consistency['collected_cards_count'] }}</strong> (Selisih: {{ abs($consistency['diff']) }}).</div>
                                    <label class="flex items-center gap-2 cursor-pointer pt-1 font-semibold text-red-800 dark:text-red-200">
                                        <input type="checkbox" x-model="confirmForcePublish" class="rounded text-red-600 focus:ring-red-500">
                                        <span>Saya mengerti selisih ini</span>
                                    </label>
                                </div>

                                <form method="POST" action="{{ route('admin.pilketos.periods.publish-results', $period) }}" class="no-loading">
                                    @csrf
                                    <input type="hidden" name="on" value="1">
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" :disabled="!confirmForcePublish"
                                            class="w-full py-2 px-3 text-xs font-bold rounded-lg bg-red-600 hover:bg-red-700 disabled:bg-slate-200 disabled:text-slate-400 dark:disabled:bg-slate-700 text-white transition shadow-sm">
                                        Tetap Publikasikan Hasil (Force)
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.pilketos.periods.publish-results', $period) }}" class="no-loading">
                                    @csrf
                                    <input type="hidden" name="on" value="1">
                                    <button type="submit"
                                            class="w-full py-2 px-3 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                                        ✓ Publikasikan Hasil Sekarang
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endif

                    <div class="flex items-center gap-2 pt-1">
                        <input type="text" readonly value="{{ url('/pilketos/hasil') }}"
                               class="w-full px-2 py-1 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded font-mono text-slate-700 dark:text-slate-300">
                        <button type="button" @click="copyUrl('{{ url('/pilketos/hasil') }}')"
                                class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs rounded transition whitespace-nowrap">
                            Salin
                        </button>
                    </div>
                </div>
            </div>

            {{-- 3. Detail Periode --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-2 text-xs">
                <h2 class="font-semibold text-slate-700 dark:text-slate-300 text-sm mb-2">Ketentuan</h2>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700">
                    <span class="text-slate-500">Kelas Pemilih:</span>
                    <span class="font-medium text-slate-700 dark:text-slate-300">
                        {{ empty($period->eligible_class_ids) ? 'Semua Kelas' : count($period->eligible_class_ids) . ' Kelas Terpilih' }}
                    </span>
                </div>
                @if($period->opened_at)
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-700">
                    <span class="text-slate-500">Dibuka Pada:</span>
                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $period->opened_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                @if($period->closed_at)
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Ditutup Pada:</span>
                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $period->closed_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>

        </div>

        {{-- Right Column: Paslon & Pemilih --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Pasangan Calon (TANPA angka perolehan suara sebelum closed) --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <h2 class="font-semibold text-slate-800 dark:text-white">🧑‍💼 Pasangan Calon</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar kandidat ketua dan wakil ketua OSIS</p>
                    </div>
                    @if(in_array($period->status, ['draft', 'ready']))
                        <a href="{{ route('admin.pilketos.periods.candidates.create', $period) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                            + Tambah Paslon
                        </a>
                    @endif
                </div>

                @if($period->candidates->isEmpty())
                    <div class="p-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                        Belum ada paslon. Daftarkan minimal 1 paslon untuk dapat menandai status Siap.
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($period->candidates as $candidate)
                            <div class="p-4 flex items-start gap-4">
                                {{-- Nomor Urut Badge --}}
                                <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0 shadow">
                                    {{ $candidate->number }}
                                </div>

                                {{-- Foto Paslon --}}
                                @if($candidate->hasCustomPhoto())
                                    <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->label }}"
                                         class="w-16 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-600 flex-shrink-0">
                                @else
                                    <div class="flex items-center -space-x-3 flex-shrink-0">
                                        <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->chairman_name }}" title="Ketua: {{ $candidate->chairman_name }}"
                                             class="w-14 h-14 rounded-xl object-cover border-2 border-indigo-500 shadow bg-white dark:bg-slate-800">
                                        <img src="{{ $candidate->vice_photo_url }}" alt="{{ $candidate->vice_name }}" title="Wakil: {{ $candidate->vice_name }}"
                                             class="w-14 h-14 rounded-xl object-cover border-2 border-emerald-500 shadow bg-white dark:bg-slate-800">
                                    </div>
                                @endif

                                {{-- Info Paslon --}}
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-800 dark:text-white text-sm">
                                        {{ $candidate->chairman_name }} <span class="text-xs font-normal text-slate-500">({{ $candidate->chairman_class ?? '-' }})</span>
                                        &bull;
                                        {{ $candidate->vice_name }} <span class="text-xs font-normal text-slate-500">({{ $candidate->vice_class ?? '-' }})</span>
                                    </div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5">
                                        Calon Ketua &amp; Wakil Ketua
                                    </div>

                                    @if($candidate->vision)
                                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                                            <strong>Visi:</strong> {{ $candidate->vision }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Action --}}
                                @if(in_array($period->status, ['draft', 'ready']))
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <a href="{{ route('admin.pilketos.periods.candidates.edit', [$period, $candidate]) }}"
                                           class="p-1.5 text-slate-400 hover:text-indigo-600 transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.pilketos.periods.candidates.destroy', [$period, $candidate]) }}" class="no-loading">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus paslon nomor urut {{ $candidate->number }}?')"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 2. Daftar Pemilih & Cetak Kartu per Kelas --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <h2 class="font-semibold text-slate-800 dark:text-white">📋 Pemilih &amp; Kartu per Kelas</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Cetak kartu kredensial (NIS + TOKEN) per kelas</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(in_array($period->status, ['draft', 'ready']))
                            <form method="POST" action="{{ route('admin.pilketos.periods.generate-voters', $period) }}" class="no-loading">
                                @csrf
                                <button type="submit" onclick="return confirm('Generate / sinkronkan pemilih dari siswa aktif?')"
                                        class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-lg transition">
                                    👥 Generate / Sinkron Pemilih
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.pilketos.periods.voters.index', $period) }}"
                           class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            Semua Pemilih →
                        </a>
                    </div>
                </div>

                @if($voterSummary->isEmpty())
                    <div class="p-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                        Belum ada data pemilih. Klik "Generate / Sinkron Pemilih" untuk memasukkan siswa aktif.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-700">
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold">Kelas</th>
                                    <th class="px-4 py-2.5 font-semibold text-center">Total Siswa</th>
                                    <th class="px-4 py-2.5 font-semibold text-center">Kartu Dicetak</th>
                                    <th class="px-4 py-2.5 font-semibold text-center">Sudah Memilih</th>
                                    <th class="px-4 py-2.5 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @foreach($voterSummary as $row)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                        <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-white">
                                            {{ $row->class_name ?? 'Tanpa Kelas' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center text-slate-600 dark:text-slate-400">
                                            {{ $row->total }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            @if($row->printed > 0)
                                                <span class="text-green-600 font-semibold">{{ $row->printed }}</span> / {{ $row->total }}
                                            @else
                                                <span class="text-amber-500 font-medium">Belum</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-center text-slate-600 dark:text-slate-400">
                                            {{ $row->voted }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            @if($period->status !== 'closed' && $row->class_id)
                                                <form method="POST" action="{{ route('admin.pilketos.periods.cards.class', [$period, $row->class_id]) }}" class="inline no-loading">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-medium rounded transition">
                                                        🖨️ Cetak Kartu Kelas
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- 3. Log Aktivitas --}}
            @if($logs->isNotEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                    <h2 class="font-semibold text-slate-700 dark:text-slate-300 text-sm mb-3">📋 Log Aktivitas</h2>
                    <div class="space-y-2">
                        @foreach($logs as $log)
                            <div class="flex items-start gap-3 text-xs text-slate-600 dark:text-slate-400">
                                <span class="text-slate-400 flex-shrink-0 pt-0.5">{{ $log->created_at->format('d/m H:i') }}</span>
                                <span class="font-mono bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-xs flex-shrink-0">{{ $log->action }}</span>
                                @if($log->meta)
                                    <span class="text-slate-500 truncate">{{ collect($log->meta)->map(fn($v,$k) => "$k: $v")->implode(', ') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    </div>

    {{-- Modal Ganti PIN Bilik --}}
    <div x-show="showPinModal" style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         x-transition>
        <div class="bg-white dark:bg-slate-800 rounded-xl max-w-sm w-full p-6 shadow-xl border border-slate-200 dark:border-slate-700"
             @click.away="showPinModal = false">
            <h3 class="text-base font-bold text-slate-800 dark:text-white mb-2">Atur PIN Bilik Suara</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                PIN ini dimasukkan oleh panitia di komputer bilik suara. Mengganti PIN akan otomatis mencabut semua sesi bilik yang sedang aktif.
            </p>

            <form method="POST" action="{{ route('admin.pilketos.periods.set-pin', $period) }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">PIN Baru (4-8 Digit)</label>
                    <div class="flex gap-2">
                        <input type="text" name="booth_pin" x-model="newPin" maxlength="8" required
                               placeholder="123456"
                               class="w-full px-3 py-2 text-sm font-mono tracking-widest border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-center">
                        <button type="button" @click="generatePin()"
                                class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-xs font-medium rounded-lg whitespace-nowrap">
                            🎲 Acak
                        </button>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="showPinModal = false"
                            class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                        Simpan PIN
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
