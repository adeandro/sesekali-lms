{{-- Shared form partial for Season create / edit --}}
<div>
    <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Nama Season <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $season?->name) }}" placeholder="Semester Ganjil 2025/2026" required
           class="w-full px-4 py-2.5 rounded-2xl border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }} text-sm font-bold focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Semester</label>
        <select name="semester_type" required class="w-full px-4 py-2.5 rounded-2xl border border-gray-200 text-sm font-bold focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            <option value="ganjil" {{ old('semester_type', $season?->semester_type) === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
            <option value="genap"  {{ old('semester_type', $season?->semester_type) === 'genap'  ? 'selected' : '' }}>Genap</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Tahun Ajaran</label>
        <input type="text" name="academic_year" value="{{ old('academic_year', $season?->academic_year) }}" placeholder="2025/2026" required
               class="w-full px-4 py-2.5 rounded-2xl border border-gray-200 text-sm font-bold focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Tanggal Mulai</label>
        <input type="date" name="start_date" value="{{ old('start_date', $season?->start_date?->format('Y-m-d')) }}" required
               class="w-full px-4 py-2.5 rounded-2xl border border-gray-200 text-sm font-bold focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5">Tanggal Selesai</label>
        <input type="date" name="end_date" value="{{ old('end_date', $season?->end_date?->format('Y-m-d')) }}" required
               class="w-full px-4 py-2.5 rounded-2xl border border-gray-200 text-sm font-bold focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    </div>
</div>

<label class="flex items-center gap-3 cursor-pointer p-3 rounded-2xl border border-gray-100 hover:border-indigo-200 transition">
    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $season?->is_active) ? 'checked' : '' }}
           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
    <div>
        <p class="text-sm font-black text-gray-900">Jadikan Season Aktif</p>
        <p class="text-xs text-gray-400">Season lain akan otomatis dinonaktifkan.</p>
    </div>
</label>
