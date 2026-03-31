@extends('layouts.app')
@section('title', 'Surat Keterangan Aktif')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">

  <div>
    <h1 class="text-2xl font-black text-gray-900
               tracking-tight">
      Surat Keterangan Siswa Aktif
    </h1>
    <p class="text-sm text-gray-500 mt-1">
      Surat akan langsung diunduh setelah form diisi
    </p>
  </div>

  <div class="bg-amber-50 border border-amber-100
              rounded-xl px-4 py-2 text-xs
              text-amber-700 flex items-center gap-2">
    <i class="fas fa-info-circle"></i>
    Format surat menggunakan template
    <strong>{{ $template->name }}</strong>.
    TU/Admin dapat mengubah format di menu
    Manajemen Surat → Templates.
  </div>

  {{-- Info Siswa --}}
  <div class="bg-emerald-50 border border-emerald-100
              rounded-2xl p-5">
    <p class="text-[10px] font-black text-emerald-600
               uppercase tracking-widest mb-3">
      Data Otomatis dari Akun Anda
    </p>
    <div class="grid grid-cols-2 gap-3 text-sm">
      <div>
        <p class="text-[10px] text-gray-500
                   font-bold uppercase">Nama</p>
        <p class="font-black text-gray-900">
          {{ $student->name }}
        </p>
      </div>
      <div>
        <p class="text-[10px] text-gray-500
                   font-bold uppercase">NIS</p>
        <p class="font-black text-gray-900">
          {{ $student->nis ?? '-' }}
        </p>
      </div>
      <div>
        <p class="text-[10px] text-gray-500
                   font-bold uppercase">Kelas</p>
        <p class="font-black text-gray-900">
          {{ $student->classRoom->name ?? '-' }}
        </p>
      </div>
      <div>
        <p class="text-[10px] text-gray-500
                   font-bold uppercase">Tahun Ajaran</p>
        <p class="font-black text-gray-900">
          {{ \App\Models\Setting::get('academic_year', '-') }}
        </p>
      </div>
    </div>
  </div>

  {{-- Form --}}
  <form method="POST"
        action="{{ route('self-service.sk.generate') }}"
        class="bg-white rounded-2xl border
               border-gray-100 shadow-sm p-6 space-y-5">
    @csrf

    <div>
      <label class="block text-xs font-bold
                     text-gray-600 mb-1
                     uppercase tracking-wider">
        Ditujukan Kepada
      </label>
      <input type="text" name="ditujukan"
             value="{{ old('ditujukan') }}"
             placeholder="Contoh: Yth. Kepala Dinas Kependudukan..."
             required
             class="w-full border border-gray-200
                    rounded-xl px-3 py-2.5 text-sm
                    focus:outline-none focus:ring-2
                    focus:ring-[var(--brand-primary)]
                    @error('ditujukan') border-rose-400 @enderror">
      @error('ditujukan')
        <p class="text-xs text-rose-500 mt-1">
          {{ $message }}
        </p>
      @enderror
    </div>

    <div>
      <label class="block text-xs font-bold
                     text-gray-600 mb-1
                     uppercase tracking-wider">
        Keperluan Surat
      </label>
      <textarea name="keperluan" rows="3"
                required
                placeholder="Contoh: untuk keperluan pembuatan KTP..."
                class="w-full border border-gray-200
                       rounded-xl px-3 py-2.5 text-sm
                       resize-none focus:outline-none
                       focus:ring-2
                       focus:ring-[var(--brand-primary)]
                       @error('keperluan') border-rose-400 @enderror">{{ old('keperluan') }}</textarea>
      @error('keperluan')
        <p class="text-xs text-rose-500 mt-1">
          {{ $message }}
        </p>
      @enderror
    </div>

    <div class="pt-2">
      <button type="submit"
              class="w-full py-3
                     bg-[var(--brand-primary)]
                     text-white font-black text-sm
                     uppercase tracking-widest
                     rounded-xl hover:opacity-90
                     transition flex items-center
                     justify-center gap-2">
        <i class="fas fa-file-pdf"></i>
        Unduh Surat Keterangan (PDF)
      </button>
    </div>
  </form>
</div>
@endsection
