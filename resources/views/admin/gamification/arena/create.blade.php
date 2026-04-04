@extends('layouts.app')
@section('title', 'Buat Battle Room')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8"
     x-data="createRoom()">

  <div class="mb-6">
    <a href="{{ route('admin.gamification.arena.index') }}"
       class="text-sm text-gray-500 hover:text-gray-700
              dark:text-gray-400 flex items-center gap-1">
      ← Kembali
    </a>
    <h1 class="text-xl font-medium text-gray-900
               dark:text-white mt-2">
      Buat Battle Room
    </h1>
  </div>

  @if($errors->any())
  <div class="mb-4 px-4 py-3 rounded-lg bg-red-50
              border border-red-200 text-red-700 text-sm
              dark:bg-red-900/20 dark:text-red-300">
    <ul class="list-disc list-inside space-y-1">
      @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <form method="POST"
        action="{{ route('admin.gamification.arena.store') }}"
        x-init="$nextTick(() => {})"
        class="space-y-5">
    @csrf

    {{-- Info Dasar --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border
                border-gray-200 dark:border-gray-700 p-5
                space-y-4">
      <h2 class="font-medium text-gray-800
                 dark:text-white text-sm">
        Informasi Dasar
      </h2>

      <div>
        <label class="block text-sm text-gray-600
                      dark:text-gray-300 mb-1">
          Nama Battle
        </label>
        <input type="text" name="name"
               value="{{ old('name') }}"
               placeholder="contoh: Kuis Matematika Kelas 10A"
               class="w-full px-3 py-2 rounded-lg border
                      border-gray-200 dark:border-gray-600
                      bg-white dark:bg-gray-700
                      text-gray-900 dark:text-white text-sm
                      focus:ring-2 focus:ring-purple-500
                      focus:border-transparent outline-none">
      </div>

      <div>
        <label class="block text-sm text-gray-600
                      dark:text-gray-300 mb-2">
          Mode Battle
        </label>
        <div class="grid grid-cols-3 gap-3">
          @foreach([
            ['individual', 'Siswa vs Siswa', 'Ranking personal'],
            ['group',      'Grup / Tim',     'Kelompok bersaing'],
            ['class',      'Kelas vs Kelas', 'Perwakilan kelas'],
          ] as [$val, $label, $desc])
          <label class="relative cursor-pointer">
            <input type="radio" name="mode"
                   value="{{ $val }}"
                   x-model="mode"
                   {{ old('mode','individual') === $val ? 'checked' : '' }}
                   class="peer sr-only">
            <div class="px-3 py-3 rounded-lg border-2
                        text-center transition-colors
                        border-gray-200 dark:border-gray-600
                        peer-checked:border-purple-500
                        peer-checked:bg-purple-50
                        dark:peer-checked:bg-purple-900/20">
              <p class="text-sm font-medium text-gray-800
                         dark:text-white">{{ $label }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                {{ $desc }}
              </p>
            </div>
          </label>
          @endforeach
        </div>
      </div>

      {{-- Konfigurasi Grup --}}
      <div x-show="mode === 'group'"
           x-cloak
           x-transition
           class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-600
                        dark:text-gray-300 mb-1">
            Jumlah Grup
          </label>
          <select name="group_count"
                  class="w-full px-3 py-2 rounded-lg border
                         border-gray-200 dark:border-gray-600
                         bg-white dark:bg-gray-700 text-sm
                         text-gray-900 dark:text-white
                         focus:ring-2 focus:ring-purple-500
                         outline-none">
            @foreach(range(2, 8) as $n)
            <option value="{{ $n }}"
              {{ old('group_count', 4) == $n ? 'selected' : '' }}>
              {{ $n }} Grup
            </option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-600
                        dark:text-gray-300 mb-1">
            Maks. per Grup
            <span class="text-gray-400 text-xs">(opsional)</span>
          </label>
          <input type="number" name="max_per_group"
                 value="{{ old('max_per_group') }}"
                 min="1" max="20"
                 placeholder="Auto"
                 class="w-full px-3 py-2 rounded-lg border
                        border-gray-200 dark:border-gray-600
                        bg-white dark:bg-gray-700 text-sm
                        text-gray-900 dark:text-white
                        focus:ring-2 focus:ring-purple-500
                        outline-none">
        </div>
      </div>
    </div>

    {{-- Sumber Soal --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border
                border-gray-200 dark:border-gray-700 p-5
                space-y-4">
      <h2 class="font-medium text-gray-800
                 dark:text-white text-sm">
        Sumber Soal
        <span class="text-xs font-normal text-gray-400 ml-1">
          — hanya soal pilihan ganda (PG)
        </span>
      </h2>

      <div class="flex gap-5">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" name="source_type"
                 value="exam"
                 x-model="sourceType"
                 {{ old('source_type','exam') === 'exam' ? 'checked' : '' }}
                 class="text-purple-600 accent-purple-600">
          <span class="text-sm text-gray-700
                       dark:text-gray-300">
            Dari Ujian
          </span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" name="source_type"
                 value="manual"
                 x-model="sourceType"
                 {{ old('source_type') === 'manual' ? 'checked' : '' }}
                 class="text-purple-600 accent-purple-600">
          <span class="text-sm text-gray-700
                       dark:text-gray-300">
            Pilih Soal Manual
          </span>
        </label>
      </div>

      {{-- Dari Ujian --}}
      <div x-show="sourceType === 'exam'" x-transition>
        <select name="source_id"
                @change="fetchExamPreview($event.target.value)"
                class="w-full px-3 py-2 rounded-lg border
                       border-gray-200 dark:border-gray-600
                       bg-white dark:bg-gray-700 text-sm
                       text-gray-900 dark:text-white
                       focus:ring-2 focus:ring-purple-500
                       outline-none">
          <option value="">— Pilih ujian —</option>
          @foreach($exams as $exam)
          <option value="{{ $exam->id }}"
            {{ old('source_id') == $exam->id ? 'selected' : '' }}>
            {{ $exam->title }}
            @if($exam->subject)
              ({{ $exam->subject->name }})
            @endif
          </option>
          @endforeach
        </select>

        {{-- Preview info soal --}}
        <template x-if="examPreview">
          <div class="mt-2 px-3 py-2 rounded-lg text-xs
                      border transition-colors"
               :class="examPreview.usable
                 ? 'bg-purple-50 border-purple-200 text-purple-700 dark:bg-purple-900/20 dark:border-purple-700 dark:text-purple-300'
                 : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300'">
            <span x-text="'Total soal: ' + examPreview.total"></span>
            &nbsp;·&nbsp;
            <span x-text="'PG: ' + examPreview.pg_count"></span>
            <template x-if="examPreview.essay_count > 0">
              <span x-text="' · Essay: ' + examPreview.essay_count + ' (diabaikan)'"></span>
            </template>
            <template x-if="examPreview.note">
              <p class="mt-1 font-medium"
                 x-text="examPreview.note"></p>
            </template>
          </div>
        </template>
      </div>

      {{-- Pilih Soal Manual --}}
      <div x-show="sourceType === 'manual'" x-transition>
        <div class="px-4 py-3 rounded-lg border border-dashed
                    border-gray-300 dark:border-gray-600
                    text-xs text-gray-400 text-center">
          Pilih soal manual — akan diimplementasi
          setelah alur dasar berfungsi
        </div>
        {{-- Placeholder input agar validasi tidak error --}}
        <input type="hidden" name="question_ids[]"
               value="">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-600
                        dark:text-gray-300 mb-1">
            Jumlah Soal
          </label>
          <input type="number" name="total_questions"
                 value="{{ old('total_questions', 10) }}"
                 min="1" max="50"
                 class="w-full px-3 py-2 rounded-lg border
                        border-gray-200 dark:border-gray-600
                        bg-white dark:bg-gray-700 text-sm
                        text-gray-900 dark:text-white
                        focus:ring-2 focus:ring-purple-500
                        outline-none">
        </div>
        <div>
          <label class="block text-sm text-gray-600
                        dark:text-gray-300 mb-1">
            Waktu per Soal
            <span class="text-gray-400 text-xs">(detik)</span>
          </label>
          <input type="number" name="duration_per_question"
                 value="{{ old('duration_per_question', 20) }}"
                 min="5" max="120"
                 class="w-full px-3 py-2 rounded-lg border
                        border-gray-200 dark:border-gray-600
                        bg-white dark:bg-gray-700 text-sm
                        text-gray-900 dark:text-white
                        focus:ring-2 focus:ring-purple-500
                        outline-none">
        </div>
      </div>


    </div>

    {{-- Reward Pemenang --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl
                border border-gray-200 dark:border-gray-700
                p-5 space-y-5"
         x-data="{
           hasTheme: {{ old('reward_rank1_theme_id') || old('reward_rank2_theme_id') || old('reward_rank3_theme_id') || old('reward_participant_theme_id') ? 'true' : 'false' }},
           hasPhysical: {{ old('reward_physical') ? 'true' : 'false' }},
           selectedThemes: {
             rank1: '{{ old('reward_rank1_theme_id', '') }}',
             rank2: '{{ old('reward_rank2_theme_id', '') }}',
             rank3: '{{ old('reward_rank3_theme_id', '') }}',
             participant: '{{ old('reward_participant_theme_id', '') }}'
           }
         }">

      <h2 class="font-medium text-gray-800
                 dark:text-white text-sm">
        Reward Pemenang
      </h2>

      {{-- EXP per rank --}}
      <div class="grid grid-cols-3 gap-3">
        @foreach([
          ['reward_rank1_exp', '🥇 Juara 1', 500],
          ['reward_rank2_exp', '🥈 Juara 2', 300],
          ['reward_rank3_exp', '🥉 Juara 3', 150],
        ] as [$name, $label, $default])
        <div>
          <label class="block text-xs text-gray-500
                        dark:text-gray-400 mb-1">
            {{ $label }}
            <span class="text-gray-400">(EXP)</span>
          </label>
          <input type="number" name="{{ $name }}"
                 value="{{ old($name, $default) }}"
                 min="0" max="9999"
                 class="w-full px-3 py-2 rounded-lg border
                        border-gray-200 dark:border-gray-600
                        bg-white dark:bg-gray-700 text-sm
                        text-gray-900 dark:text-white
                        focus:ring-2 focus:ring-purple-500
                        outline-none">
        </div>
        @endforeach
      </div>

      {{-- Toggle tema --}}
      <div>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600
                      dark:text-gray-300">
              Reward Tema Eksklusif
            </p>
            <p class="text-xs text-gray-400 mt-0.5">
              Pemenang mendapat akses tema selama 30 hari
            </p>
          </div>
          <button type="button"
                  @click="hasTheme = !hasTheme"
                  :class="hasTheme
                    ? 'bg-purple-600'
                    : 'bg-gray-200 dark:bg-gray-600'"
                  class="relative inline-flex h-6 w-11
                         items-center rounded-full
                         transition-colors focus:outline-none
                         shrink-0 ml-4">
            <span :class="hasTheme
                    ? 'translate-x-6' : 'translate-x-1'"
                  class="inline-block h-4 w-4 rounded-full
                         bg-white shadow transform
                         transition-transform"></span>
          </button>
        </div>

        {{-- Grid tema per rank --}}
        <div x-show="hasTheme" x-cloak x-transition
             class="mt-4 space-y-4">

          @php
            $rankLabels = [
              'rank1'       => ['🥇 Juara 1', 'reward_rank1_theme_id'],
              'rank2'       => ['🥈 Juara 2', 'reward_rank2_theme_id'],
              'rank3'       => ['🥉 Juara 3', 'reward_rank3_theme_id'],
              'participant' => ['👥 Partisipan', 'reward_participant_theme_id'],
            ];
          @endphp

          @foreach($rankLabels as $rankKey => [$rankLabel, $fieldName])
          <div>
            <p class="text-xs font-medium text-gray-600
                       dark:text-gray-300 mb-2">
              {{ $rankLabel }}
              <span class="text-gray-400 font-normal">
                — pilih tema (opsional)
              </span>
            </p>

            <div class="flex flex-wrap gap-2">

              {{-- Opsi "Tidak ada" --}}
              <label class="cursor-pointer"
                     @click="selectedThemes.{{ $rankKey }} = ''">
                <input type="radio"
                       name="{{ $fieldName }}"
                       value=""
                       x-model="selectedThemes.{{ $rankKey }}"
                       class="sr-only">
                <div class="px-3 py-2 rounded-lg border-2
                            text-xs transition-all"
                     :class="selectedThemes.{{ $rankKey }} === ''
                       ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300'
                       : 'border-gray-200 dark:border-gray-600 text-gray-500 hover:border-gray-300'">
                  Tidak ada
                </div>
              </label>

              {{-- Tema-tema --}}
              @foreach($themes as $theme)
              <label class="cursor-pointer"
                     @click="selectedThemes.{{ $rankKey }} = '{{ $theme->id }}'">
                <input type="radio"
                       name="{{ $fieldName }}"
                       value="{{ $theme->id }}"
                       x-model="selectedThemes.{{ $rankKey }}"
                       {{ old($fieldName) == $theme->id ? 'checked' : '' }}
                       class="sr-only">
                <div class="flex items-center gap-2 px-3 py-2
                            rounded-lg border-2 transition-all"
                     :class="selectedThemes.{{ $rankKey }} == '{{ $theme->id }}'
                       ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                       : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'">

                  {{-- Color dot --}}
                  <div class="w-4 h-4 rounded-full shrink-0
                              border border-white/20 shadow-sm"
                       style="background-color: {{ $theme->primary_color }}">
                  </div>

                  <span class="text-xs font-medium text-gray-700
                               dark:text-gray-200 whitespace-nowrap">
                    {{ $theme->name }}
                  </span>

                  @if($theme->min_level > 0)
                  <span class="text-[10px] text-gray-400">
                    Lv.{{ $theme->min_level }}+
                  </span>
                  @endif
                </div>
              </label>
              @endforeach
            </div>
          </div>
          @endforeach

        </div>

        {{-- Jika toggle OFF, kirim null untuk semua tema --}}
        <template x-if="!hasTheme">
          <div>
            <input type="hidden" name="reward_rank1_theme_id" value="">
            <input type="hidden" name="reward_rank2_theme_id" value="">
            <input type="hidden" name="reward_rank3_theme_id" value="">
            <input type="hidden" name="reward_participant_theme_id" value="">
          </div>
        </template>
      </div>

      {{-- Toggle hadiah fisik --}}
      <div x-data="{ hasPhysical: {{ old('reward_rank1_physical') || old('reward_rank2_physical') || old('reward_rank3_physical') ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              Hadiah Fisik
            </p>
            <p class="text-xs text-gray-400 mt-0.5">
              Opsional — contoh: voucher, hadiah langsung
            </p>
          </div>
          <button type="button"
                  @click="hasPhysical = !hasPhysical"
                  :class="hasPhysical
                    ? 'bg-purple-600'
                    : 'bg-gray-200 dark:bg-gray-600'"
                  class="relative inline-flex h-6 w-11
                         items-center rounded-full
                         transition-colors focus:outline-none
                         shrink-0 ml-4">
            <span :class="hasPhysical
                    ? 'translate-x-6' : 'translate-x-1'"
                  class="inline-block h-4 w-4 rounded-full
                         bg-white shadow transform
                         transition-transform"></span>
          </button>
        </div>
        <div x-show="hasPhysical" x-cloak x-transition
             class="mt-3 space-y-3">
          @foreach([
            ['reward_rank1_physical', '🥇 Juara 1'],
            ['reward_rank2_physical', '🥈 Juara 2'],
            ['reward_rank3_physical', '🥉 Juara 3'],
          ] as [$name, $label])
          <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-gray-400 w-20 shrink-0">{{ $label }}</span>
            <input type="text" name="{{ $name }}"
                   value="{{ old($name) }}"
                   placeholder="contoh: Voucher Kantin Rp 10.000"
                   class="flex-1 px-3 py-1.5 rounded-lg border
                          border-gray-200 dark:border-gray-600
                          bg-white dark:bg-gray-700 text-xs
                          text-gray-900 dark:text-white
                          focus:ring-2 focus:ring-purple-500
                          outline-none">
          </div>
          @endforeach
        </div>
      </div>

    </div>


    <div class="flex items-center justify-end gap-3 pt-1">
      <a href="{{ route('admin.gamification.arena.index') }}"
         class="px-4 py-2 text-sm text-gray-500
                hover:text-gray-700 transition-colors">
        Batal
      </a>
      <button type="submit"
              class="px-6 py-2 rounded-lg bg-purple-600
                     text-white text-sm font-medium
                     hover:bg-purple-700 transition-colors">
        Buat Room
      </button>
    </div>
  </form>
</div>

<script>
function createRoom() {
  return {
    mode: '{{ old('mode', 'individual') }}',
    sourceType: '{{ old('source_type', 'exam') }}',
    examPreview: null,

    async fetchExamPreview(examId) {
      if (!examId) {
        this.examPreview = null;
        return;
      }
      try {
        const url = new URL(
          '{{ route('admin.gamification.arena.exam.preview') }}',
          window.location.origin
        );
        url.searchParams.set('exam_id', examId);
        const res = await fetch(url.toString(), {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          }
        });
        this.examPreview = await res.json();
      } catch (e) {
        this.examPreview = null;
      }
    }
  }
}
</script>
@endsection
