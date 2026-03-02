@extends('layouts.app')

@section('title', 'Detail Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Detail Siswa')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6 animate-fadeIn">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center justify-between mb-2">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.students.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-users mr-2"></i> Manajemen Siswa
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 text-xs mx-2"></i>
                            <span class="text-sm font-bold text-indigo-600">Detail Profil</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.students.edit', $student) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors" title="Ubah Data">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="{{ route('admin.students.index') }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('password'))
            <div class="bg-emerald-50 border border-emerald-100 rounded-3xl p-6 flex items-start gap-4 animate-bounce-short">
                <div class="bg-emerald-100 p-3 rounded-2xl text-emerald-600">
                    <i class="fas fa-key text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-emerald-900 font-bold">Password Berhasil Dihasilkan!</h3>
                    <p class="text-emerald-700 text-sm mt-1">Gunakan password di bawah ini untuk login pertama kali. Harap catat atau berikan langsung kepada siswa.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="px-4 py-2 bg-white border border-emerald-200 rounded-xl font-mono text-lg font-bold text-emerald-800 tracking-wider shadow-sm select-all">
                            {{ session('password') }}
                        </div>
                        <button onclick="copyToClipboard('{{ session('password') }}')" class="p-2 text-emerald-600 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-emerald-200" title="Salin Password">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Profile Header -->
            <div class="h-32 bg-indigo-600 relative">
                <div class="absolute -bottom-12 left-8">
                    <div class="h-24 w-24 bg-white rounded-3xl p-1 shadow-md border-4 border-white overflow-hidden">
                        <img src="{{ $student->photo_url }}" alt="Profile" class="h-full w-full object-cover rounded-2xl">
                    </div>
                </div>
            </div>

            <div class="pt-16 pb-8 px-8">
                <div class="mb-8">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $student->name }}</h2>
                            <p class="text-indigo-600 font-mono text-sm font-bold mt-1">NIS: {{ $student->nis }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-4 py-1 rounded-full text-xs font-bold ring-1 ring-inset {{ $student->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-rose-200' }}">
                                <i class="fas {{ $student->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                {{ $student->is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                            </span>
                            <p class="text-[10px] text-gray-400">Terdaftar sejak {{ $student->created_at->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Basic Info -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Informasi Akademik</label>
                            <div class="bg-gray-50 rounded-2xl p-4 space-y-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center border border-gray-100 text-gray-400">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Kelas</p>
                                        <p class="text-sm font-bold text-gray-800">{{ $student->grade }} {{ $student->class_group }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center border border-gray-100 text-gray-400">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Username/Email</p>
                                        <p class="text-sm font-bold text-gray-800">{{ $student->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics / Actions -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Kredensial & Keamanan</label>
                            <div class="bg-gray-50 rounded-2xl p-4 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center border border-gray-100 text-gray-400">
                                            <i class="fas fa-key"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Password</p>
                                            <p class="text-sm font-bold text-gray-800">************</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.students.resetPassword', $student) }}" method="POST" id="reset-form">
                                        @csrf
                                        <button type="button" onclick="confirmReset()" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition-colors">
                                            Reset Baru
                                        </button>
                                    </form>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center border border-gray-100 text-gray-400">
                                            <i class="fas fa-user-shield"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-tighter">Status Login</p>
                                            <p class="text-sm font-bold text-gray-800">{{ $student->is_active ? 'Dizinkan' : 'Dilarang' }}</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.students.toggleActive', $student) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="text-xs font-bold {{ $student->is_active ? 'text-rose-600' : 'text-emerald-600' }} hover:opacity-80 transition-opacity">
                                            {{ $student->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex items-center gap-4">
                    <a href="{{ route('admin.students.edit', $student) }}" class="flex-1 px-8 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition shadow-sm hover:shadow-md active:scale-95 text-center flex items-center justify-center gap-2">
                        <i class="fas fa-edit"></i> Ubah Profil Siswa
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="px-8 py-3 bg-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-200 transition text-center flex items-center justify-center gap-2 border border-gray-200">
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Password telah disalin ke clipboard.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        }

        function confirmReset() {
            Swal.fire({
                title: 'Reset Password?',
                text: "Siswa ini tidak akan bisa login dengan password lama. Password baru akan langsung digenerate!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#888',
                confirmButtonText: 'Ya, Reset Sekarang!',
                cancelButtonText: 'Batal',
                customClass: {
                    container: 'swal2-modern',
                    popup: 'rounded-3xl',
                    confirmButton: 'rounded-xl font-bold px-6 py-3',
                    cancelButton: 'rounded-xl font-bold px-6 py-3'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('reset-form').submit();
                }
            })
        }
    </script>

    <style>
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bounceShort {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .animate-bounce-short { animation: bounceShort 2s ease-in-out infinite; }
    </style>
@endsection
