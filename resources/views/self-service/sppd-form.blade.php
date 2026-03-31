@extends('layouts.app')
@section('title', 'Cetak SPPD')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">

  {{-- Header --}}
  <div>
    <h1 class="text-2xl font-black text-gray-900
               tracking-tight">
      Surat Perintah Perjalanan Dinas
    </h1>
    <p class="text-sm text-gray-500 mt-1">
      Isi form berikut, PDF akan langsung diunduh
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

  {{-- Info Guru (otomatis) --}}
  <div class="bg-indigo-50 border border-indigo-100
              rounded-2xl p-5">
    <p class="text-[10px] font-black text-indigo-600
               uppercase tracking-widest mb-3">
      Data Otomatis dari Akun Anda
    </p>
    <div class="grid grid-cols-2 gap-3 text-sm">
      <div>
        <p class="text-[10px] text-gray-500
                   font-bold uppercase">Nama</p>
        <p class="font-black text-gray-900">
          {{ $teacher->name }}
        </p>
      </div>
      <div>
        <p class="text-[10px] text-gray-500
                   font-bold uppercase">NIP/NIY</p>
        <p class="font-black text-gray-900">
          {{ $teacher->nip ?? $teacher->niy ?? '-' }}
        </p>
      </div>
    </div>
  </div>

  {{-- Form --}}
  <form method="POST"
        action="{{ route('self-service.sppd.generate') }}"
        class="bg-white rounded-2xl border
               border-gray-100 shadow-sm p-6 space-y-5">
    @csrf

    {{-- Jabatan --}}
    <div>
      <label class="block text-xs font-bold
                     text-gray-600 mb-1
                     uppercase tracking-wider">
        Jabatan / Tugas
      </label>
      <input type="text" name="jabatan"
             value="{{ old('jabatan', 'Guru') }}"
             required
             class="w-full border border-gray-200
                    rounded-xl px-3 py-2.5 text-sm
                    focus:outline-none focus:ring-2
                    focus:ring-[var(--brand-primary)]
                    @error('jabatan') border-rose-400 @enderror">
      @error('jabatan')
        <p class="text-xs text-rose-500 mt-1">
          {{ $message }}
        </p>
      @enderror
    </div>

    {{-- Tujuan --}}
    <div>
      <label class="block text-xs font-bold
                     text-gray-600 mb-1
                     uppercase tracking-wider">
        Tujuan Perjalanan
      </label>
      <input type="text" name="tujuan"
             value="{{ old('tujuan') }}"
             placeholder="Contoh: Dinas Pendidikan Kab. Banjarnegara"
             required
             class="w-full border border-gray-200
                    rounded-xl px-3 py-2.5 text-sm
                    focus:outline-none focus:ring-2
                    focus:ring-[var(--brand-primary)]
                    @error('tujuan') border-rose-400 @enderror">
      @error('tujuan')
        <p class="text-xs text-rose-500 mt-1">
          {{ $message }}
        </p>
      @enderror
    </div>

    {{-- Keperluan --}}
    <div>
      <label class="block text-xs font-bold
                     text-gray-600 mb-1
                     uppercase tracking-wider">
        Keperluan / Maksud Perjalanan
      </label>
      <textarea name="keperluan" rows="3"
                required
                placeholder="Jelaskan keperluan perjalanan dinas..."
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

    {{-- Tanggal --}}
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-bold
                       text-gray-600 mb-1
                       uppercase tracking-wider">
          Tanggal Berangkat
        </label>
        <input type="date" name="tanggal_berangkat"
               value="{{ old('tanggal_berangkat',
                   date('Y-m-d')) }}"
               required
               class="w-full border border-gray-200
                      rounded-xl px-3 py-2.5 text-sm
                      focus:outline-none focus:ring-2
                      focus:ring-[var(--brand-primary)]">
        @error('tanggal_berangkat')
          <p class="text-xs text-rose-500 mt-1">
            {{ $message }}
          </p>
        @enderror
      </div>
      <div>
        <label class="block text-xs font-bold
                       text-gray-600 mb-1
                       uppercase tracking-wider">
          Tanggal Kembali
        </label>
        <input type="date" name="tanggal_kembali"
               value="{{ old('tanggal_kembali',
                   date('Y-m-d')) }}"
               required
               class="w-full border border-gray-200
                      rounded-xl px-3 py-2.5 text-sm
                      focus:outline-none focus:ring-2
                      focus:ring-[var(--brand-primary)]">
        @error('tanggal_kembali')
          <p class="text-xs text-rose-500 mt-1">
            {{ $message }}
          </p>
        @enderror
      </div>
    </div>

    {{-- Kendaraan --}}
    <div>
      <label class="block text-xs font-bold
                     text-gray-600 mb-1
                     uppercase tracking-wider">
        Kendaraan yang Digunakan
      </label>
      <select name="kendaraan"
              class="w-full border border-gray-200
                     rounded-xl px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2
                     focus:ring-[var(--brand-primary)]">
        <option value="Kendaraan Pribadi"
            {{ old('kendaraan') == 'Kendaraan Pribadi'
                ? 'selected' : '' }}>
          Kendaraan Pribadi
        </option>
        <option value="Kendaraan Dinas"
            {{ old('kendaraan') == 'Kendaraan Dinas'
                ? 'selected' : '' }}>
          Kendaraan Dinas
        </option>
        <option value="Angkutan Umum"
            {{ old('kendaraan') == 'Angkutan Umum'
                ? 'selected' : '' }}>
          Angkutan Umum
        </option>
        <option value="Motor"
            {{ old('kendaraan') == 'Motor'
                ? 'selected' : '' }}>
          Motor
        </option>
      </select>
    </div>

    {{-- Submit --}}
    <div class="pt-2">
      <button type="submit"
              class="w-full py-3 bg-[var(--brand-primary)]
                     text-white font-black text-sm
                     uppercase tracking-widest
                     rounded-xl hover:opacity-90
                     transition flex items-center
                     justify-center gap-2">
        <i class="fas fa-file-pdf"></i>
        Unduh SPPD (PDF)
      </button>
    </div>
  </form>
</div>
@endsection
