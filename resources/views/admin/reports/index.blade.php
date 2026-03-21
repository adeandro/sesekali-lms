@extends('layouts.app')

@section('title', 'Raport Siswa')

@section('content')
<div class="space-y-6">

    {{-- ── Page Header ────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Raport Siswa</h1>
            <p class="text-sm text-gray-500 mt-1">Cetak dan kelola laporan hasil belajar siswa per semester</p>
        </div>
    </div>

    {{-- ── Filter Form ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-sm font-black uppercase tracking-widest text-gray-500 mb-4">Filter Raport</h2>
        <form method="GET" action="{{ route('admin.reports.index') }}" class="no-loading">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Kelas --}}
                @if(auth()->user()->role === 'teacher' && $classes->count() === 1)
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Kelas Anda</label>
                        <div class="w-full bg-indigo-50 border border-indigo-100 rounded-xl px-3 py-2.5 text-sm font-black text-indigo-700 uppercase tracking-tight">
                            {{ $classes->first()->name }}
                        </div>
                        <input type="hidden" name="class_id" value="{{ $classes->first()->id }}">
                    </div>
                @elseif($classes->isEmpty())
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider text-rose-500">Akses</label>
                        <div class="w-full bg-rose-50 border border-rose-100 rounded-xl px-3 py-2.5 text-[10px] font-black text-rose-600 uppercase tracking-widest">
                            Non-Wali Kelas
                        </div>
                    </div>
                @else
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Kelas</label>
                        <select name="class_id"
                                onchange="this.form.submit()"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] cursor-pointer">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Semester --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Semester</label>
                    <select name="semester"
                            onchange="this.form.submit()"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
                        <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Ganjil (1)</option>
                        <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Genap (2)</option>
                    </select>
                </div>

                {{-- Tahun Ajaran --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Tahun Ajaran</label>
                    <input type="text" name="academic_year"
                           value="{{ $academicYear }}"
                           placeholder="2024/2025"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
                </div>

                {{-- Jenjang --}}
                @if(!$class)
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Jenjang</label>
                    <select name="jenjang"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
                        <option value="10" {{ $jenjang == 10 ? 'selected' : '' }}>Kelas X (10)</option>
                        <option value="11" {{ $jenjang == 11 ? 'selected' : '' }}>Kelas XI (11)</option>
                        <option value="12" {{ $jenjang == 12 ? 'selected' : '' }}>Kelas XII (12)</option>
                    </select>
                </div>
                @else
                   <input type="hidden" name="jenjang" value="{{ $jenjang }}">
                @endif

                {{-- Jenis Raport --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Jenis Raport</label>
                    <select name="report_type"
                            onchange="this.form.submit()"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
                        <option value="semester" {{ $reportType == 'semester' ? 'selected' : '' }}>Penilaian Akhir Semester</option>
                        <option value="mid" {{ $reportType == 'mid' ? 'selected' : '' }}>Penilaian Tengah Semester</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[var(--brand-primary)] text-white text-sm font-bold rounded-xl hover:opacity-90 transition-all">
                    <i class="fas fa-search"></i>
                    Tampilkan
                </button>
            </div>
        </form>
    </div>

    {{-- ── Tabel Daftar Siswa ──────────────────────────────────────── --}}
    @if($class)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Header tabel + tombol bulk --}}
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-gray-900">{{ $class->name }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Semester {{ $semester == 1 ? 'Ganjil' : 'Genap' }} &bull; {{ $academicYear }} &bull; {{ $students->count() }} siswa
                    </p>
                </div>
                <a href="{{ route('admin.reports.printClass', $class->id) . '?' . http_build_query(['semester' => $semester, 'academic_year' => $academicYear, 'report_type' => $reportType]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-gray-700 transition-all whitespace-nowrap">
                    <i class="fas fa-print"></i>
                    Cetak Semua Siswa
                </a>
            </div>

            @if($reportSummary)
                <div x-data="{ openNote: null }">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500 w-8">No</th>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">Nama Siswa</th>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-500">NIS</th>
                                <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-wider text-gray-500">Status Nilai</th>
                                <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-wider text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($reportSummary as $idx => $item)
                                @php $student = $item['student']; @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    {{-- No --}}
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $idx + 1 }}</td>

                                    {{-- Nama --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-[var(--brand-glow)] flex-shrink-0">
                                                <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">{{ $student->full_name }}</span>
                                        </div>
                                    </td>

                                    {{-- NIS --}}
                                    <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $student->nis ?? '-' }}</td>

                                    {{-- Status --}}
                                    <td class="px-4 py-3 text-center">
                                        @if($item['is_complete'])
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100">
                                                <i class="fas fa-check-circle text-[10px]"></i> Lengkap
                                            </span>
                                        @elseif($item['has_any'])
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-100">
                                                <i class="fas fa-exclamation-circle text-[10px]"></i> Sebagian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-50 text-gray-500 text-xs font-bold rounded-lg border border-gray-100">
                                                <i class="fas fa-times-circle text-[10px]"></i> Belum Ada
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($item['has_any'])
                                                {{-- Preview --}}
                                                <a href="{{ route('admin.reports.preview', $student->id) . '?' . http_build_query(['semester' => $semester, 'academic_year' => $academicYear, 'report_type' => $reportType]) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg hover:bg-indigo-100 transition-colors">
                                                    <i class="fas fa-eye text-[10px]"></i> Preview
                                                </a>

                                                {{-- Cetak --}}
                                                <a href="{{ route('admin.reports.printSingle', $student->id) . '?' . http_build_query(['semester' => $semester, 'academic_year' => $academicYear, 'report_type' => $reportType]) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-800 text-white text-xs font-bold rounded-lg hover:bg-gray-600 transition-colors">
                                                    <i class="fas fa-print text-[10px]"></i> Cetak
                                                </a>
                                            @else
                                                {{-- Preview Disabled --}}
                                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-400 text-xs font-bold rounded-lg cursor-not-allowed" title="Belum ada data nilai">
                                                    <i class="fas fa-eye text-[10px]"></i> Preview
                                                </span>

                                                {{-- Cetak Disabled --}}
                                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-200 text-gray-400 text-xs font-bold rounded-lg cursor-not-allowed" title="Belum ada data nilai">
                                                    <i class="fas fa-print text-[10px]"></i> Cetak
                                                </span>
                                            @endif

                                            {{-- Toggle Catatan --}}
                                            <button @click="openNote = (openNote === {{ $student->id }}) ? null : {{ $student->id }}"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg hover:bg-emerald-100 transition-colors">
                                                <i class="fas fa-pen text-[10px]"></i> Catatan
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Form Catatan Wali Kelas (collapsible) --}}
                                <tr x-show="openNote === {{ $student->id }}"
                                    x-cloak
                                    x-collapse
                                    class="bg-emerald-50/50">
                                    <td colspan="5" class="px-4 py-4">
                                        <form action="{{ route('admin.reports.notes') }}"
                                              method="POST"
                                              class="no-loading flex flex-col gap-3">
                                            @csrf
                                            <input type="hidden" name="student_id"    value="{{ $student->id }}">
                                            <input type="hidden" name="semester"      value="{{ $semester }}">
                                            <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                                            <input type="hidden" name="class_id"      value="{{ $class->id }}">

                                            <div>
                                                <label class="block text-xs font-bold text-gray-600 mb-1">
                                                    Catatan Wali Kelas untuk <strong>{{ $student->full_name }}</strong>
                                                </label>
                                                @php
                                                    $existingNote = \App\Models\ReportNote::where('student_id', $student->id)
                                                        ->where('semester', $semester)
                                                        ->where('academic_year', $academicYear)
                                                        ->first();
                                                @endphp
                                                <textarea name="note" rows="3"
                                                          placeholder="Tuliskan catatan wali kelas di sini..."
                                                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 resize-none focus:outline-none focus:ring-2 focus:ring-emerald-400">{{ $existingNote?->note }}</textarea>
                                            </div>

                                            <div class="flex gap-2">
                                                <button type="submit"
                                                        class="px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-colors">
                                                    <i class="fas fa-save mr-1"></i> Simpan Catatan
                                                </button>
                                                <button type="button"
                                                        @click="openNote = null"
                                                        class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-200 transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mb-4">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Tidak ada siswa di kelas ini</p>
                </div>
            @endif
        </div>

    @else
        {{-- Belum pilih kelas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-[var(--brand-glow)] flex items-center justify-center mb-4">
                <i class="fas fa-file-invoice text-2xl text-[var(--brand-primary)]"></i>
            </div>
            <h3 class="text-base font-black text-gray-700 mb-1">Pilih kelas untuk memulai</h3>
            <p class="text-sm text-gray-400 max-w-sm">Gunakan filter di atas untuk memilih kelas, semester, dan tahun ajaran yang ingin ditampilkan.</p>
        </div>
    @endif

</div>
@endsection
