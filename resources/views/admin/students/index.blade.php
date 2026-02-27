@extends('layouts.app')

@section('title', 'Manajemen Siswa - SesekaliCBT')

@section('page-title', 'Manajemen Siswa')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Manajemen Siswa</h2>
            <div class="space-x-2">
                <a href="{{ route('admin.students.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    + Tambah Siswa
                </a>
                <a href="{{ route('admin.students.importForm') }}" class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    📥 Impor
                </a>
                <a href="{{ route('admin.students.export') }}" class="inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    📤 Ekspor
                </a>
                <form id="resetAllPasswordsForm" action="{{ route('admin.students.resetAllPasswords') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="button" onclick="confirmResetAllPasswords()" class="inline-block px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        🔄 Atur Ulang Semua
                    </button>
                </form>
                <form id="deleteAllStudentsForm" action="{{ route('admin.students.deleteAll') }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteAllStudents()" class="inline-block px-4 py-2 bg-red-800 text-white rounded-lg hover:bg-red-900 transition font-bold">
                        🗑️ Hapus Semua Siswa
                    </button>
                </form>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-sm flex items-start justify-between animate-slideDown">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-green-800">Berhasil!</p>
                        <p class="text-sm text-green-700 mt-1">{{ $message }}</p>
                    </div>
                </div>
                <button type="button" class="text-green-500 hover:text-green-700 transition" onclick="this.parentElement.remove();">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.querySelector('[class*="animate-slideDown"]');
                    if (alert) alert.remove();
                }, 5000);
            </script>
        @endif

        @if ($message = Session::get('error'))
            <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-lg shadow-sm flex items-start justify-between animate-slideDown">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-red-800">Terjadi Kesalahan!</p>
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    </div>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700 transition" onclick="this.parentElement.remove();">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.querySelector('[class*="animate-slideDown"]:last-of-type');
                    if (alert) alert.remove();
                }, 7000);
            </script>
        @endif

        @if ($message = Session::get('password'))
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-lg shadow-sm flex items-start justify-between">
                <div class="flex items-start gap-3 flex-1">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-800">Password Baru Dibuat</p>
                        <p class="text-sm text-blue-700 mt-1">NIS: <span class="font-mono font-bold">{{ Session::get('nis') }}</span></p>
                        <p class="text-sm text-blue-700 mt-1">Password: <span class="font-mono font-bold bg-blue-100 px-2 py-1 rounded">{{ $message }}</span></p>
                    </div>
                </div>
                <button type="button" class="text-blue-500 hover:text-blue-700 transition flex-shrink-0" onclick="this.parentElement.remove();">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        @endif

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

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form action="{{ route('admin.students.index') }}" method="GET" class="flex gap-4">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Cari berdasarkan NIS atau nama..." 
                    value="{{ request('search') }}"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <select name="grade" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class }}" {{ request('grade') === $class ? 'selected' : '' }}>
                            Kelas {{ $class }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Cari
                </button>
            </form>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">NIS</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nama</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Kelas (Angkatan)</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Rombel</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Password</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $student->nis }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $student->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="px-3 py-1 rounded-full text-white text-xs font-semibold bg-blue-500">
                                    Kelas {{ $student->grade }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $student->class_group }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-900">
                                <code class="bg-gray-100 px-2 py-1 rounded">{{ $student->password_display ?? '-' }}</code>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold 
                                    {{ $student->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('admin.students.edit', $student) }}" class="text-blue-600 hover:text-blue-800">Ubah</a>
                                <form action="{{ route('admin.students.resetPassword', $student) }}" method="POST" style="display: inline;" onclick="return confirm('Atur ulang password?');">
                                    @csrf
                                    <button type="submit" class="text-orange-600 hover:text-orange-800">Atur Ulang</button>
                                </form>
                                <form action="{{ route('admin.students.toggleActive', $student) }}" method="POST" style="display: inline;" onclick="return confirm('Ubah status?');">
                                    @csrf
                                    <button type="submit" class="text-purple-600 hover:text-purple-800">
                                        {{ $student->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" style="display: inline;" id="deleteStudentForm{{ $student->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="text-red-600 hover:text-red-800" onclick="deleteStudent('{{ $student->name }}', {{ $student->id }})">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada siswa ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $students->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteAll() {
            Swal.fire({
                title: '⚠️ PERHATIAN SERIUS!',
                html: `
                    <div class="text-left">
                        <p class="font-bold text-red-700 mb-4">Hapus SEMUA data siswa? Tindakan ini TIDAK BISA DIBATALKAN.</p>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                            <p class="font-semibold text-red-900 mb-2">Data yang akan dihapus:</p>
                            <ul class="text-sm text-red-800 space-y-1">
                                <li>✗ Semua profil siswa</li>
                                <li>✗ Semua data ujian siswa</li>
                                <li>✗ Semua hasil ujian siswa</li>
                                <li>✗ Semua jawaban siswa</li>
                                <li>✗ Data tidak dapat dipulihkan setelah penghapusan</li>
                            </ul>
                        </div>
                        <p class="text-sm text-gray-700 mb-4">Jika Anda yakin ingin melanjutkan, ketik <span class="font-mono font-bold bg-gray-200 px-2 py-1 rounded">HAPUS SEMUA</span> di bawah:</p>
                        <input type="text" id="confirmText" placeholder="Ketik: HAPUS SEMUA" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#991b1b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus Semua',
                cancelButtonText: 'Batal',
                didOpen: () => {
                    document.getElementById('confirmText').focus();
                },
                preConfirm: () => {
                    const input = document.getElementById('confirmText').value;
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
                        html: '<p class="text-gray-800">Ini adalah konfirmasi terakhir. Klik "Hapus Selamanya" untuk menghapus semua data siswa.</p>',
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

        function confirmResetAllPasswords() {
            Swal.fire({
                title: '🔄 Atur Ulang Password',
                html: `
                    <div class="text-left">
                        <p class="font-bold text-orange-700 mb-4">Atur ulang password SEMUA siswa?</p>
                        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded mb-4">
                            <p class="font-semibold text-orange-900 mb-2">Tindakan ini akan:</p>
                            <ul class="text-sm text-orange-800 space-y-1">
                                <li>✓ Membuat password acak baru untuk setiap siswa</li>
                                <li>✓ Password akan ditampilkan hanya sekali</li>
                                <li>✓ Semua siswa perlu password baru untuk login</li>
                                <li>⚠️ Tindakan ini tidak bisa dibatalkan</li>
                            </ul>
                        </div>
                        <p class="text-sm text-gray-700">Tindakan ini tidak bisa dibatalkan. Pastikan Anda siap sebelum melanjutkan.</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b45309',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Atur Ulang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('resetAllPasswordsForm').submit();
                }
            });
        }
    </script>
@endsection
