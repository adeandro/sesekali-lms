@extends('layouts.app')

@section('title', ($candidate ? 'Edit Paslon' : 'Tambah Paslon') . ' — ' . $period->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.pilketos.periods.show', $period) }}"
           class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">
                {{ $candidate ? 'Edit Pasangan Calon' : 'Tambah Pasangan Calon' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $period->title }}</p>
        </div>
    </div>

    {{-- Error Flash --}}
    @if(session('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Main Form Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 sm:p-8"
         x-data="{
             searchUrl: '{{ route('admin.pilketos.candidates.search-students') }}',
             allClasses: {{ Js::from($classes) }},
             allGrades: {{ Js::from($grades) }},

             // ── KETUA ──
             chairmanGrade: '',
             chairmanClassId: '',
             chairmanSearchText: '',
             chairmanId: '{{ old('chairman_id', $candidate?->chairman_id) }}',
             chairmanName: '{{ old('chairman_name', $candidate?->chairman_name) }}',
             chairmanNis: '{{ old('chairman_nis', $candidate?->chairman?->nis) }}',
             chairmanClass: '{{ old('chairman_class', $candidate?->chairman_class) }}',
             chairmanPhoto: '{{ $candidate?->chairman?->photo_url ?? '' }}',
             chairmanStudents: [],
             loadingChairman: false,

             // ── WAKIL ──
             viceGrade: '',
             viceClassId: '',
             viceSearchText: '',
             viceId: '{{ old('vice_id', $candidate?->vice_id) }}',
             viceName: '{{ old('vice_name', $candidate?->vice_name) }}',
             viceNis: '{{ old('vice_nis', $candidate?->vice?->nis) }}',
             viceClass: '{{ old('vice_class', $candidate?->vice_class) }}',
             vicePhoto: '{{ $candidate?->vice?->photo_url ?? '' }}',
             viceStudents: [],
             loadingVice: false,

             // ── UPLOADED FOTO PASLON ──
             customPhotoPreview: '{{ $candidate?->photo_path ? '/storage/' . $candidate->photo_path : '' }}',

             init() {
                 this.fetchStudents('chairman');
                 this.fetchStudents('vice');
             },

             getFilteredClasses(grade) {
                 if (!grade) return this.allClasses;
                 return this.allClasses.filter(c => c.grade == grade);
             },

             fetchStudents(target) {
                 let grade = target === 'chairman' ? this.chairmanGrade : this.viceGrade;
                 let classId = target === 'chairman' ? this.chairmanClassId : this.viceClassId;
                 let q = target === 'chairman' ? this.chairmanSearchText : this.viceSearchText;

                 if (target === 'chairman') this.loadingChairman = true;
                 else this.loadingVice = true;

                 let url = new URL(this.searchUrl, window.location.origin);
                 if (grade) url.searchParams.append('grade', grade);
                 if (classId) url.searchParams.append('class_id', classId);
                 if (q && q.length >= 2) url.searchParams.append('q', q);

                 fetch(url)
                     .then(res => res.json())
                     .then(data => {
                         if (target === 'chairman') {
                             this.chairmanStudents = data;
                             this.loadingChairman = false;
                         } else {
                             this.viceStudents = data;
                             this.loadingVice = false;
                         }
                     })
                     .catch(() => {
                         if (target === 'chairman') this.loadingChairman = false;
                         else this.loadingVice = false;
                     });
             },

             selectChairman(id) {
                 if (!id) return;
                 let s = this.chairmanStudents.find(item => item.id == id);
                 if (s) {
                     this.chairmanId = s.id;
                     this.chairmanName = s.name;
                     this.chairmanNis = s.nis;
                     this.chairmanClass = s.class;
                     this.chairmanPhoto = s.photo_url;
                 }
             },

             selectVice(id) {
                 if (!id) return;
                 let s = this.viceStudents.find(item => item.id == id);
                 if (s) {
                     this.viceId = s.id;
                     this.viceName = s.name;
                     this.viceNis = s.nis;
                     this.viceClass = s.class;
                     this.vicePhoto = s.photo_url;
                 }
             },

             resetSelection(target) {
                 if (target === 'chairman') {
                     this.chairmanId = '';
                     this.chairmanName = '';
                     this.chairmanNis = '';
                     this.chairmanClass = '';
                     this.chairmanPhoto = '';
                 } else {
                     this.viceId = '';
                     this.viceName = '';
                     this.viceNis = '';
                     this.viceClass = '';
                     this.vicePhoto = '';
                 }
             }
         }">

        <form method="POST"
              action="{{ $candidate
                  ? route('admin.pilketos.periods.candidates.update', [$period, $candidate])
                  : route('admin.pilketos.periods.candidates.store', $period) }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @if($candidate)
                @method('PUT')
            @endif

            {{-- Nomor Urut Paslon --}}
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <label class="block text-sm font-bold text-slate-800 dark:text-white">
                        Nomor Urut Pasangan Calon <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-slate-500 mt-0.5">Nomor urut yang akan tertera pada kertas suara</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-500">No.</span>
                    <input type="number" name="number" min="1" max="99" required
                           value="{{ old('number', $candidate?->number) }}"
                           placeholder="1"
                           class="w-20 px-3 py-2 text-center text-lg font-bold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('number') border-red-500 @enderror">
                </div>
                @error('number')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- BAGIAN 1: CALON KETUA OSIS                                       --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="p-5 rounded-2xl border-2 border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/20 dark:bg-indigo-950/10 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow">1</span>
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">Calon Ketua OSIS</h2>
                    </div>
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Wajib Dipilih</span>
                </div>

                <input type="hidden" name="chairman_id" :value="chairmanId">

                {{-- Tampilan saat Siswa Belum Dipilih / Ingin Memilih --}}
                <div x-show="!chairmanId" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Filter Angkatan / Tingkat --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                1. Pilih Angkatan / Tingkat
                            </label>
                            <select x-model="chairmanGrade"
                                    @change="chairmanClassId = ''; fetchStudents('chairman')"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua Angkatan</option>
                                <template x-for="g in allGrades" :key="g">
                                    <option :value="g" x-text="'Tingkat ' + g"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Filter Kelas --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                2. Pilih Kelas
                            </label>
                            <select x-model="chairmanClassId"
                                    @change="fetchStudents('chairman')"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua Kelas</option>
                                <template x-for="c in getFilteredClasses(chairmanGrade)" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Dropdown Pilih Nama Siswa --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">
                                3. Pilih Nama Siswa <span class="text-red-500">*</span>
                            </label>
                            <span x-show="loadingChairman" class="text-[11px] text-indigo-500 animate-pulse">Memuat data siswa...</span>
                        </div>
                        <select @change="selectChairman($event.target.value); $event.target.value = ''"
                                class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 font-medium">
                            <option value="">-- Klik untuk memilih siswa --</option>
                            <template x-for="s in chairmanStudents" :key="s.id">
                                <option :value="s.id" x-text="s.name + ' (' + s.class + ' - NIS: ' + s.nis + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Opsional: Cari Nama Langsung --}}
                    <div>
                        <input type="text" x-model="chairmanSearchText"
                               @input.debounce.300ms="fetchStudents('chairman')"
                               placeholder="Atau ketik nama / NIS untuk menyaring siswa..."
                               class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Preview Kartu Siswa Terpilih (Lengkap dengan FOTO OTOMATIS) --}}
                <div x-show="chairmanId" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-indigo-200 dark:border-indigo-800/60 shadow-sm flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img :src="chairmanPhoto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(chairmanName || 'Ketua')"
                             alt=""
                             class="w-16 h-16 rounded-xl object-cover border-2 border-indigo-500 shadow flex-shrink-0 bg-slate-100">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300 mb-1">
                                Calon Ketua Terpilih
                            </span>
                            <div class="text-base font-bold text-slate-900 dark:text-white" x-text="chairmanName"></div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                NIS: <span class="font-mono font-medium" x-text="chairmanNis"></span> &bull;
                                Kelas: <span class="font-semibold" x-text="chairmanClass"></span>
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="resetSelection('chairman')"
                            class="px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 rounded-lg transition whitespace-nowrap">
                        Ganti Siswa
                    </button>
                </div>
                @error('chairman_id')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- BAGIAN 2: CALON WAKIL KETUA OSIS                                 --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="p-5 rounded-2xl border-2 border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/20 dark:bg-emerald-950/10 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-xs shadow">2</span>
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">Calon Wakil Ketua OSIS</h2>
                    </div>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Wajib Dipilih</span>
                </div>

                <input type="hidden" name="vice_id" :value="viceId">

                {{-- Tampilan saat Wakil Belum Dipilih / Ingin Memilih --}}
                <div x-show="!viceId" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Filter Angkatan / Tingkat --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                1. Pilih Angkatan / Tingkat
                            </label>
                            <select x-model="viceGrade"
                                    @change="viceClassId = ''; fetchStudents('vice')"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">Semua Angkatan</option>
                                <template x-for="g in allGrades" :key="g">
                                    <option :value="g" x-text="'Tingkat ' + g"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Filter Kelas --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                2. Pilih Kelas
                            </label>
                            <select x-model="viceClassId"
                                    @change="fetchStudents('vice')"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">Semua Kelas</option>
                                <template x-for="c in getFilteredClasses(viceGrade)" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Dropdown Pilih Nama Siswa --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">
                                3. Pilih Nama Siswa <span class="text-red-500">*</span>
                            </label>
                            <span x-show="loadingVice" class="text-[11px] text-emerald-500 animate-pulse">Memuat data siswa...</span>
                        </div>
                        <select @change="selectVice($event.target.value); $event.target.value = ''"
                                class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 font-medium">
                            <option value="">-- Klik untuk memilih siswa --</option>
                            <template x-for="s in viceStudents" :key="s.id">
                                <option :value="s.id" x-text="s.name + ' (' + s.class + ' - NIS: ' + s.nis + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Opsional: Cari Nama Langsung --}}
                    <div>
                        <input type="text" x-model="viceSearchText"
                               @input.debounce.300ms="fetchStudents('vice')"
                               placeholder="Atau ketik nama / NIS untuk menyaring siswa..."
                               class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                {{-- Preview Kartu Wakil Terpilih (Lengkap dengan FOTO OTOMATIS) --}}
                <div x-show="viceId" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800/60 shadow-sm flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img :src="vicePhoto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(viceName || 'Wakil')"
                             alt=""
                             class="w-16 h-16 rounded-xl object-cover border-2 border-emerald-500 shadow flex-shrink-0 bg-slate-100">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 mb-1">
                                Calon Wakil Terpilih
                            </span>
                            <div class="text-base font-bold text-slate-900 dark:text-white" x-text="viceName"></div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                NIS: <span class="font-mono font-medium" x-text="viceNis"></span> &bull;
                                Kelas: <span class="font-semibold" x-text="viceClass"></span>
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="resetSelection('vice')"
                            class="px-3 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-lg transition whitespace-nowrap">
                        Ganti Siswa
                    </button>
                </div>
                @error('vice_id')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- BAGIAN 3: FOTO PASLON (OTOMATIS & OPSIONAL UNGGAH BERSAMA)      --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Foto Pasangan Calon</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Foto secara otomatis diambil dari data foto siswa yang dipilih.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-semibold">
                        <span>✓</span> Otomatis Menyesuaikan
                    </span>
                </div>

                {{-- Tampilan Pratinjau Foto Otomatis / Custom --}}
                <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                    <template x-if="customPhotoPreview">
                        <div class="text-center">
                            <img :src="customPhotoPreview" class="w-28 h-28 rounded-xl object-cover border-2 border-indigo-500 shadow">
                            <span class="block text-[11px] text-indigo-600 font-bold mt-1.5">Foto Khusus Paslon</span>
                        </div>
                    </template>

                    <template x-if="!customPhotoPreview">
                        <div class="flex items-center gap-4">
                            {{-- Foto Ketua --}}
                            <div class="text-center">
                                <img :src="chairmanPhoto || 'https://ui-avatars.com/api/?name=Ketua&background=4f46e5&color=fff'"
                                     class="w-20 h-20 rounded-xl object-cover border-2 border-indigo-500 shadow bg-slate-100">
                                <span class="block text-[10px] text-slate-500 font-semibold mt-1">Foto Ketua</span>
                            </div>
                            <span class="text-xl font-bold text-slate-300">&amp;</span>
                            {{-- Foto Wakil --}}
                            <div class="text-center">
                                <img :src="vicePhoto || 'https://ui-avatars.com/api/?name=Wakil&background=10b981&color=fff'"
                                     class="w-20 h-20 rounded-xl object-cover border-2 border-emerald-500 shadow bg-slate-100">
                                <span class="block text-[10px] text-slate-500 font-semibold mt-1">Foto Wakil</span>
                            </div>
                        </div>
                    </template>

                    <div class="flex-1 text-xs text-slate-500 space-y-2">
                        <p>
                            Sistem akan otomatis menggunakan foto profil siswa di atas pada bilik suara digital dan hasil rekap.
                        </p>
                        <div>
                            <input type="file" name="photo" accept="image/*" id="photo-input" class="hidden"
                                   @change="customPhotoPreview = URL.createObjectURL($event.target.files[0])">
                            <label for="photo-input"
                                   class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                📷 Unggah Foto Khusus Berdua (Opsional)
                            </label>
                            <template x-if="customPhotoPreview">
                                <button type="button" @click="customPhotoPreview = ''; document.getElementById('photo-input').value = ''"
                                        class="ml-2 text-xs text-red-500 hover:underline">
                                    Hapus Foto Khusus
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                @error('photo')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- BAGIAN 4: VISI & MISI                                            --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Visi Pasangan Calon
                    </label>
                    <textarea name="vision" rows="3"
                              placeholder="Tuliskan visi pasangan calon..."
                              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none @error('vision') border-red-500 @enderror">{{ old('vision', $candidate?->vision) }}</textarea>
                    @error('vision')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Misi Pasangan Calon
                    </label>
                    <textarea name="mission" rows="4"
                              placeholder="Tuliskan poin-poin misi pasangan calon..."
                              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none @error('mission') border-red-500 @enderror">{{ old('mission', $candidate?->mission) }}</textarea>
                    @error('mission')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Submit Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('admin.pilketos.periods.show', $period) }}"
                   class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition">
                    Batal
                </a>
                <button type="submit"
                        :disabled="!chairmanId || !viceId || chairmanId === viceId"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 disabled:dark:bg-slate-700 disabled:cursor-not-allowed text-white text-sm font-bold rounded-lg transition shadow-md">
                    {{ $candidate ? 'Simpan Perubahan' : 'Tambah Paslon' }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
