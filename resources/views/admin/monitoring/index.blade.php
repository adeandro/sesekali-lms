@extends('layouts.app')

@section('title', 'Detail Monitoring - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Monitoring Real-Time')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="space-y-2">
            <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                <a href="{{ route('admin.monitor-exams.index') }}" class="hover:text-indigo-600 transition-colors">Monitor Ujian</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-indigo-600">Detail Monitoring</span>
            </nav>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">{{ $exam->title }}</h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100/50">{{ $exam->subject->name }}</span>
                <span class="text-gray-300">•</span>
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelas {{ $exam->jenjang }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.monitor-exams.index') }}" class="px-6 py-3 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-100 hover:text-gray-600 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-indigo-100/50">
                <i class="fas fa-users-viewfinder text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Peserta</p>
                <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors">{{ $stats['total_siswa_jenjang'] }}</p>
                <p class="text-[9px] font-bold text-gray-300 uppercase tracking-widest mt-1">Siswa Jenjang {{ $exam->jenjang }}</p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-emerald-100/50">
                <i class="fas fa-flag-checkered text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Sudah Selesai</p>
                <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-emerald-600 transition-colors">{{ $stats['total_selesai'] }}</p>
                <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-1">Siswa Telah Submit</p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-500">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-500 shadow-sm border border-amber-100/50">
                <i class="fas fa-user-clock text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Belum Ujian</p>
                <p class="text-3xl font-black text-gray-900 leading-tight group-hover:text-amber-600 transition-colors">{{ $stats['belum_ujian'] }}</p>
                <p class="text-[9px] font-bold text-amber-600 uppercase tracking-widest mt-1">Belum Ada Sesi</p>
            </div>
        </div>
    </div>

    <!-- Completed Students Table -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-user-check text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight uppercase">Siswa Selesai</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Daftar siswa yang telah menyelesaikan ujian ini</p>
                </div>
            </div>
            
            @if($completedStudents->count() > 0)
                <div class="relative group w-full md:w-72">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama atau NIS..." 
                        class="w-full h-12 bg-gray-50 border-transparent rounded-2xl pl-12 pr-6 text-xs font-bold text-gray-900 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all">
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            @if($completedStudents->count() > 0)
                <table class="w-full text-left border-collapse" id="studentsTable">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-20 text-center">No</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Data Siswa</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Tipe Ujian</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Waktu Selesai</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($completedStudents as $student)
                        <tr class="group hover:bg-gray-50/50 transition-colors search-row">
                            <td class="px-8 py-6 text-center">
                                <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest">{{ $loop->iteration }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4" x-data>
                                    <div x-html="multiavatar('{{ $student['avatar_seed'] }}')" class="w-10 h-10 rounded-xl border border-gray-100 shadow-sm bg-white overflow-hidden"></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 leading-none student-name">{{ $student['student_name'] }}</p>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1 student-nis">NIS: {{ $student['nis'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-3 py-1 bg-gray-50 text-gray-500 rounded-lg text-[9px] font-black uppercase tracking-widest border border-gray-100/50">
                                    {{ $student['exam_type'] }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <p class="text-[11px] font-bold text-gray-900 tracking-tight">{{ $student['submitted_at']->format('d/m/Y') }}</p>
                                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mt-1">{{ $student['submitted_at']->format('H:i:s') }}</p>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="flex flex-col gap-2">
                                    <button class="reopen-btn h-9 px-4 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 hover:text-white transition flex items-center justify-center gap-2 mx-auto w-full"
                                        data-attempt-id="{{ $student['attempt_id'] }}"
                                        data-student-name="{{ $student['student_name'] }}"
                                        data-last-worked="{{ $student['submitted_at']->format('Y-m-d H:i:s') }}"
                                        data-exam-duration="{{ $exam->duration_minutes }}">
                                        <i class="fas fa-lock-open text-[8px]"></i> Buka Akses
                                    </button>
                                    <button onclick="resetExam('{{ $student['attempt_id'] }}', '{{ $student['student_name'] }}')" 
                                        class="h-9 px-4 bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-600 hover:text-white transition flex items-center justify-center gap-2 mx-auto w-full"
                                        title="Hapus Jawaban & Reset Nilai">
                                        <i class="fas fa-undo text-[8px]"></i> Reset Jawaban
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-20 text-center animate-fadeIn">
                    <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center text-gray-200 mx-auto mb-6 transform rotate-6">
                        <i class="fas fa-user-slash text-3xl"></i>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest max-w-xs mx-auto leading-relaxed">Belum ada siswa yang menyelesaikan ujian ini.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.search-row');
                
                rows.forEach(row => {
                    const name = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
                    const nis = row.querySelector('.student-nis')?.textContent.toLowerCase() || '';
                    
                    if (name.includes(searchTerm) || nis.includes(searchTerm)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        }

        // Handle reopen button click
        document.addEventListener('click', function(e) {
            const reopenBtn = e.target.closest('.reopen-btn');
            if (reopenBtn) {
                const attemptId = reopenBtn.dataset.attemptId;
                const studentName = reopenBtn.dataset.studentName;
                const lastWorked = reopenBtn.dataset.lastWorked;
                const examDuration = reopenBtn.dataset.examDuration;
                reopenExam(attemptId, studentName, lastWorked, examDuration);
            }
        });
    });

    async function resetExam(attemptId, studentName) {
        const result = await Swal.fire({
            title: 'RESET JAWABAN?',
            html: `
                <div class="space-y-4 text-center">
                    <p class="text-sm text-gray-500">Seluruh jawaban dan nilai untuk <span class="font-black text-gray-900">${studentName}</span> akan dihapus permanen.</p>
                    <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100 text-left">
                        <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest flex items-center gap-2 mb-1">
                            <i class="fas fa-exclamation-triangle"></i> Peringatan
                        </p>
                        <p class="text-[9px] font-bold text-rose-400 leading-relaxed uppercase tracking-widest">
                            Siswa harus mengulang ujian dari awal setelah akses dibuka kembali.
                        </p>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'YA, RESET SEKARANG',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: 'rounded-[1.5rem] p-8',
                confirmButton: 'h-14 bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 order-2 border-0',
                cancelButton: 'h-14 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 order-1 border-0'
            },
            buttonsStyling: false
        });

        if (result.isConfirmed) {
            try {
                Swal.fire({
                    title: 'MEMPROSES...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    customClass: { popup: 'rounded-[1.5rem]' }
                });

                const response = await fetch(`/admin/monitor/attempts/${attemptId}/reset`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        title: 'BERHASIL!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OKE',
                        customClass: {
                            popup: 'rounded-[1.5rem]',
                            confirmButton: 'h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 border-0'
                        },
                        buttonsStyling: false
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        title: 'GAGAL',
                        text: data.message || 'Terjadi kesalahan sistem.',
                        icon: 'error',
                        customClass: { popup: 'rounded-[1.5rem]' }
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'ERROR',
                    text: 'Gagal menghubungi server.',
                    icon: 'error',
                    customClass: { popup: 'rounded-[1.5rem]' }
                });
            }
        }
    }

    async function reopenExam(attemptId, studentName, lastWorked, examDuration) {
        const result = await Swal.fire({
            title: 'BUKA AKSES UJIAN',
            html: `
                <div class="text-left space-y-4 p-2">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-6 border-b border-indigo-50 pb-4">
                        Konfigurasi ulang sesi untuk: <span class="text-indigo-600">${studentName}</span>
                    </p>
                    <div class="space-y-3">
                        <label class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 border border-transparent hover:border-indigo-100 cursor-pointer transition-all group has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-200">
                            <input type="radio" name="timeOption" value="continue" checked class="mt-1 w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <div class="flex-1">
                                <p class="text-xs font-black text-gray-900 uppercase tracking-tight leading-none group-has-[:checked]:text-indigo-900">Lanjutkan Sesi Berjalan</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1 group-has-[:checked]:text-indigo-400">Terakhir: ${lastWorked}</p>
                            </div>
                        </label>
                        
                        <label class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 border border-transparent hover:border-indigo-100 cursor-pointer transition-all group has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-200">
                            <input type="radio" name="timeOption" value="reset" class="mt-1 w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <div class="flex-1">
                                <p class="text-xs font-black text-gray-900 uppercase tracking-tight leading-none group-has-[:checked]:text-indigo-900">Reset Dari Awal</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1 group-has-[:checked]:text-indigo-400">Total Durasi: ${examDuration} Menit</p>
                            </div>
                        </label>
                        
                        <div class="p-4 rounded-2xl bg-gray-50 border border-transparent has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-200 transition-all">
                            <label class="flex items-start gap-4 cursor-pointer group">
                                <input type="radio" name="timeOption" value="custom" class="mt-1 w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <div class="flex-1">
                                    <p class="text-xs font-black text-gray-900 uppercase tracking-tight leading-none group-has-[:checked]:text-indigo-900">Atur Durasi Kustom</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1 group-has-[:checked]:text-indigo-400">Tentukan menit tersisa</p>
                                </div>
                            </label>
                            <div class="hidden mt-4 pl-8" id="customMinutesDiv">
                                <div class="relative">
                                    <input type="number" id="customMinutes" placeholder="Menit" min="1" max="${examDuration}" 
                                        class="w-full h-12 bg-white border-2 border-indigo-100 rounded-xl px-4 text-sm font-black text-indigo-900 focus:border-indigo-500 outline-none transition-all">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[9px] font-black text-indigo-300 uppercase tracking-widest">Menit</span>
                                </div>
                                <p class="text-[9px] font-bold text-indigo-300 mt-2 uppercase tracking-widest">Maksimal: ${examDuration} Menit</p>
                            </div>
                        </div>
                    </div>
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'KONFIRMASI BUKA AKSES',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: 'rounded-[1.5rem] p-8',
                title: 'text-lg font-black tracking-tight text-gray-900 uppercase',
                confirmButton: 'h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 order-2 border-0',
                cancelButton: 'h-14 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 order-1 border-0'
            },
            buttonsStyling: false,
            didOpen: () => {
                const radios = Swal.getPopup().querySelectorAll('input[name="timeOption"]');
                const customMinutesDiv = Swal.getPopup().querySelector('#customMinutesDiv');
                const customMinutesInput = Swal.getPopup().querySelector('#customMinutes');
                
                radios.forEach(radio => {
                    radio.addEventListener('change', (e) => {
                        if (e.target.value === 'custom') {
                            customMinutesDiv.classList.remove('hidden');
                            customMinutesInput.focus();
                        } else {
                            customMinutesDiv.classList.add('hidden');
                        }
                    });
                });
            },
            preConfirm: () => {
                const selectedOption = Swal.getPopup().querySelector('input[name="timeOption"]:checked').value;
                let customMinutes = null;
                
                if (selectedOption === 'custom') {
                    customMinutes = Swal.getPopup().querySelector('#customMinutes').value;
                    if (!customMinutes || parseInt(customMinutes) <= 0) {
                        Swal.showValidationMessage('Masukkan jumlah menit yang valid');
                        return false;
                    }
                }
                
                return {
                    timeOption: selectedOption,
                    customMinutes: customMinutes
                };
            }
        });

        if (!result.isConfirmed) return;

        const timeData = result.value;

        try {
            Swal.fire({
                title: 'MEMPROSES...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                customClass: { popup: 'rounded-[1.5rem]' }
            });

            const response = await fetch(`/admin/monitor/attempts/${attemptId}/reopen`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    reason: 'Dibuka kembali oleh admin',
                    time_option: timeData.timeOption,
                    custom_minutes: timeData.customMinutes
                })
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    title: 'BERHASIL!',
                    text: 'Akses ujian telah dibuka kembali.',
                    icon: 'success',
                    confirmButtonText: 'OKE',
                    customClass: {
                        popup: 'rounded-[1.5rem]',
                        confirmButton: 'h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl px-12 border-0'
                    },
                    buttonsStyling: false
                });
                location.reload();
            } else {
                Swal.fire({
                    title: 'GAGAL',
                    text: data.message || 'Terjadi kesalahan sistem.',
                    icon: 'error',
                    customClass: { popup: 'rounded-[1.5rem]' }
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'ERROR',
                text: 'Gagal menghubungi server.',
                icon: 'error',
                customClass: { popup: 'rounded-[1.5rem]' }
            });
        }
    }
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .hidden { display: none !important; }
</style>
@endsection
