@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.typing-tests.index') }}" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold" style="color: var(--brand-primary)">
            <i class="fas fa-keyboard mr-2"></i>Edit Tes Mengetik
        </h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form method="POST" action="{{ route('admin.typing-tests.update', $test) }}" x-data="typingTestForm()">
            @csrf
            @method('PUT')

            {{-- Judul --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tes <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $test->title) }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                       style="--tw-ring-color: var(--brand-primary)" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                          style="--tw-ring-color: var(--brand-primary)">{{ old('description', $test->description) }}</textarea>
            </div>

            {{-- Token --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Token Akses</label>
                <input type="text" name="token" value="{{ old('token', $test->token) }}"
                       placeholder="Kosongkan jika tidak memerlukan token"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                       style="--tw-ring-color: var(--brand-primary)">
                <p class="text-xs text-gray-400 mt-1">Jika diisi, siswa wajib memasukkan token ini sebelum memulai tes.</p>
            </div>

            {{-- Durasi + Target WPM --}}
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (detik) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_seconds" value="{{ old('duration_seconds', $test->duration_seconds) }}"
                           min="10" max="600"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: var(--brand-primary)" required>
                    <p class="text-xs text-gray-400 mt-1">Contoh: 15, 30, 60, 120 detik</p>
                    @error('duration_seconds') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target WPM <span class="text-red-500">*</span></label>
                    <input type="number" name="target_wpm" value="{{ old('target_wpm', $test->target_wpm) }}"
                           min="1" max="300"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: var(--brand-primary)" required>
                    <p class="text-xs text-gray-400 mt-1">Siswa mencapai WPM ini = nilai kecepatan penuh</p>
                    @error('target_wpm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Bobot --}}
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bobot Akurasi (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="weight_accuracy" x-model.number="weightAccuracy"
                           value="{{ old('weight_accuracy', $test->weight_accuracy) }}" min="0" max="100"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none"
                           required>
                    @error('weight_accuracy') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bobot Kecepatan (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="weight_speed" x-model.number="weightSpeed"
                           value="{{ old('weight_speed', $test->weight_speed) }}" min="0" max="100"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none"
                           required>
                </div>
            </div>
            <div class="mb-5">
                <p class="text-xs" :class="weightAccuracy + weightSpeed === 100 ? 'text-emerald-600' : 'text-red-500'">
                    <i class="fas" :class="weightAccuracy + weightSpeed === 100 ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
                    Total bobot: <span x-text="weightAccuracy + weightSpeed"></span>% (harus 100%)
                </p>
            </div>

            {{-- Tampilkan hasil --}}
            <div class="mb-5 flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="show_result" value="1" class="sr-only peer"
                           {{ (old('show_result') !== null ? old('show_result') : $test->show_result) ? 'checked' : '' }}>
                    <div class="w-10 h-6 bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors peer-focus:ring-2 peer-focus:ring-emerald-300"></div>
                    <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                </label>
                <span class="text-sm font-medium text-gray-700">Tampilkan hasil (WPM, akurasi, nilai) ke siswa setelah selesai</span>
            </div>

            {{-- Jadwal --}}
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jadwal Mulai</label>
                    <input type="datetime-local" name="starts_at"
                           value="{{ old('starts_at', $test->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: var(--brand-primary)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jadwal Berakhir</label>
                    <input type="datetime-local" name="ends_at"
                           value="{{ old('ends_at', $test->ends_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: var(--brand-primary)">
                </div>
            </div>

            {{-- Status --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                        style="--tw-ring-color: var(--brand-primary)">
                    <option value="draft" {{ old('status', $test->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $test->status) === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>

            {{-- Batasi Kelas --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Batasi Kelas</label>
                <p class="text-xs text-gray-400 mb-2">Kosongkan untuk membuka ke semua kelas.</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 border border-gray-200 rounded-xl p-3 max-h-48 overflow-y-auto">
                    @foreach($classes as $class)
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" name="class_restriction[]" value="{{ $class->id }}"
                                   class="rounded text-emerald-500"
                                   {{ in_array($class->id, old('class_restriction', $test->class_restriction ?? [])) ? 'checked' : '' }}>
                            {{ $class->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3">
                <button type="submit"
                        :disabled="weightAccuracy + weightSpeed !== 100"
                        class="px-6 py-2.5 rounded-xl text-white text-sm font-medium shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background: var(--brand-primary)">
                    <i class="fas fa-save mr-2"></i>Perbarui Tes
                </button>
                <a href="{{ route('admin.typing-tests.index') }}"
                   class="px-6 py-2.5 rounded-xl text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function typingTestForm() {
    return {
        weightAccuracy: parseInt('{{ old('weight_accuracy', $test->weight_accuracy) }}'),
        weightSpeed: parseInt('{{ old('weight_speed', $test->weight_speed) }}'),
    };
}
</script>
@endpush
@endsection
