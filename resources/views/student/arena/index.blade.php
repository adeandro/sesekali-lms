@extends('layouts.app')
@section('title', 'Battle Arena')
@section('content')
<div class="min-h-[80vh] flex items-center
            justify-center px-4 py-8">
  <div class="w-full max-w-sm space-y-6">

    {{-- Header --}}
    <div class="text-center">
      <div class="w-20 h-20 mx-auto mb-4 rounded-3xl
                  bg-gradient-to-br from-purple-500
                  to-indigo-600 flex items-center
                  justify-center shadow-lg
                  shadow-purple-500/25">
        <i class="fas fa-fist-raised text-3xl
                   text-white"></i>
      </div>
      <h1 class="text-2xl font-black text-gray-900
                  dark:text-white tracking-tight">
        Battle Arena
      </h1>
      <p class="text-sm text-gray-500 mt-1">
        Masukkan kode dari gurumu untuk mulai
      </p>
    </div>

    @if($errors->any())
    <div class="flex items-start gap-3 px-4 py-3
                rounded-xl bg-red-50 dark:bg-red-900/20
                border border-red-200
                dark:border-red-800">
      <i class="fas fa-exclamation-circle
                 text-red-500 mt-0.5 shrink-0"></i>
      <p class="text-sm text-red-700
                 dark:text-red-300 font-medium">
        {{ $errors->first() }}
      </p>
    </div>
    @endif

    {{-- Form join --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl
                border border-gray-100
                dark:border-gray-700 p-6 shadow-sm">
      <form method="POST"
            action="{{ route('student.arena.join') }}">
        @csrf
        <label class="block text-xs font-black
                       text-gray-400 uppercase
                       tracking-widest mb-3 text-center">
          Kode Room
        </label>
        <input type="text"
               name="token"
               value="{{ old('token') }}"
               maxlength="6"
               placeholder="ABCXYZ"
               autocomplete="off"
               class="w-full px-4 py-4 rounded-2xl
                      border-2 border-gray-200
                      dark:border-gray-600
                      bg-gray-50 dark:bg-gray-700
                      text-center text-3xl font-black
                      font-mono tracking-[0.5em]
                      uppercase text-gray-900
                      dark:text-white
                      focus:border-purple-500
                      focus:ring-4
                      focus:ring-purple-500/10
                      outline-none transition-all
                      placeholder:tracking-normal
                      placeholder:text-gray-300
                      placeholder:text-xl"
               oninput="this.value =
                 this.value.toUpperCase()">

        <button type="submit"
                class="w-full mt-4 py-3.5 rounded-2xl
                       bg-purple-600 text-white
                       font-black text-sm uppercase
                       tracking-widest
                       hover:bg-purple-700
                       active:scale-[0.98]
                       transition-all shadow-sm
                       shadow-purple-500/20">
          Bergabung
          <i class="fas fa-arrow-right ml-2"></i>
        </button>
      </form>
    </div>

    {{-- Info --}}
    <p class="text-center text-xs text-gray-400">
      Kode terdiri dari 6 karakter huruf/angka.<br>
      Dapatkan dari gurumu sebelum battle dimulai.
    </p>

  </div>
</div>
@endsection
