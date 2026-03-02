@extends('layouts.app')

@section('title', 'Pengaturan Profil - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="space-y-10 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
        <div class="space-y-1">
            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em] italic">Keamanan & Identitas</p>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-wider flex items-center gap-3">
                <span class="w-2 h-10 bg-indigo-600 rounded-full"></span>
                Pengaturan Profil
            </h1>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard.teacher') }}" class="group px-6 py-3 bg-white border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-indigo-600 hover:border-indigo-100 transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Profile Info & Photo -->
        <div class="lg:col-span-2 space-y-10">
            <form action="{{ route('teacher.settings.profile') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden">
                @csrf
                <div class="px-10 py-8 border-b border-gray-50 bg-gray-50/30 flex items-center justify-between">
                    <h2 class="text-sm font-black text-gray-900 uppercase tracking-[0.2em] flex items-center gap-3">
                        <i class="fas fa-user-circle text-indigo-600 font-bold"></i> Informasi Dasar
                    </h2>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic">Update Terakhir: {{ $user->updated_at->format('d M Y') }}</span>
                </div>

                <div class="p-10 space-y-10">
                    <!-- Photo Upload with Preview -->
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="relative group">
                            <div class="w-32 h-32 md:w-40 md:h-40 rounded-[2.5rem] overflow-hidden border-4 border-white shadow-2xl relative">
                                <img id="photo-preview" src="{{ $user->photo_url }}" alt="Avatar" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer" onclick="document.getElementById('photo-input').click()">
                                    <i class="fas fa-camera text-white text-2xl"></i>
                                </div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg border-4 border-white">
                                <i class="fas fa-pen text-[10px]"></i>
                            </div>
                        </div>

                        <div class="flex-1 space-y-4 text-center md:text-left">
                            <div class="space-y-1">
                                <h3 class="text-lg font-black text-gray-900 uppercase tracking-wider">Foto Profil</h3>
                                <p class="text-xs font-bold text-gray-400">Gunakan format JPG, PNG atau JPEG. Maksimal 2MB.</p>
                            </div>
                            <input type="file" id="photo-input" name="photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                            <button type="button" onclick="document.getElementById('photo-input').click()" class="px-6 py-3 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition-colors">
                                Pilih File Baru
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] pl-4">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-gray-50/50 px-6 py-4 border-2 border-gray-100 rounded-2xl focus:border-indigo-600 focus:bg-white focus:outline-none transition-all font-bold text-gray-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] pl-4">NIP / Username</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" class="w-full bg-gray-50/50 px-6 py-4 border-2 border-gray-100 rounded-2xl focus:border-indigo-600 focus:bg-white focus:outline-none transition-all font-bold text-gray-900">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="px-10 py-4 bg-indigo-600 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center gap-3">
                            <i class="fas fa-save"></i> Perbarui Profil
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Security / Password -->
        <div class="lg:col-span-1 space-y-10">
            <form action="{{ route('teacher.settings.password') }}" method="POST" class="bg-indigo-900 rounded-[3rem] p-10 text-white shadow-2xl shadow-indigo-200 space-y-8 relative overflow-hidden">
                @csrf
                <!-- Decoration -->
                <div class="absolute top-0 right-0 -mr-10 -mt-10 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>

                <div class="space-y-2">
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] flex items-center gap-3">
                        <span class="w-1.5 h-6 bg-white rounded-full"></span> Ganti Password
                    </h3>
                    <p class="text-[9px] font-bold text-indigo-300 leading-relaxed italic opacity-80">Pastikan menggunakan kombinasi unik untuk keamanan sesi.</p>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-indigo-300 uppercase tracking-[0.2em] pl-2">Password Saat Ini</label>
                        <input type="password" name="current_password" class="w-full bg-white/10 px-6 py-4 border border-white/20 rounded-2xl focus:bg-white/20 focus:outline-none transition-all font-bold text-white placeholder:text-white/30" placeholder="••••••••">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-indigo-300 uppercase tracking-[0.2em] pl-2">Password Baru</label>
                        <input type="password" name="password" class="w-full bg-white/10 px-6 py-4 border border-white/20 rounded-2xl focus:bg-white/20 focus:outline-none transition-all font-bold text-white placeholder:text-white/30" placeholder="Min. 8 Karakter">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-indigo-300 uppercase tracking-[0.2em] pl-2">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="w-full bg-white/10 px-6 py-4 border border-white/20 rounded-2xl focus:bg-white/20 focus:outline-none transition-all font-bold text-white placeholder:text-white/30" placeholder="Ulangi Password Baru">
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full py-4 bg-white text-indigo-900 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:bg-indigo-50 transition-all flex items-center justify-center gap-3 group">
                        Perbarui Password <i class="fas fa-lock text-[9px] group-hover:scale-110 transition-transform"></i>
                    </button>
                </div>
            </form>

            <!-- Info Box -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 text-xs">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h4 class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Pusat Bantuan</h4>
                </div>
                <p class="text-[10px] font-bold text-gray-400 leading-relaxed italic">Jika Anda mengalami kendala saat mengubah data profil atau lupa password, silakan hubungi Administrator IT sekolah.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
