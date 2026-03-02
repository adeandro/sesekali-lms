@extends('layouts.app')

@section('title', 'Manajemen Guru - SesekaliCBT')

@section('page-title', 'Manajemen Guru')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-gray-900">Manajemen Guru</h2>
            <a href="{{ route('superadmin.teachers.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Tambah Guru
            </a>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('superadmin.teachers.index') }}" method="GET" class="flex gap-4">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Cari berdasarkan nama, email, atau NIP..." 
                    value="{{ request('search') }}"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Cari
                </button>
            </form>
        </div>

        <!-- Teachers Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">NIP/NIS</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nama</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Mata Pelajaran</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $teacher->nis }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $teacher->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $teacher->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($teacher->subjects as $subject)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $subject->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold 
                                    {{ $teacher->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $teacher->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-3">
                                <a href="{{ route('superadmin.teachers.edit', $teacher) }}" class="text-blue-600 hover:text-blue-800 font-medium">Ubah</a>
                                <form action="{{ route('superadmin.teachers.destroy', $teacher) }}" method="POST" class="inline" onsubmit="return confirm('Hapus guru ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                                Tidak ada data guru ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $teachers->links() }}
        </div>
    </div>
@endsection
