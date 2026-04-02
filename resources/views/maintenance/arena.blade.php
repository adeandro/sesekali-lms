@extends('layouts.app')

@section('title', 'Battle Arena — Maintenance')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center p-6">
  <div class="text-center space-y-6 max-w-md">

    {{-- Icon --}}
    <div class="w-24 h-24 rounded-[2rem] bg-amber-50 border border-amber-100 flex items-center justify-center mx-auto">
      <i class="fas fa-tools text-4xl text-amber-500"></i>
    </div>

    {{-- Text --}}
    <div class="space-y-2">
      <h1 class="text-2xl font-black text-gray-900 tracking-tight">
        Battle Arena
      </h1>
      <div class="inline-flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-full px-4 py-1.5">
        <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse">
        </div>
        <span class="text-xs font-black text-amber-600 uppercase tracking-widest">
          Sedang dalam perbaikan
        </span>
      </div>
      <p class="text-sm text-gray-500 mt-3">
        Fitur Battle Arena sedang kami tingkatkan untuk memberikan pengalaman yang lebih baik. Akan segera hadir kembali! 🚀
      </p>
    </div>

    {{-- Back button --}}
    <a href="{{ url()->previous() == url()->current() ? '/' : url()->previous() }}"
       class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white font-black text-sm rounded-2xl hover:bg-gray-700 transition">
      <i class="fas fa-arrow-left"></i>
      Kembali
    </a>

  </div>
</div>
@endsection
