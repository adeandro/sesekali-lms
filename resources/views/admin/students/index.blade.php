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
                <form action="{{ route('admin.students.resetAllPasswords') }}" method="POST" style="display:inline;" onclick="return confirm('Atur ulang SEMUA password siswa? Tindakan ini tidak bisa dibatalkan. Semua siswa akan mendapat password acak baru.');">
                    @csrf
                    <button type="submit" class="inline-block px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        🔄 Atur Ulang Semua
                    </button>
                </form>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex justify-between">
                <span>{{ $message }}</span>
                <button type="button" class="text-green-800 hover:text-green-600" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex justify-between">
                <span>{{ $message }}</span>
                <button type="button" class="text-red-800 hover:text-red-600" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        @endif

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
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
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
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $student->email ?? '-' }}</td>
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
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" style="display: inline;" onclick="return confirm('Hapus siswa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
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
@endsection
