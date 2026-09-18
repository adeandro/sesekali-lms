@extends('layouts.app')
@section('title', 'Masukkan Token — ' . $test->title)
@section('content')
<div class="max-w-md mx-auto mt-20 space-y-6">

  {{-- Card --}}
  <div class="bg-white rounded-[2.5rem] border
              border-gray-100 shadow-sm p-8 space-y-6">

    {{-- Icon --}}
    <div class="text-center">
      <div class="w-16 h-16 rounded-2xl mx-auto
                  flex items-center justify-center text-2xl"
           style="background:var(--brand-glow);
                  color:var(--brand-primary)">
        <i class="fas fa-keyboard"></i>
      </div>
      <h1 class="mt-4 text-xl font-black text-gray-900
                 tracking-tight">
        {{ $test->title }}
      </h1>
      <p class="text-sm text-gray-500 mt-1">
        Masukkan token akses dari gurumu untuk memulai
      </p>
    </div>

    {{-- Error --}}
    @error('token')
    <div class="bg-rose-50 border border-rose-200
                rounded-xl px-4 py-3 text-sm
                text-rose-700 font-medium">
      <i class="fas fa-exclamation-circle mr-2"></i>
      {{ $message }}
    </div>
    @enderror

    {{-- Form --}}
    <form method="POST"
          action="{{ route('student.typing-tests.validate-token', $test) }}">
      @csrf
      <div class="space-y-4">
        <input type="text"
               name="token"
               value="{{ old('token') }}"
               placeholder="Contoh: XK9QPR"
               maxlength="6"
               autocomplete="off"
               oninput="this.value=this.value.toUpperCase()"
               autofocus
               class="w-full text-center text-2xl
                      font-black tracking-[0.4em]
                      uppercase border border-gray-200
                      rounded-xl px-4 py-4
                      focus:outline-none focus:ring-2
                      focus:ring-[var(--brand-primary)]">
        <button type="submit"
                class="w-full py-3 rounded-xl
                       font-black text-sm uppercase
                       tracking-widest text-white
                       hover:opacity-90 transition"
                style="background:var(--brand-primary)">
          <i class="fas fa-arrow-right mr-2"></i>
          Lanjutkan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
