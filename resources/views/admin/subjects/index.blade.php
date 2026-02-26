@extends('layouts.app')

@section('title', 'Subject Management - SesekaliCBT')

@section('page-title', 'Subject Management')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Subject Management</h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.subjects.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    + Add Subject
                </a>
                <form id="deleteAllForm" action="{{ route('admin.subjects.deleteAll') }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteAllSubjects()" class="inline-block px-4 py-2 bg-red-800 text-white rounded-lg hover:bg-red-900 transition font-bold">
                        🗑️ Hapus Semua Mata Pelajaran
                    </button>
                </form>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="mb-6 p-4 bg-linear-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-sm flex items-start justify-between animate-slideDown">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-green-800">Berhasil!</p>
                        <p class="text-sm text-green-700 mt-1">{{ $message }}</p>
                    </div>
                </div>
                <button type="button" class="text-green-600 hover:text-green-800" onclick="this.parentElement.style.display='none';">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <script>
                setTimeout(() => {
                    const successAlert = document.querySelector('.animate-slideDown');
                    if (successAlert) {
                        successAlert.style.display = 'none';
                    }
                }, 5000);
            </script>
        @endif

        @if ($message = Session::get('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex justify-between">
                <span>{{ $message }}</span>
                <button type="button" class="text-red-800 hover:text-red-600" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        @endif

        <!-- Subjects Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Questions</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $subject->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $subject->questions_count }}</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" style="display: inline;" onclick="return confirm('Delete subject?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                No subjects found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $subjects->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteAllSubjects() {
            Swal.fire({
                title: '⚠️ PERHATIAN SERIUS!',
                html: `
                    <div class="text-left">
                        <p class="font-bold text-red-700 mb-4">Hapus SEMUA mata pelajaran? Tindakan ini TIDAK BISA DIBATALKAN.</p>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                            <p class="font-semibold text-red-900 mb-2">Data yang akan dihapus:</p>
                            <ul class="text-sm text-red-800 space-y-1">
                                <li>✗ Semua mata pelajaran</li>
                                <li>✗ Semua soal yang terkait</li>
                                <li>✗ Semua ujian yang menggunakan mata pelajaran ini</li>
                                <li>✗ Data tidak dapat dipulihkan setelah penghapusan</li>
                            </ul>
                        </div>
                        <p class="text-sm text-gray-700 mb-4">Jika Anda yakin ingin melanjutkan, ketik <span class="font-mono font-bold bg-gray-200 px-2 py-1 rounded">HAPUS SEMUA</span> di bawah:</p>
                        <input type="text" id="confirmTextSubjects" placeholder="Ketik: HAPUS SEMUA" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#991b1b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus Semua',
                cancelButtonText: 'Batal',
                didOpen: () => {
                    document.getElementById('confirmTextSubjects').focus();
                },
                preConfirm: () => {
                    const input = document.getElementById('confirmTextSubjects').value;
                    if (input !== 'HAPUS SEMUA') {
                        Swal.showValidationMessage('Ketik "HAPUS SEMUA" dengan benar untuk konfirmasi');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show final confirmation
                    Swal.fire({
                        title: 'Konfirmasi Terakhir',
                        html: '<p class="text-gray-800">Ini adalah konfirmasi terakhir. Klik "Hapus Selamanya" untuk menghapus semua mata pelajaran.</p>',
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#7f1d1d',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Hapus Selamanya',
                        cancelButtonText: 'Batal',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'inline-block px-6 py-2 bg-red-900 text-white rounded-lg font-bold hover:bg-red-950 transition',
                            cancelButton: 'inline-block px-6 py-2 bg-gray-500 text-white rounded-lg font-bold hover:bg-gray-600 transition ml-2'
                        }
                    }).then((finalResult) => {
                        if (finalResult.isConfirmed) {
                            document.getElementById('deleteAllForm').submit();
                        }
                    });
                }
            });
        }
    </script>

    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-slideDown {
            animation: slideDown 0.3s ease-out;
        }
    </style>
@endsection
