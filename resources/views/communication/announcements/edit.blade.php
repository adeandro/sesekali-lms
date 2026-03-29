@extends('layouts.app')

@section('title', 'Edit Pengumuman - ' . ($configs['school_name'] ?? 'ExamFlow'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('communication.announcements.index') }}"
           class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-400 hover:text-[var(--brand-primary)] hover:bg-[var(--brand-glow)] transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-black text-gray-900">Edit Pengumuman</h1>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui informasi pengumuman yang sudah ada.</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('communication.announcements.update', $announcement) }}" method="POST"
          class="theme-surface-card rounded-2xl p-6 space-y-5 theme-soft-shadow">
        @csrf
        @method('PUT')

        {{-- Title --}}
        <div>
            <label for="title" class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                Judul Pengumuman <span class="text-red-500">*</span>
            </label>
            <input type="text" id="title" name="title" value="{{ old('title', $announcement->title) }}" required
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent transition @error('title') border-red-400 @enderror"
                   placeholder="Contoh: Jadwal Ujian Semester Ganjil">
            @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Content --}}
        <div>
            <label for="content" class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                Isi Pengumuman <span class="text-red-500">*</span>
            </label>
            <textarea id="content" name="content" rows="5" required
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent transition resize-none @error('content') border-red-400 @enderror"
                      placeholder="Tulis isi pengumuman di sini...">{{ old('content', $announcement->content) }}</textarea>
            @error('content')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Type + Target Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="type" class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                    Jenis <span class="text-red-500">*</span>
                </label>
                <select id="type" name="type" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent @error('type') border-red-400 @enderror">
                    <option value="info"    {{ old('type', $announcement->type) === 'info'    ? 'selected' : '' }}>ℹ️  Informasi</option>
                    <option value="warning" {{ old('type', $announcement->type) === 'warning' ? 'selected' : '' }}>⚠️  Peringatan</option>
                    <option value="urgent"  {{ old('type', $announcement->type) === 'urgent'  ? 'selected' : '' }}>🚨  URGENT</option>
                </select>
                @error('type')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="target_role" class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                    Penerima <span class="text-red-500">*</span>
                </label>
                <select id="target_role" name="target_role" required
                        x-data x-on:change="$dispatch('role-changed', $event.target.value)"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent @error('target_role') border-red-400 @enderror">
                    <option value="all"     {{ old('target_role', $announcement->target_role) === 'all'     ? 'selected' : '' }}>Semua Pengguna</option>
                    <option value="student" {{ old('target_role', $announcement->target_role) === 'student' ? 'selected' : '' }}>Siswa</option>
                    @if(auth()->user()->role === 'superadmin')
                    <option value="teacher" {{ old('target_role', $announcement->target_role) === 'teacher' ? 'selected' : '' }}>Guru</option>
                    @endif
                </select>
                @error('target_role')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Target Class (conditional) --}}
        @if(!empty($classes))
        <div x-data="{ role: '{{ old('target_role', $announcement->target_role) }}' }" @role-changed.window="role = $event.detail">
            <div x-show="role === 'student'" x-transition>
                <label for="target_class_id" class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                    Kelas (opsional — kosongkan untuk semua kelas)
                </label>
                <select id="target_class_id" name="target_class_id"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                    <option value="{{ $class }}" {{ old('target_class_id', $announcement->target_class_id) === $class ? 'selected' : '' }}>{{ $class }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        {{-- Expires At --}}
        <div>
            <label for="expires_at" class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">
                Berlaku Hingga (opsional)
            </label>
            <input type="datetime-local" id="expires_at" name="expires_at" value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}"
                   min="{{ now()->format('Y-m-d\TH:i') }}"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:border-transparent @error('expires_at') border-red-400 @enderror">
            <p class="text-xs text-gray-400 mt-1">Biarkan kosong jika tidak ada batas waktu.</p>
            @error('expires_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Show on Login Toggle --}}
        <div class="flex items-start gap-4 p-4 rounded-xl border border-indigo-100 bg-indigo-50/50">
            <label for="show_on_login" class="flex items-center gap-3 cursor-pointer select-none group flex-1">
                <div class="relative">
                    <input type="checkbox" id="show_on_login" name="show_on_login" value="1"
                           class="peer sr-only" {{ old('show_on_login', $announcement->show_on_login) ? 'checked' : '' }}>
                    <div class="w-10 h-6 rounded-full border-2 border-gray-300 bg-white transition-all
                                peer-checked:border-indigo-600 peer-checked:bg-indigo-600 group-hover:border-indigo-400">
                    </div>
                    <div class="absolute top-[3px] left-[3px] w-4 h-4 rounded-full bg-gray-300 shadow transition-all
                                peer-checked:translate-x-4 peer-checked:bg-white">
                    </div>
                </div>
                <div>
                    <p class="text-sm font-black text-gray-800">Tampilkan di Halaman Login</p>
                    <p class="text-xs text-gray-500 mt-0.5">Pengumuman akan muncul sebagai floating card di halaman login untuk semua pengunjung.</p>
                </div>
            </label>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-indigo-100 shrink-0">
                <i class="fas fa-door-open text-indigo-600 text-sm"></i>
            </div>
        </div>

        {{-- Preview Panel --}}
        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Preview Banner</p>
            <div class="announcement-banner announcement-banner-{{ $announcement->type }}" id="previewBanner">
                <div class="w-9 h-9 rounded-xl {{ $announcement->type === 'urgent' ? 'bg-red-100' : ($announcement->type === 'warning' ? 'bg-amber-100' : 'bg-blue-100') }} flex items-center justify-center shrink-0">
                    <i class="fas {{ $announcement->type === 'urgent' ? 'fa-bell text-red-600' : ($announcement->type === 'warning' ? 'fa-exclamation-triangle text-amber-600' : 'fa-info-circle text-blue-600') }} text-sm" id="previewIcon"></i>
                </div>
                <div>
                    <p class="text-sm font-black" id="previewTitle">{{ $announcement->title }}</p>
                    <p class="text-xs opacity-80 mt-0.5 line-clamp-2" id="previewContent">{{ $announcement->content }}</p>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
            <a href="{{ route('communication.announcements.index') }}"
               class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 transition">
                Batal
            </a>
            <button type="submit" class="theme-primary-btn px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
// Live preview
const titleInput   = document.getElementById('title');
const contentInput = document.getElementById('content');
const typeSelect   = document.getElementById('type');
const banner       = document.getElementById('previewBanner');
const icon         = document.getElementById('previewIcon');
const pvTitle      = document.getElementById('previewTitle');
const pvContent    = document.getElementById('previewContent');

const configs = {
    info:    { cls: 'announcement-banner-info',    icon: 'fa-info-circle',         iconBg: 'bg-blue-100',   iconColor: 'text-blue-600' },
    warning: { cls: 'announcement-banner-warning', icon: 'fa-exclamation-triangle', iconBg: 'bg-amber-100',  iconColor: 'text-amber-600' },
    urgent:  { cls: 'announcement-banner-urgent',  icon: 'fa-bell',                 iconBg: 'bg-red-100',    iconColor: 'text-red-600' },
};

function updatePreview() {
    const type = typeSelect.value;
    const cfg  = configs[type];
    banner.className = `announcement-banner ${cfg.cls}`;
    icon.className   = `fas ${cfg.icon} text-sm ${cfg.iconColor}`;
    icon.parentElement.className = `w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${cfg.iconBg}`;
    pvTitle.textContent   = titleInput.value   || 'Judul Pengumuman';
    pvContent.textContent = contentInput.value || 'Isi pengumuman akan muncul di sini.';
}

[titleInput, contentInput, typeSelect].forEach(el => el.addEventListener('input', updatePreview));
</script>
@endsection
