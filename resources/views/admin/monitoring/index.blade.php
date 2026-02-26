@extends('layouts.app')

@section('title', 'Monitoring Ujian - SesekaliCBT')

@section('page-title', 'Monitoring Ujian - ' . $exam->title)

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Siswa Jenjang</p>
            <p class="text-4xl font-bold text-blue-600">{{ $stats['total_siswa_jenjang'] }}</p>
            <p class="text-xs text-gray-500 mt-2">Kelas {{ $exam->jenjang }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-600 text-sm font-semibold mb-2">Total Selesai</p>
            <p class="text-4xl font-bold text-green-600">{{ $stats['total_selesai'] }}</p>
            <p class="text-xs text-gray-500 mt-2">Sudah submit jawaban</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-400">
            <p class="text-gray-600 text-sm font-semibold mb-2">Belum Ujian</p>
            <p class="text-4xl font-bold text-gray-600">{{ $stats['belum_ujian'] }}</p>
            <p class="text-xs text-gray-500 mt-2">Belum mendaftar/mulai</p>
        </div>
    </div>

    <!-- Completed Students Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">📋 Siswa yang Sudah Selesai</h2>
            @if($completedStudents->count() > 0)
                <input type="text" id="searchInput" placeholder="Cari nama atau NIS..." 
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @endif
        </div>

        <div class="overflow-x-auto">
            @if($completedStudents->count() > 0)
                <table class="w-full" id="studentsTable">
                    <thead class="bg-gray-100 border-b border-gray-300">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">No</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nama Siswa</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">NIS</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Tipe Ujian</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Waktu Selesai</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($completedStudents as $student)
                        <tr class="hover:bg-gray-50 transition search-row">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 student-name">{{ $student['student_name'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 student-nis">{{ $student['nis'] }}</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $student['exam_type'] }}</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600">
                                {{ $student['submitted_at']->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button class="reopen-btn px-4 py-2 text-sm bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition font-medium"
                                    data-attempt-id="{{ $student['attempt_id'] }}"
                                    data-student-name="{{ $student['student_name'] }}"
                                    data-last-worked="{{ $student['submitted_at']->format('Y-m-d H:i:s') }}"
                                    data-exam-duration="{{ $exam->duration_minutes }}">
                                    <i class="fas fa-unlock mr-1"></i>Buka Akses
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-4 block"></i>
                    <p class="text-gray-500 text-lg">Belum ada siswa yang menyelesaikan ujian</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
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

    async function reopenExam(attemptId, studentName, lastWorked, examDuration) {
        const result = await Swal.fire({
            title: '🔓 Buka Akses Ujian',
            html: `<p>Buka akses ujian untuk <strong>${studentName}</strong></p>
                   <div class="mt-4 text-left">
                       <label class="flex items-center mb-3">
                           <input type="radio" name="timeOption" value="continue" checked class="w-4 h-4">
                           <span class="ml-2">
                               <strong>Lanjutkan dari terakhir mengerjakan</strong><br>
                               <span class="text-xs text-gray-600">${lastWorked}</span>
                           </span>
                       </label>
                       <label class="flex items-center mb-3">
                           <input type="radio" name="timeOption" value="reset" class="w-4 h-4">
                           <span class="ml-2"><strong>Reset dari awal</strong> (${examDuration} menit)</span>
                       </label>
                       <label class="flex items-center mb-2">
                           <input type="radio" name="timeOption" value="custom" class="w-4 h-4">
                           <span class="ml-2"><strong>Set durasi (menit)</strong></span>
                       </label>
                       <div class="hidden ml-8" id="customMinutesDiv">
                           <input type="number" id="customMinutes" placeholder="Masukkan jumlah menit" min="1" max="${examDuration}" class="w-32 px-3 py-2 border border-gray-300 rounded text-sm">
                           <span class="text-xs text-gray-600 ml-2">menit (max: ${examDuration})</span>
                       </div>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#eab308',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Buka Akses',
            cancelButtonText: 'Batal',
            didOpen: () => {
                // Show/hide custom minutes input based on radio selection
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
                        Swal.showValidationMessage('Silakan masukkan durasi waktu (dalam menit) yang valid');
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

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    title: '✅ Berhasil!',
                    text: 'Akses ujian berhasil dibuka. Siswa dapat melanjutkan ujian.',
                    icon: 'success',
                    confirmButtonColor: '#10b981'
                });
                setTimeout(() => location.reload(), 1500);
            } else {
                await Swal.fire({
                    title: '❌ Error',
                    text: data.message || 'Gagal membuka akses ujian',
                    icon: 'error'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                title: '❌ Error',
                text: error.message || 'Terjadi kesalahan saat membuka akses',
                icon: 'error'
            });
        }
    }
</script>
@endsection
