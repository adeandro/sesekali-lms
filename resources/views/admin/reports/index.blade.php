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

    {{-- Progress Section --}}
    @if($class)
    <div id="grade-status-section"
         class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 animate-fadeIn"
         style="display:none;">

        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-tasks text-sm"></i>
                </div>
                <div>
                    <h3 class="font-black text-gray-900 uppercase tracking-tight">Status Kelengkapan Nilai</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest" id="progress-summary">Memuat status...</p>
                </div>
            </div>
            <div class="text-right">
                <span id="progress-pct" class="text-2xl font-black text-indigo-600">0%</span>
            </div>
        </div>

        {{-- Progress bar utama --}}
        <div class="bg-gray-100 rounded-full h-4 mb-2 overflow-hidden border border-gray-50">
            <div id="progress-bar-main"
                 class="bg-indigo-500 h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(99,102,241,0.3)]"
                 style="width: 0%"></div>
        </div>
        <div class="flex items-center justify-between">
            <p id="progress-label" class="text-[10px] font-black text-gray-400 uppercase tracking-widest">0 dari 0 mapel lengkap</p>
            <button onclick="showIncompleteModal()"
                    id="btn-show-incomplete"
                    class="text-[10px] font-black text-rose-500 uppercase tracking-widest hover:underline hidden">
                ⚠️ Lihat mapel belum lengkap
            </button>
        </div>

        {{-- Grid status per mapel --}}
        <div id="subject-status-grid"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-6 pt-6 border-t border-gray-50 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
            {{-- Diisi oleh JavaScript --}}
        </div>
    </div>

    {{-- Modal mapel belum lengkap --}}
    <div id="incomplete-modal"
         class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 animate-fadeIn">
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100">
            <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 uppercase tracking-tight">Mapel Belum Lengkap</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Segera lengkapi nilai berikut</p>
                    </div>
                </div>
                <button onclick="closeIncompleteModal()" class="w-8 h-8 rounded-full hover:bg-white flex items-center justify-center text-gray-400 transition-colors">✕</button>
            </div>
            <div id="incomplete-list" class="p-8 space-y-3 max-h-[60vh] overflow-y-auto">
                {{-- Diisi JavaScript --}}
            </div>
            <div class="p-6 bg-gray-50/50 text-center border-t border-gray-50">
                <button onclick="closeIncompleteModal()" class="h-10 px-8 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-gray-800 transition">Tutup</button>
            </div>
        </div>
    </div>
    @endif

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
                <div class="flex flex-col items-end gap-1">
                    <a href="{{ route('admin.reports.printClass', $class->id) . '?' . http_build_query(['semester' => $semester, 'academic_year' => $academicYear, 'report_type' => $reportType]) }}"
                       target="_blank"
                       id="btn-print-all"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-gray-700 transition-all whitespace-nowrap opacity-50 cursor-not-allowed"
                       onclick="return !this.hasAttribute('disabled')">
                        <i class="fas fa-print"></i>
                        Cetak Semua Siswa
                    </a>
                    <p id="print-warning" class="text-[9px] font-bold text-rose-500 uppercase tracking-tight hidden">
                        ⚠️ Nilai belum lengkap
                    </p>
                </div>
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

