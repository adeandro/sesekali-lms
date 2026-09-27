@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.project-assignments.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold" style="color: var(--brand-primary)">
                <i class="fas fa-edit mr-2"></i>Edit Assignment Project
            </h1>
        </div>
        <a href="{{ route('gallery.show', $assignment) }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
            <i class="fas fa-external-link-alt"></i> Lihat Galeri Publik
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8"
         x-data="{ selectedType: '{{ old('assignment_type', $assignment->assignment_type->value ?? 'web') }}', defaults: { web: 10, document: 25, image: 5, general: 20 } }">
        <form method="POST" action="{{ route('admin.project-assignments.update', $assignment) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Tipe Assignment --}}
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-2 uppercase tracking-wider">
                    Tipe Assignment <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach($types as $type)
                    <label class="cursor-pointer" @click="selectedType = '{{ $type->value }}'">
                        <input type="radio"
                               name="assignment_type"
                               value="{{ $type->value }}"
                               x-model="selectedType"
                               class="sr-only">
                        <div class="p-4 rounded-2xl border-2 text-center transition-all h-full flex flex-col items-center justify-center"
                             :class="selectedType === '{{ $type->value }}'
                               ? 'border-[var(--brand-primary)] bg-indigo-50/40 shadow-xs'
                               : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <i class="fas {{ $type->icon() }} text-xl mb-2"
                               :class="selectedType === '{{ $type->value }}'
                                   ? 'text-[var(--brand-primary)]'
                                   : 'text-gray-400'"></i>
                            <p class="text-xs font-black text-gray-700">
                                {{ $type->label() }}
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                @switch($type->value)
                                    @case('web')
                                        ZIP/RAR + index.html
                                        @break
                                    @case('document')
                                        PDF, DOCX, PPTX, XLSX
                                        @break
                                    @case('image')
                                        JPG, PNG, GIF, SVG
                                        @break
                                    @case('general')
                                        File bebas
                                        @break
                                @endswitch
                            </p>
                        </div>
                    </label>
                    @endforeach

                    {{-- Field allowed_extensions untuk tipe general --}}
                    <div x-show="selectedType === 'general'"
                         x-cloak
                         x-transition
                         class="col-span-2 sm:col-span-4 mt-2 p-4 rounded-2xl bg-amber-50/60 border border-amber-200/80">
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">
                            Ekstensi yang Diizinkan
                            <span class="text-gray-400 font-normal normal-case">
                                (pisahkan dengan koma, tanpa tanda titik)
                            </span>
                        </label>
                        <input type="text"
                               name="allowed_extensions_raw"
                               placeholder="contoh: mp3, wav, pdf, txt, blend"
                               value="{{ old('allowed_extensions_raw', isset($assignment) ? implode(', ', $assignment->allowed_extensions ?? []) : '') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2"
                               style="--tw-ring-color: var(--brand-primary)">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Kosongkan jika ingin mengizinkan seluruh format file bebas.
                        </p>
                    </div>
                </div>
                @error('assignment_type') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Judul Assignment --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Judul Assignment <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $assignment->title) }}"
                       class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                       style="--tw-ring-color: var(--brand-primary)" required>
                <p class="text-xs text-gray-400 mt-1">Slug saat ini: <span class="font-mono text-indigo-600 font-bold">/gallery/{{ $assignment->slug }}</span></p>
                @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi & Petunjuk Tugas</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                          style="--tw-ring-color: var(--brand-primary)">{{ old('description', $assignment->description) }}</textarea>
                @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Mapel & Batas Ukuran & Jumlah Slot --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mata Pelajaran</label>
                    <select name="subject_id"
                            class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                            style="--tw-ring-color: var(--brand-primary)">
                        <option value="">-- Umum / Semua Mapel --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Batas Ukuran File (MB)
                    </label>
                    <input type="number" name="max_file_size_mb" value="{{ old('max_file_size_mb', $assignment->max_file_size_mb) }}"
                           :placeholder="'Default: ' + (defaults[selectedType] || 10) + ' MB'"
                           min="1" max="500"
                           class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: var(--brand-primary)">
                    <p class="text-[11px] text-gray-400 mt-1"
                       x-text="'Default untuk tipe ini: ' + (defaults[selectedType] || 10) + ' MB. Kosongkan untuk pakai default.'">
                    </p>
                    @error('max_file_size_mb') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Slot Tugas per Siswa <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="max_slots" value="{{ old('max_slots', $assignment->max_slots) }}"
                           min="1" max="10"
                           class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: var(--brand-primary)" required>
                    <p class="text-[11px] text-gray-400 mt-1">1 = 1 file/projek, 2+ = multi submission</p>
                    @error('max_slots') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Jadwal Mulai & Berakhir --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jadwal Mulai Upload</label>
                    <input type="datetime-local" name="starts_at"
                           value="{{ old('starts_at', $assignment->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: var(--brand-primary)">
                    @error('starts_at') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Batas Waktu (Deadline)</label>
                    <input type="datetime-local" name="ends_at"
                           value="{{ old('ends_at', $assignment->ends_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: var(--brand-primary)">
                    @error('ends_at') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Status Aktif --}}
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                <div>
                    <p class="text-sm font-bold text-gray-800">Status Aktif Assignment</p>
                    <p class="text-xs text-gray-500 mt-0.5">Jika dinonaktifkan, siswa tidak bisa upload/update tugas, namun galeri tetap dapat dilihat.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $assignment->is_active) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            {{-- Batasi Kelas --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Batasi Kelas yang Mengikuti</label>
                <p class="text-xs text-gray-400 mb-2.5">Pilih kelas yang wajib mengumpulkan tugas project ini. Kosongkan untuk membuka bagi semua kelas.</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 border border-gray-200 rounded-2xl p-4 max-h-48 overflow-y-auto bg-slate-50/50">
                    @foreach($classes as $class)
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer hover:text-indigo-600 transition">
                            <input type="checkbox" name="class_restriction[]" value="{{ $class->id }}"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   {{ in_array($class->id, old('class_restriction', $assignment->class_restriction ?? [])) ? 'checked' : '' }}>
                            {{ $class->name }}
                        </label>
                    @endforeach
                </div>
                @error('class_restriction') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2.5 rounded-2xl text-white text-sm font-semibold shadow-md transition hover:opacity-90"
                        style="background: var(--brand-primary)">
                    <i class="fas fa-save mr-2"></i>Perbarui Assignment
                </button>
                <a href="{{ route('admin.project-assignments.index') }}"
                   class="px-6 py-2.5 rounded-2xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
