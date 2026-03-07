@extends('layouts.app')

@section('title', 'Manajemen Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Manajemen Siswa')

@section('content')
    <div class="space-y-6 animate-fadeIn">
        <!-- Header & Quick Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Daftar Siswa</h2>
                <p class="text-sm text-gray-500">Kelola data siswa, impor masal, dan pengaturan foto profil.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.students.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md active:scale-95">
                    <i class="fas fa-plus mr-2"></i> Tambah Siswa
                </a>
                <div class="flex rounded-xl shadow-sm overflow-hidden border border-gray-200">
                    <a href="{{ route('admin.students.importForm') }}" class="px-4 py-2 bg-white text-gray-700 text-sm font-bold hover:bg-gray-50 transition-colors border-r border-gray-200" title="Impor Data">
                        <i class="fas fa-file-import mr-1 text-emerald-600"></i> Impor
                    </a>
                    <a href="{{ route('admin.students.export') }}" class="px-4 py-2 bg-white text-gray-700 text-sm font-bold hover:bg-gray-50 transition-colors" title="Ekspor Data">
                        <i class="fas fa-file-export mr-1 text-orange-600"></i> Ekspor
                    </a>
                </div>
                <a href="{{ route('admin.students.upload-photos') }}" class="inline-flex items-center px-4 py-2 bg-white border border-indigo-200 text-indigo-700 text-sm font-bold rounded-xl hover:bg-indigo-50 transition-all active:scale-95 shadow-sm">
                    <i class="fas fa-images mr-2"></i> Unggah Foto
                </a>
                <div class="relative group">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-rose-50 border border-rose-200 text-rose-700 text-sm font-bold rounded-xl hover:bg-rose-100 transition-all active:scale-95">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Aksi Bahaya
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <!-- Floating Wrapper to Close the Hover Gap -->
                    <div class="absolute right-0 pt-2 w-56 z-30 hidden group-hover:block animate-slideUp">
                        <div class="bg-white rounded-xl shadow-xl border border-gray-100 py-2 overflow-hidden">
                            <button type="button" onclick="confirmResetAllPasswords()" class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 font-medium flex items-center gap-3 transition-colors">
                                <i class="fas fa-key w-5 text-center"></i> 
                                <span>Atur Ulang Password</span>
                            </button>
                            <hr class="my-2 border-gray-100">
                            <button type="button" onclick="confirmDeleteAllStudents()" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 font-bold flex items-center gap-3 transition-colors">
                                <i class="fas fa-trash-alt w-5 text-center"></i>
                                <span>Hapus Semua Siswa</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="resetAllPasswordsForm" action="{{ route('admin.students.resetAllPasswords') }}" method="POST" class="hidden no-loading">@csrf</form>
        <form id="deleteAllStudentsForm" action="{{ route('admin.students.deleteAll') }}" method="POST" class="hidden no-loading">@csrf @method('DELETE')</form>

        <!-- Search and Filter -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <form action="{{ route('admin.students.index') }}" method="GET" class="no-loading">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-6 lg:col-span-7">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1 ml-1">Cari Siswa</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Cari berdasarkan NIS atau nama..." 
                                value="{{ request('search') }}"
                                class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all text-sm"
                            >
                        </div>
                    </div>
                    <div class="md:col-span-4 lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1 ml-1">Filter Kelas</label>
                        <select name="grade" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all text-sm appearance-none">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class }}" {{ request('grade') == $class ? 'selected' : '' }}>
                                    Kelas {{ $class }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button type="submit" class="w-full px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl hover:bg-indigo-600 transition-all flex items-center justify-center gap-2 active:scale-95 shadow-sm">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Profil</th>
                            <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Identitas</th>
                            <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Kelas & Rombel</th>
                            <th class="px-6 py-4 text-left text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Akses</th>
                            <th class="px-6 py-4 text-center text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-center text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($students as $student)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 flex-shrink-0">
                                            <img src="{{ $student->photo_url }}" alt="Avatar" class="h-12 w-12 rounded-xl object-cover ring-2 ring-white shadow-sm">
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $student->formatted_name }}</div>
                                    <div class="text-xs text-gray-500 font-mono tracking-tight mt-0.5">NIS: {{ $student->nis }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 w-fit">
                                            KELAS {{ $student->grade }}
                                        </span>
                                        <span class="text-gray-600 font-medium">Rombel: {{ $student->class_group }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="group/pass relative inline-block">
                                        <code class="text-[11px] bg-gray-50 px-2 py-1 rounded-md text-gray-700 border border-gray-100 font-mono cursor-pointer hover:bg-gray-100 transition-colors">
                                            {{ $student->password_display ?? '********' }}
                                        </code>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex px-2 py-1 text-[10px] font-extrabold rounded-md uppercase tracking-wider
                                        {{ $student->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.students.edit', $student) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Ubah Data">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>
                                        <button type="button" onclick="confirmResetPassword({{ $student->id }}, '{{ $student->name }}')" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Atur Ulang Password">
                                            <i class="fas fa-key text-lg"></i>
                                        </button>
                                        <button type="button" onclick="confirmToggleActive({{ $student->id }}, '{{ $student->name }}', {{ $student->is_active ? 'true' : 'false' }})" class="p-2 {{ $student->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition-all" title="{{ $student->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas {{ $student->is_active ? 'fa-user-slash' : 'fa-user-check' }} text-lg"></i>
                                        </button>
                                        <button type="button" onclick="confirmDeleteStudent({{ $student->id }}, '{{ $student->name }}')" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Hapus">
                                            <i class="fas fa-trash-alt text-lg"></i>
                                        </button>
                                    </div>
                                    <!-- Hidden Forms -->
                                    <form id="reset-password-form-{{ $student->id }}" action="{{ route('admin.students.resetPassword', $student) }}" method="POST" class="hidden no-loading">@csrf</form>
                                    <form id="toggle-active-form-{{ $student->id }}" action="{{ route('admin.students.toggleActive', $student) }}" method="POST" class="hidden no-loading">@csrf</form>
                                    <form id="delete-form-{{ $student->id }}" action="{{ route('admin.students.destroy', $student) }}" method="POST" class="hidden no-loading">@csrf @method('DELETE')</form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-users-slash text-4xl text-gray-200"></i>
                                        <p class="text-gray-500 font-medium italic">Tidak ada data siswa ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($students->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function confirmDeleteStudent(id, name) {
            Swal.fire({
                title: 'Hapus Siswa?',
                html: `Anda akan menghapus data siswa <b class="text-rose-600">${name}</b> secara permanen.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('loading-overlay').style.display = 'block';
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        function confirmResetPassword(id, name) {
            Swal.fire({
                title: 'Reset Password?',
                html: `Buat password baru acak untuk <b class="text-indigo-600">${name}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Reset',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('loading-overlay').style.display = 'block';
                    document.getElementById('reset-password-form-' + id).submit();
                }
            });
        }

        function confirmToggleActive(id, name, isActive) {
            const actionText = isActive ? 'Nonaktifkan' : 'Aktifkan';
            Swal.fire({
                title: `${actionText} Siswa?`,
                html: `${actionText} akses login untuk <b class="text-indigo-600">${name}</b>.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                confirmButtonText: `Ya, ${actionText}`,
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('loading-overlay').style.display = 'block';
                    document.getElementById('toggle-active-form-' + id).submit();
                }
            });
        }

        function confirmResetAllPasswords() {
            Swal.fire({
                title: 'Atur Ulang Semua Password?',
                text: 'SEMUA siswa akan mendapatkan password baru. Tindakan ini tidak bisa dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Reset Semua',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('loading-overlay').style.display = 'block';
                    document.getElementById('resetAllPasswordsForm').submit();
                }
            });
        }

        function confirmDeleteAllStudents() {
            Swal.fire({
                title: 'HAPUS SEMUA SISWA?',
                html: 'Tindakan ini akan menghapus <b>SELURUH</b> data siswa dan riwayat ujian mereka selamanya!',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#be123c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'HAPUS SELAMANYA',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('loading-overlay').style.display = 'block';
                    document.getElementById('deleteAllStudentsForm').submit();
                }
            });
        }
    </script>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        .animate-slideUp { animation: slideUp 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
@endsection
