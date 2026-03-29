@extends('layouts.app')
@section('title', 'Dashboard Kepala Sekolah')
@section('content')
<div class="space-y-8 pb-12">

  {{-- ── Hero ── --}}
  <div class="relative overflow-hidden rounded-[3rem] p-10 text-white shadow-2xl"
       style="background: linear-gradient(135deg,
           var(--brand-primary), var(--brand-dark));">
    <div class="absolute top-0 right-0 -mr-20 -mt-20
                w-80 h-80 bg-white/10 rounded-full
                blur-3xl pointer-events-none"></div>
    <div class="relative flex flex-col md:flex-row
                items-center justify-between gap-8">
      <div class="space-y-3">
        <p class="text-[10px] font-black uppercase
                  tracking-[0.4em] opacity-70">
          Dashboard Kepala Sekolah
        </p>
        <h1 class="text-3xl md:text-4xl font-black
                   uppercase tracking-wider leading-tight">
          Selamat Datang,<br>
          {{ auth()->user()->name }}
        </h1>
        <p class="text-sm font-bold opacity-80">
          Semester {{ $semester == 1 ? 'Ganjil' : 'Genap' }}
          &bull; {{ $academicYear }}
        </p>
      </div>
      <div class="hidden md:block">
        <div class="w-36 h-36 bg-white/10 backdrop-blur-xl
                    border border-white/20 rounded-[3rem]
                    flex items-center justify-center
                    shadow-2xl rotate-3">
          <i class="fas fa-school text-6xl
                    text-white opacity-40"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Ringkasan Hari Ini ── --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @php
      $summaryCards = [
        [
          'label' => 'Total Kelas',
          'value' => $schoolStats['total_classes'],
          'icon'  => 'fa-school',
          'color' => 'indigo',
        ],
        [
          'label' => 'Total Siswa',
          'value' => $schoolStats['total_students'],
          'icon'  => 'fa-users',
          'color' => 'emerald',
        ],
        [
          'label' => 'Ujian Aktif',
          'value' => $activeExams,
          'icon'  => 'fa-file-alt',
          'color' => 'amber',
        ],
        [
          'label' => 'Sedang Ujian',
          'value' => $studentsInExam,
          'icon'  => 'fa-pen-to-square',
          'color' => 'rose',
        ],
      ];
      $colorMap = [
        'indigo' => ['bg'=>'bg-indigo-50','text'=>'text-indigo-600','border'=>'border-indigo-100'],
        'emerald'=> ['bg'=>'bg-emerald-50','text'=>'text-emerald-600','border'=>'border-emerald-100'],
        'amber'  => ['bg'=>'bg-amber-50','text'=>'text-amber-600','border'=>'border-amber-100'],
        'rose'   => ['bg'=>'bg-rose-50','text'=>'text-rose-600','border'=>'border-rose-100'],
      ];
    @endphp
    @foreach($summaryCards as $card)
    @php $c = $colorMap[$card['color']]; @endphp
    <div class="bg-white rounded-[2rem] p-6 border
                {{ $c['border'] }} shadow-sm
                hover:shadow-md transition-shadow">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 {{ $c['bg'] }} {{ $c['text'] }}
                    rounded-2xl flex items-center
                    justify-center text-xl flex-shrink-0">
          <i class="fas {{ $card['icon'] }}"></i>
        </div>
        <div>
          <p class="text-[9px] font-black text-gray-400
                    uppercase tracking-widest">
            {{ $card['label'] }}
          </p>
          <p class="text-2xl font-black text-gray-900
                    tracking-tight">
            {{ $card['value'] }}
          </p>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- ── Progress Nilai Per Kelas ── --}}
  <div class="bg-white rounded-[2.5rem] border
              border-gray-100 shadow-sm overflow-hidden">
    <div class="px-8 py-6 border-b border-gray-50
                flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-50 text-indigo-600
                    rounded-xl flex items-center
                    justify-center">
          <i class="fas fa-tasks text-sm"></i>
        </div>
        <div>
          <h3 class="font-black text-gray-900
                     uppercase tracking-tight">
            Progress Input Nilai
          </h3>
          <p class="text-[10px] font-bold text-gray-400
                    uppercase tracking-widest">
            Status kunci nilai per kelas
          </p>
        </div>
      </div>
    </div>

    <div class="divide-y divide-gray-50">
      @forelse($classProgress as $cp)
      <div class="px-8 py-4 flex items-center gap-6
                  hover:bg-gray-50/50 transition-colors">
        {{-- Nama kelas --}}
        <div class="w-24 flex-shrink-0">
          <p class="text-sm font-black text-gray-900">
            {{ $cp['class']->name }}
          </p>
          <p class="text-[9px] text-gray-400 font-bold
                    truncate">
            {{ $cp['homeroom_name'] }}
          </p>
        </div>

        {{-- Progress bar --}}
        <div class="flex-1">
          <div class="flex items-center
                      justify-between mb-1">
            <span class="text-[9px] font-black
                         text-gray-500 uppercase
                         tracking-widest">
              {{ $cp['locked_subjects'] }}/{{ $cp['total_subjects'] }}
              mapel terkunci
            </span>
            <span class="text-[9px] font-black
                         {{ $cp['pct'] == 100
                             ? 'text-emerald-600'
                             : ($cp['pct'] > 50
                                 ? 'text-amber-600'
                                 : 'text-rose-600') }}">
              {{ $cp['pct'] }}%
            </span>
          </div>
          <div class="h-2 bg-gray-100 rounded-full
                      overflow-hidden">
            <div class="h-full rounded-full transition-all
                        {{ $cp['pct'] == 100
                            ? 'bg-emerald-500'
                            : ($cp['pct'] > 50
                                ? 'bg-amber-500'
                                : 'bg-rose-500') }}"
                 style="width: {{ $cp['pct'] }}%">
            </div>
          </div>
        </div>

        {{-- Badge status --}}
        <div class="flex-shrink-0">
          @if($cp['pct'] == 100)
            <span class="inline-flex items-center gap-1
                         px-3 py-1 bg-emerald-50
                         text-emerald-700 text-[9px]
                         font-black rounded-lg
                         border border-emerald-100">
              <i class="fas fa-check-circle text-[8px]"></i>
              Lengkap
            </span>
          @elseif($cp['pct'] > 0)
            <span class="inline-flex items-center gap-1
                         px-3 py-1 bg-amber-50
                         text-amber-700 text-[9px]
                         font-black rounded-lg
                         border border-amber-100">
              <i class="fas fa-clock text-[8px]"></i>
              Proses
            </span>
          @else
            <span class="inline-flex items-center gap-1
                         px-3 py-1 bg-rose-50
                         text-rose-700 text-[9px]
                         font-black rounded-lg
                         border border-rose-100">
              <i class="fas fa-times-circle text-[8px]"></i>
              Belum
            </span>
          @endif
        </div>

        {{-- Aksi: lihat raport kelas --}}
        <a href="{{ route('admin.reports.index',
               ['class_id' => $cp['class']->id,
                'semester' => $semester,
                'academic_year' => $academicYear]) }}"
           class="flex-shrink-0 text-[9px] font-black
                  text-indigo-600 hover:underline
                  uppercase tracking-widest">
          Lihat →
        </a>
      </div>
      @empty
      <div class="px-8 py-12 text-center text-gray-400">
        <i class="fas fa-school text-2xl mb-2"></i>
        <p class="text-sm font-bold">Belum ada kelas</p>
      </div>
      @endforelse
    </div>
  </div>

  {{-- ── Grid: Siswa At-Risk + Mapel Lemah ── --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Siswa Perlu Perhatian --}}
    <div class="bg-white rounded-[2.5rem] border
                border-gray-100 shadow-sm overflow-hidden">
      <div class="px-8 py-6 border-b border-gray-50
                  flex items-center gap-3">
        <div class="w-10 h-10 bg-rose-50 text-rose-600
                    rounded-xl flex items-center
                    justify-center">
          <i class="fas fa-exclamation-triangle text-sm"></i>
        </div>
        <div>
          <h3 class="font-black text-gray-900
                     uppercase tracking-tight">
            Siswa Perlu Perhatian
          </h3>
          <p class="text-[10px] font-bold text-gray-400
                    uppercase tracking-widest">
            ≥2 mapel di bawah KKM
          </p>
        </div>
      </div>

      <div class="divide-y divide-gray-50 max-h-80
                  overflow-y-auto">
        @forelse($atRiskStudents as $item)
        <div class="px-8 py-3 flex items-center
                    justify-between hover:bg-gray-50/50">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full
                        bg-rose-50 flex items-center
                        justify-center text-rose-600
                        text-xs font-black flex-shrink-0">
              {{ $item['below_kkm'] }}
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">
                {{ $item['student']->name }}
              </p>
              <p class="text-[9px] text-gray-400 font-bold">
                {{ $item['class']->name }}
                &bull; NIS: {{ $item['student']->nis ?? '-' }}
              </p>
            </div>
          </div>
          <span class="text-[9px] font-black text-rose-600
                       bg-rose-50 px-2 py-1 rounded-lg
                       border border-rose-100">
            {{ $item['below_kkm'] }}/{{ $item['total'] }}
            di bawah KKM
          </span>
        </div>
        @empty
        <div class="px-8 py-12 text-center text-gray-400">
          <i class="fas fa-check-circle text-2xl
                    text-emerald-400 mb-2"></i>
          <p class="text-sm font-bold text-emerald-600">
            Semua siswa di atas KKM!
          </p>
        </div>
        @endforelse
      </div>
    </div>

    {{-- Mapel Rata-rata Terendah --}}
    <div class="bg-white rounded-[2.5rem] border
                border-gray-100 shadow-sm overflow-hidden">
      <div class="px-8 py-6 border-b border-gray-50
                  flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-50 text-amber-600
                    rounded-xl flex items-center
                    justify-center">
          <i class="fas fa-chart-bar text-sm"></i>
        </div>
        <div>
          <h3 class="font-black text-gray-900
                     uppercase tracking-tight">
            Mapel Rata-rata Terendah
          </h3>
          <p class="text-[10px] font-bold text-gray-400
                    uppercase tracking-widest">
            Berdasarkan nilai UAS/PAS
          </p>
        </div>
      </div>

      <div class="px-8 py-6 space-y-4">
        @forelse($subjectAverages as $sa)
        @php
          $avg = round($sa->avg_score, 1);
          $kkm = $sa->subject?->kkm ?? 75;
          $pct = min(100, round($avg));
          $color = $avg >= $kkm
              ? 'bg-emerald-500'
              : ($avg >= $kkm * 0.8
                  ? 'bg-amber-500'
                  : 'bg-rose-500');
        @endphp
        <div class="space-y-1">
          <div class="flex items-center
                      justify-between">
            <span class="text-xs font-bold text-gray-700">
              {{ $sa->subject?->name ?? 'Mapel Terhapus' }}
            </span>
            <span class="text-xs font-black
                         {{ $avg >= $kkm
                             ? 'text-emerald-600'
                             : 'text-rose-600' }}">
              {{ $avg }}
              <span class="text-[9px] text-gray-400
                           font-normal">
                / KKM {{ $kkm }}
              </span>
            </span>
          </div>
          <div class="h-2 bg-gray-100 rounded-full
                      overflow-hidden">
            <div class="h-full {{ $color }} rounded-full"
                 style="width: {{ $pct }}%"></div>
          </div>
          <p class="text-[9px] text-gray-400">
            {{ $sa->student_count }} siswa dinilai
          </p>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
          <p class="text-sm font-bold">
            Belum ada data nilai
          </p>
        </div>
        @endforelse
      </div>
    </div>

  </div>

</div>
@endsection
