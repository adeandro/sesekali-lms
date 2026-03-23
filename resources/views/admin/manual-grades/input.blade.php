@extends('layouts.app')

@section('title', 'Input Nilai Manual - ' . ($configs['school_name'] ?? 'SesekaliCBT'))
@section('page-title', 'Input Nilai Manual')

@section('content')
<div class="space-y-8 animate-fadeIn pb-12">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                <i class="fas fa-edit text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Input Nilai Manual</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Harian · UTS · UAS per mapel per kelas per semester</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.manual-grades.import-form') }}" class="inline-flex items-center gap-2 h-10 px-5 bg-gray-100 text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-200 transition">
                <i class="fas fa-file-excel text-sm"></i> Import Excel
            </a>
            <a href="{{ route('admin.manual-grades.index') }}" class="inline-flex items-center gap-2 h-10 px-5 bg-gray-100 text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-200 transition">
                <i class="fas fa-list"></i> Semua Nilai
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="fas fa-filter text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Filter Kelas & Mapel</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pilih kelas, mapel, dan periode untuk menampilkan tabel nilai</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.manual-grades.input') }}" class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Kelas --}}
                <div class="space-y-3">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kelas <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="class_id" class="w-full h-12 bg-gray-50 border-transparent rounded-xl px-4 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $selectedClass?->id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Mapel --}}
                <div class="space-y-3">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="subject_id" class="w-full h-12 bg-gray-50 border-transparent rounded-xl px-4 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                            <option value="">Pilih Mapel</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ $selectedSubject?->id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Semester --}}
                <div class="space-y-3">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Semester <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="semester" class="w-full h-12 bg-gray-50 border-transparent rounded-xl px-4 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                            <option value="">Pilih Semester</option>
                            <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                            <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Tahun Ajaran --}}
                <div class="space-y-3">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="academic_year" value="{{ $academicYear ?? ($configs['academic_year'] ?? '') }}"
                        placeholder="2024/2025"
                        class="w-full h-12 bg-gray-50 border-transparent rounded-xl px-4 text-sm font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-gray-300">
                </div>
            </div>
            <div class="mt-6 flex items-center gap-4">
                <button type="submit" class="h-12 px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-2">
                    <i class="fas fa-eye"></i> Tampilkan Nilai
                </button>
                @if($selectedSubject && $selectedClass && $semester && $academicYear)
                    <a href="{{ route('admin.manual-grades.download-template', ['subject_id' => $selectedSubject->id, 'class_id' => $selectedClass->id, 'semester' => $semester, 'academic_year' => $academicYear]) }}"
                       class="h-12 px-6 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-100 transition flex items-center gap-2">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    @if($selectedSubject && $selectedClass && $semester && $academicYear)
        @php
            $wHarian = $weight?->weight_harian ?? 40;
            $wUts    = $weight?->weight_uts    ?? 30;
            $wUas    = $weight?->weight_uas    ?? 30;
            $isLocked = \App\Models\GradeLock::isLocked(
                $selectedSubject->id, $semester, $academicYear
            );
        @endphp

        {{-- Bobot Info --}}
        @if($weight)
            <div class="flex flex-wrap items-center gap-4 px-6 py-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="text-sm font-black text-indigo-800">
                    Bobot Nilai:
                    <span class="ml-2 inline-flex items-center px-3 py-1 bg-white rounded-full text-indigo-700 text-[10px] font-black border border-indigo-200">Harian {{ number_format($wHarian, 0) }}%</span>
                    <span class="ml-1 inline-flex items-center px-3 py-1 bg-white rounded-full text-indigo-700 text-[10px] font-black border border-indigo-200">UTS {{ number_format($wUts, 0) }}%</span>
                    <span class="ml-1 inline-flex items-center px-3 py-1 bg-white rounded-full text-indigo-700 text-[10px] font-black border border-indigo-200">UAS {{ number_format($wUas, 0) }}%</span>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3 px-6 py-4 bg-amber-50 rounded-2xl border border-amber-100">
                <i class="fas fa-exclamation-triangle text-amber-500"></i>
                <p class="text-sm font-bold text-amber-700">
                    Bobot nilai belum dikonfigurasi untuk kelas ini. Menggunakan default:
                    <strong>Harian 40% · UTS 30% · UAS 30%</strong>
                </p>
                <a href="{{ route('admin.grade-weights.create') }}" class="ml-auto text-[10px] font-black uppercase tracking-widest text-amber-700 hover:underline whitespace-nowrap">Konfigurasi →</a>
            </div>
        @endif

        @if(session('success'))
            <div class="flex items-center gap-3 px-6 py-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <p class="text-sm font-bold text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if($isLocked)
        <div class="bg-amber-50 border border-amber-300 rounded-[1.5rem] p-6 mb-4 flex items-center gap-4 animate-fadeIn">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600 text-xl">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <p class="font-black text-amber-900 uppercase tracking-tight">Nilai Terkunci</p>
                <p class="text-xs font-bold text-amber-700 uppercase tracking-widest opacity-80 mt-1">
                    Nilai mapel ini sudah dikunci dan tidak dapat diubah. Klik "Buka Kunci Nilai" untuk mengedit.
                </p>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-table text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">{{ $selectedSubject->name }} — {{ $selectedClass->name }}</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Semester {{ $semester }} / {{ $academicYear }} · {{ count($students) }} Siswa</p>
                    </div>
                </div>
            </div>

            @if(count($students) === 0)
                <div class="p-16 text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mx-auto mb-4">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Tidak ada siswa aktif di kelas ini</p>
                </div>
            @else
                <form action="{{ route('admin.manual-grades.store') }}" method="POST"
                      x-data="{
                          wH: {{ $wHarian }},
                          wU: {{ $wUts }},
                          wA: {{ $wUas }},
                          calc(h, u, a) {
                              let hv = parseFloat(h)||0, uv = parseFloat(u)||0, av = parseFloat(a)||0;
                              if (h===''||u===''||a==='') return '—';
                              return ((hv*this.wH/100)+(uv*this.wU/100)+(av*this.wA/100)).toFixed(2);
                          }
                      }">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <input type="hidden" name="academic_year" value="{{ $academicYear }}">

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-50">
                                    <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">No</th>
                                    <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Nama Siswa</th>
                                    <th class="px-6 py-4 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">NIS</th>
                                    <th class="px-6 py-4 text-center text-[9px] font-black text-indigo-500 uppercase tracking-[0.15em]">Harian <span class="text-gray-400 font-normal">({{ number_format($wHarian,0) }}%)</span></th>
                                    <th class="px-6 py-4 text-center text-[9px] font-black text-indigo-500 uppercase tracking-[0.15em]">UTS <span class="text-gray-400 font-normal">({{ number_format($wUts,0) }}%)</span></th>
                                    <th class="px-6 py-4 text-center text-[9px] font-black text-indigo-500 uppercase tracking-[0.15em]">UAS <span class="text-gray-400 font-normal">({{ number_format($wUas,0) }}%)</span></th>
                                    <th class="px-6 py-4 text-center text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Nilai Akhir*</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($students as $i => $student)
                                @php
                                    $cbt    = $cbtGrades[$student->id]   ?? ['harian' => null, 'uts' => null, 'uas' => null];
                                    $manual = $manualGradesMap[$student->id] ?? ['harian' => '', 'uts' => '', 'uas' => ''];
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors"
                                    x-data="{
                                        h: '{{ $cbt['harian'] ?? $manual['harian'] }}',
                                        u: '{{ $cbt['uts'] ?? $manual['uts'] }}',
                                        a: '{{ $cbt['uas'] ?? $manual['uas'] }}',
                                        get final() {
                                            if (this.h===''||this.u===''||this.a==='') return '—';
                                            return ((parseFloat(this.h)||0)*{{ $wHarian }}/100 + (parseFloat(this.u)||0)*{{ $wUts }}/100 + (parseFloat(this.a)||0)*{{ $wUas }}/100).toFixed(2);
                                        }
                                    }">
                                    <td class="px-6 py-4 text-sm font-bold text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-black text-gray-900">{{ $student->name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold text-gray-500">{{ $student->nis ?? '—' }}</span>
                                    </td>

                                    {{-- Harian --}}
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            @if($cbt['harian'] !== null)
                                                <span class="text-[9px] font-black text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full uppercase tracking-widest">CBT: {{ number_format($cbt['harian'], 1) }}</span>
                                            @endif
                                                <input type="number" name="grades[{{ $student->id }}][harian]"
                                                x-model="h"
                                                value="{{ $cbt['harian'] ?? $manual['harian'] }}"
                                                min="0" max="100" step="0.5" placeholder="—"
                                                @if($cbt['harian'] !== null || $isLocked) disabled title="{{ $isLocked ? 'Nilai terkunci' : 'Nilai diambil dari CBT' }}" @endif
                                                class="w-20 h-10 text-center text-sm font-bold rounded-xl border-transparent transition-all
                                                       {{ ($cbt['harian'] !== null || $isLocked) ? 'bg-blue-50 text-blue-700 cursor-not-allowed' : 'bg-gray-50 text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10' }}">
                                        </div>
                                    </td>

                                    {{-- UTS --}}
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            @if($cbt['uts'] !== null)
                                                <span class="text-[9px] font-black text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full uppercase tracking-widest">CBT: {{ number_format($cbt['uts'], 1) }}</span>
                                            @endif
                                                <input type="number" name="grades[{{ $student->id }}][uts]"
                                                x-model="u"
                                                value="{{ $cbt['uts'] ?? $manual['uts'] }}"
                                                min="0" max="100" step="0.5" placeholder="—"
                                                @if($cbt['uts'] !== null || $isLocked) disabled title="{{ $isLocked ? 'Nilai terkunci' : 'Nilai diambil dari CBT' }}" @endif
                                                class="w-20 h-10 text-center text-sm font-bold rounded-xl border-transparent transition-all
                                                       {{ ($cbt['uts'] !== null || $isLocked) ? 'bg-blue-50 text-blue-700 cursor-not-allowed' : 'bg-gray-50 text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10' }}">
                                        </div>
                                    </td>

                                    {{-- UAS --}}
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            @if($cbt['uas'] !== null)
                                                <span class="text-[9px] font-black text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full uppercase tracking-widest">CBT: {{ number_format($cbt['uas'], 1) }}</span>
                                            @endif
                                                <input type="number" name="grades[{{ $student->id }}][uas]"
                                                x-model="a"
                                                value="{{ $cbt['uas'] ?? $manual['uas'] }}"
                                                min="0" max="100" step="0.5" placeholder="—"
                                                @if($cbt['uas'] !== null || $isLocked) disabled title="{{ $isLocked ? 'Nilai terkunci' : 'Nilai diambil dari CBT' }}" @endif
                                                class="w-20 h-10 text-center text-sm font-bold rounded-xl border-transparent transition-all
                                                       {{ ($cbt['uas'] !== null || $isLocked) ? 'bg-blue-50 text-blue-700 cursor-not-allowed' : 'bg-gray-50 text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10' }}">
                                        </div>
                                    </td>

                                    {{-- Nilai Akhir (Alpine.js live) --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-base font-black"
                                              :class="final === '—' ? 'text-gray-300' : 'text-indigo-600'"
                                              x-text="final"></span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-50 flex items-center justify-between gap-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                            * Nilai Akhir = estimasi realtime. Nilai CBT tidak bisa dioverride oleh nilai manual.
                        </p>
                        <div class="flex items-center gap-3">
                            <button type="button"
                                id="btn-lock-toggle"
                                onclick="toggleLock()"
                                class="h-12 px-6 {{ $isLocked ? 'bg-amber-500 hover:bg-amber-600' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition shadow-lg shadow-amber-100 flex items-center gap-2">
                                <i class="fas {{ $isLocked ? 'fa-unlock' : 'fa-lock' }}"></i>
                                <span id="lock-label">{{ $isLocked ? 'Buka Kunci Nilai' : 'Kunci Nilai' }}</span>
                            </button>

                            @if(!$isLocked)
                            <button type="submit"
                                class="h-12 px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-2">
                                <i class="fas fa-save"></i> Simpan Nilai
                            </button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </div>
    @else
        {{-- Placeholder: belum filter --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-16 text-center">
            <div class="w-20 h-20 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-300 mx-auto mb-6">
                <i class="fas fa-filter text-3xl"></i>
            </div>
            <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Pilih kelas, mapel, dan periode untuk menampilkan tabel input nilai</p>
        </div>
    @endif
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

@if($selectedSubject && $selectedClass && $semester && $academicYear)
<script>
    async function toggleLock() {
        const btn = document.getElementById('btn-lock-toggle');
        const label = document.getElementById('lock-label');
        const isCurrentlyLocked = label.innerText.includes('Buka');

        const result = await Swal.fire({
            title: isCurrentlyLocked ? 'Buka Kunci Nilai?' : 'Kunci Nilai?',
            text: isCurrentlyLocked 
                ? 'Guru akan dapat mengubah nilai kembali setelah kunci dibuka.' 
                : 'Setelah dikunci, nilai tidak dapat diubah kecuali kunci dibuka kembali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: isCurrentlyLocked ? '#f59e0b' : '#059669',
            cancelButtonColor: '#6b7280',
            confirmButtonText: isCurrentlyLocked ? 'Ya, Buka Kunci' : 'Ya, Kunci Sekarang',
            cancelButtonText: 'Batal',
            background: '#ffffff',
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl px-6 py-3 text-[10px] font-black uppercase tracking-widest',
                cancelButton: 'rounded-xl px-6 py-3 text-[10px] font-black uppercase tracking-widest'
            }
        });

        if (!result.isConfirmed) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

        try {
            const res = await fetch('{{ route("admin.grade-locks.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    subject_id:    {{ $selectedSubject->id }},
                    semester:      {{ $semester }},
                    academic_year: '{{ $academicYear }}',
                }),
            });

            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: { popup: 'rounded-[2rem]' }
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Gagal mengubah status kunci.',
                    customClass: { popup: 'rounded-[2rem]' }
                });
                btn.disabled = false;
                btn.innerHTML = `<i class="fas ${isCurrentlyLocked ? 'fa-unlock' : 'fa-lock'}"></i> ${label.innerText}`;
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan sistem atau koneksi.',
                customClass: { popup: 'rounded-[2rem]' }
            });
            btn.disabled = false;
            btn.innerHTML = `<i class="fas ${isCurrentlyLocked ? 'fa-unlock' : 'fa-lock'}"></i> ${label.innerText}`;
        }
    }
</script>
@endif
@endsection