@section('scripts')
<script>
async function loadGradeStatus() {
    const classId      = document.querySelector('input[name="class_id"], select[name="class_id"]')?.value;
    const semester     = document.querySelector('select[name="semester"]')?.value;
    const academicYear = document.querySelector('input[name="academic_year"]')?.value;
    const reportType   = document.querySelector('select[name="report_type"]')?.value;

    if (!classId || !semester || !academicYear) return;

    const section = document.getElementById('grade-status-section');
    if(section) section.style.display = 'block';

    const params = new URLSearchParams({
        class_id: classId, 
        semester: semester, 
        academic_year: academicYear,
        report_type: reportType ?? 'semester',
    });

    try {
        const res  = await fetch(`/admin/grade-locks/status?${params}`);
        const data = await res.json();

        // Update progress bar
        const pct = data.total_subjects > 0
            ? Math.round((data.complete_subjects / data.total_subjects) * 100)
            : 0;

        const progressBar = document.getElementById('progress-bar-main');
        const progressPct = document.getElementById('progress-pct');
        const progressLabel = document.getElementById('progress-label');
        const progressSummary = document.getElementById('progress-summary');

        if(progressBar) progressBar.style.width = pct + '%';
        if(progressPct) progressPct.textContent = pct + '%';
        if(progressLabel) progressLabel.textContent = `${data.complete_subjects} dari ${data.total_subjects} mapel lengkap`;
        if(progressSummary) progressSummary.textContent = `${pct}% terkumpul`;

        // Render grid per mapel
        const grid = document.getElementById('subject-status-grid');
        if(grid) {
            grid.innerHTML = '';
            data.subjects.forEach(s => {
                const isComplete = s.is_complete;
                const colorClass = isComplete ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-rose-50 border-rose-100 text-rose-700';
                const dotColor   = isComplete ? 'bg-emerald-500' : 'bg-rose-500';
                const lockIcon   = s.is_locked ? '<i class="fas fa-lock text-[9px] ml-1"></i>' : '';
                
                grid.innerHTML += `
                    <div class="flex items-center justify-between border rounded-2xl px-4 py-3 ${colorClass} transition-all hover:scale-[1.02]">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-2 h-2 rounded-full ${dotColor} flex-shrink-0"></div>
                            <div class="overflow-hidden">
                                <p class="text-[11px] font-black uppercase tracking-tight truncate">${s.subject_name}${lockIcon}</p>
                                <p class="text-[9px] font-medium opacity-60 uppercase">${s.students_graded}/${s.students_total} Siswa</p>
                            </div>
                        </div>
                    </div>`;
            });
        }

        // Tombol lihat yang belum lengkap
        const hasIncomplete = !data.all_complete;
        const btnIncomplete = document.getElementById('btn-show-incomplete');
        if(btnIncomplete) btnIncomplete.classList.toggle('hidden', !hasIncomplete);

        // Enable/disable tombol cetak
        const isSuperadmin = {{ auth()->user()->role === 'superadmin' ? 'true' : 'false' }};
        const canPrint = isSuperadmin || data.all_complete;
        const btnPrint = document.getElementById('btn-print-all');
        const warning  = document.getElementById('print-warning');

        if(btnPrint) {
            if(canPrint) {
                btnPrint.removeAttribute('disabled');
                btnPrint.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                btnPrint.setAttribute('disabled', 'disabled');
                btnPrint.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
        if (warning) warning.classList.toggle('hidden', canPrint);

        // Simpan data untuk modal
        window._gradeStatusData = data;
    } catch(e) {
        console.error('Grade status load failed', e);
    }
}

function showIncompleteModal() {
    const data = window._gradeStatusData;
    if (!data) return;

    const incomplete = data.subjects.filter(s => !s.is_complete);
    const list = document.getElementById('incomplete-list');
    if(list) {
        list.innerHTML = incomplete.map(s => `
            <div class="flex items-center justify-between bg-rose-50 border border-rose-100 rounded-2xl px-5 py-4">
                <div>
                    <p class="font-black text-rose-900 text-xs uppercase tracking-tight">${s.subject_name}</p>
                    <p class="text-[10px] font-bold text-rose-600 uppercase tracking-widest mt-0.5">
                        ${s.students_graded} dari ${s.students_total} siswa sudah dinilai
                    </p>
                </div>
                <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 text-xs font-black">
                    !
                </div>
            </div>
        `).join('');
    }

    const modal = document.getElementById('incomplete-modal');
    if(modal) modal.classList.remove('hidden');
}

function closeIncompleteModal() {
    const modal = document.getElementById('incomplete-modal');
    if(modal) modal.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    loadGradeStatus();

    // Hook filters
    const filters = document.querySelectorAll('select[name="class_id"], select[name="semester"], input[name="academic_year"], select[name="report_type"]');
    filters.forEach(f => {
        f.addEventListener('change', () => {
            // Jika input text (academic_year), beri delay
            if(f.tagName === 'INPUT') {
                clearTimeout(window._loadStatusTimer);
                window._loadStatusTimer = setTimeout(loadGradeStatus, 500);
            } else {
                loadGradeStatus();
            }
        });
    });
});
</script>
<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>
@endsection
