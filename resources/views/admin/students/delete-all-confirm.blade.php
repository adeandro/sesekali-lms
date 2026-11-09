@extends('layouts.app')

@section('title', 'Konfirmasi Hapus Semua Siswa - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Konfirmasi Hapus Semua Siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 animate-fadeIn pb-12">
    <!-- Breadcrumb & Nav -->
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
                        <span class="text-sm font-bold text-rose-600">Hapus Semua Siswa</span>
                    </div>
                </li>
            </ol>
        </nav>
        <a href="{{ route('admin.students.index') }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Siswa
        </a>
    </div>

    <!-- Error Alert from Session -->
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h4 class="font-bold text-rose-900 text-sm">Operasi Ditolak / Gagal</h4>
                <p class="text-xs text-rose-700 mt-0.5 leading-relaxed">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Critical Warning Banner -->
    <div class="p-6 bg-rose-600 text-white rounded-3xl shadow-lg shadow-rose-200 space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-skull-crossbones"></i>
            </div>
            <div>
                <h3 class="font-black text-lg uppercase tracking-wide">PERINGATAN TINGKAT TINGGI!</h3>
                <p class="text-xs text-rose-100 font-medium">Tindakan ini BERBAHAYA dan TIDAK DAPAT DIBATALKAN.</p>
            </div>
        </div>
        <p class="text-sm text-rose-50 leading-relaxed font-medium bg-black/10 p-3.5 rounded-xl border border-white/10">
            Tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong>. Untuk memperbarui data siswa gunakan <strong>Import</strong> (dicocokkan via NIS) atau <strong>Nonaktifkan</strong> siswa yang sudah lulus / mutasi.
        </p>
        <div class="flex items-center gap-2 text-xs font-bold text-rose-100 pt-1">
            <i class="fas fa-database text-amber-300"></i>
            <span>Sudah backup database dan folder foto hari ini?</span>
        </div>
    </div>

    <!-- Summary of Data to be Deleted -->
    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-6">
        <div>
            <h4 class="font-bold text-gray-900 text-base mb-1 flex items-center gap-2">
                <i class="fas fa-trash-alt text-rose-500"></i> Ringkasan Data yang Akan Dihapus
            </h4>
            <p class="text-xs text-gray-500">Berikut adalah data yang akan dihapus secara permanen dari server.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-5 bg-rose-50/70 border border-rose-100 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-rose-700 uppercase tracking-wider block">Akun Siswa</span>
                    <span class="text-xs text-rose-500">Semua siswa terdaftar</span>
                </div>
                <span class="text-3xl font-black text-rose-600 font-mono">{{ number_format($studentCount) }}</span>
            </div>
            <div class="p-5 bg-amber-50/70 border border-amber-100 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-800 uppercase tracking-wider block">Berkas Foto Siswa</span>
                    <span class="text-xs text-amber-600">File foto di storage/profiles</span>
                </div>
                <span class="text-3xl font-black text-amber-700 font-mono">{{ number_format($photoCount) }}</span>
            </div>
        </div>

        <!-- Cascaded Data Detail -->
        <div class="space-y-3">
            <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-link text-gray-400"></i> Data Terkait yang Ikut Terhapus (Cascade)
            </h5>

            @if($cascadeWarning)
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-100 text-xs text-amber-800 flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <span>{{ $cascadeWarning }}</span>
                </div>
            @endif

            @if(!empty($cascadeCounts))
                <div class="border border-gray-100 rounded-2xl overflow-hidden">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider font-bold text-[10px]">
                            <tr>
                                <th class="px-4 py-2.5">Kategori Data / Tabel</th>
                                <th class="px-4 py-2.5 text-right">Jumlah Baris Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($cascadeCounts as $tbl => $cnt)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-2 font-mono font-medium">{{ $tbl }}</td>
                                    <td class="px-4 py-2 text-right font-bold text-rose-600 font-mono">{{ number_format($cnt) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(!$cascadeWarning)
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-xs text-gray-500 italic text-center">
                    Tidak ditemukan data relasi cascade lain pada database saat ini.
                </div>
            @endif
        </div>
    </div>

    <!-- Confirmation Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-rose-100 shadow-sm space-y-6">
        <div>
            <h4 class="font-black text-rose-700 text-lg flex items-center gap-2">
                <i class="fas fa-shield-alt"></i> Pengaman Validasi Penghapusan
            </h4>
            <p class="text-xs text-gray-500 mt-1">Lengkapi kedua pengaman di bawah untuk mengaktifkan tombol hapus.</p>
        </div>

        <form action="{{ route('admin.students.deleteAll') }}" method="POST" id="form-delete-all" class="space-y-6">
            @csrf
            @method('DELETE')

            <!-- Checkbox requirement -->
            <label class="flex items-start gap-3 p-4 bg-gray-50 hover:bg-gray-100/60 transition-colors border border-gray-200 rounded-2xl cursor-pointer">
                <input type="checkbox" name="understand" id="checkbox-understand" value="1" class="w-5 h-5 mt-0.5 rounded border-gray-300 text-rose-600 focus:ring-rose-500 cursor-pointer" onchange="validateConfirmation()">
                <span class="text-xs font-bold text-gray-800 leading-snug">
                    Saya mengerti semua data di atas ikut terhapus permanen.
                </span>
            </label>

            <!-- Text match requirement -->
            <div class="space-y-2">
                <label for="confirm_text" class="block text-xs font-bold text-gray-700">
                    Ketik teks persis: <span class="font-mono text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 select-all font-black">HAPUS SEMUA SISWA</span>
                </label>
                <input type="text"
                       id="confirm_text"
                       name="confirm_text"
                       placeholder="Ketik HAPUS SEMUA SISWA di sini"
                       autocomplete="off"
                       class="w-full px-4 py-3 border border-gray-200 rounded-2xl text-sm font-mono focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition"
                       oninput="validateConfirmation()">
            </div>

            <!-- Action buttons -->
            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                <a href="{{ route('admin.students.index') }}" class="w-full sm:w-1/3 px-6 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-2xl transition text-center">
                    Batal
                </a>
                <button type="submit"
                        id="btn-submit-delete"
                        disabled
                        class="w-full sm:w-2/3 px-6 py-3.5 bg-gray-300 text-gray-500 font-black text-xs rounded-2xl transition cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="fas fa-trash-alt"></i> Saya Yakin, Hapus Semua Siswa Permanen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function validateConfirmation() {
        const checkbox = document.getElementById('checkbox-understand');
        const textInput = document.getElementById('confirm_text');
        const submitBtn = document.getElementById('btn-submit-delete');

        const isChecked = checkbox && checkbox.checked;
        const isTextMatch = textInput && textInput.value.trim() === 'HAPUS SEMUA SISWA';

        if (isChecked && isTextMatch) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            submitBtn.classList.add('bg-rose-600', 'hover:bg-rose-700', 'text-white', 'cursor-pointer', 'shadow-md', 'shadow-rose-200');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('bg-rose-600', 'hover:bg-rose-700', 'text-white', 'cursor-pointer', 'shadow-md', 'shadow-rose-200');
            submitBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
        }
    }

    document.getElementById('form-delete-all').addEventListener('submit', function(e) {
        const checkbox = document.getElementById('checkbox-understand');
        const textInput = document.getElementById('confirm_text');

        if (!checkbox.checked || textInput.value.trim() !== 'HAPUS SEMUA SISWA') {
            e.preventDefault();
            return false;
        }

        const submitBtn = document.getElementById('btn-submit-delete');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus Seluruh Data Siswa...';
    });
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
