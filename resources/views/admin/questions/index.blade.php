@extends('layouts.app')

@section('title', 'Bank Soal - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Manajemen Bank Soal')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 animate-fadeIn pb-12">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col gap-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-layer-group text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Bank Soal</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Kelola, impor, dan organisasi butir soal sistem</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.questions.create') }}" class="h-14 px-8 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-3 group">
                    <i class="fas fa-plus text-sm group-hover:rotate-90 transition-transform"></i> Tambah Soal
                </a>
                <a href="{{ route('admin.questions.importForm') }}" class="h-14 px-8 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-emerald-600 hover:text-white transition flex items-center gap-3 border border-emerald-100 group">
                    <i class="fas fa-file-import text-sm group-hover:translate-x-1 transition-transform"></i> Impor
                </a>
                <a href="{{ route('admin.questions.export', request()->query()) }}" class="h-14 px-8 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-amber-600 hover:text-white transition flex items-center gap-3 border border-amber-100 group">
                    <i class="fas fa-file-export text-sm group-hover:-translate-y-1 transition-transform"></i> Ekspor
                </a>
                <button type="button" id="bulkDeleteBtn" class="hidden h-14 px-8 bg-rose-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-rose-700 transition shadow-lg shadow-rose-100 animate-slideUp items-center gap-3">
                    <i class="fas fa-trash-alt text-sm"></i> Hapus Terpilih
                </button>
                <form id="deleteAllQuestionsForm" action="{{ route('admin.questions.deleteAll') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteAllQuestions()" class="h-14 px-8 bg-gray-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-black transition shadow-xl group">
                        <i class="fas fa-radiation text-rose-500 mr-2 group-hover:animate-pulse"></i> Kosongkan
                    </button>
                </form>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden group">
            <div class="p-8">
                <form action="{{ route('admin.questions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-4">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Cari Pertanyaan / Topik</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="block w-full pl-12 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300"
                                placeholder="Ketik kata kunci...">
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Mata Pelajaran</label>
                        <select name="subject" class="block w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold cursor-pointer appearance-none">
                            <option value="">Semua Mapel</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Kelas</label>
                        <select name="jenjang" class="block w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold cursor-pointer appearance-none">
                            <option value="">Semua Kelas</option>
                            <option value="10" {{ request('jenjang') == '10' ? 'selected' : '' }}>Kelas 10</option>
                            <option value="11" {{ request('jenjang') == '11' ? 'selected' : '' }}>Kelas 11</option>
                            <option value="12" {{ request('jenjang') == '12' ? 'selected' : '' }}>Kelas 12</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Kesulitan</label>
                        <select name="difficulty" class="block w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold cursor-pointer appearance-none">
                            <option value="">Semua Level</option>
                            <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Mudah</option>
                            <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Sedang</option>
                            <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Sulit</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2 flex items-end">
                        <button type="submit" class="w-full h-[3.25rem] bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-600 hover:text-white transition-all duration-300 flex items-center justify-center gap-2 group/btn">
                            <i class="fas fa-filter group-hover:rotate-12 transition-transform"></i> Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 overflow-hidden relative group">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-50 hidden items-center justify-center">
            <div class="w-16 h-16 border-4 border-indigo-50 border-t-indigo-600 rounded-full animate-spin"></div>
        </div>

        <form id="bulkDeleteForm" action="{{ route('admin.questions.bulkDelete') }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-50">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="w-20 px-8 py-6">
                                <div class="flex items-center">
                                    <input type="checkbox" id="selectAllCheckbox" class="w-6 h-6 text-indigo-600 bg-white border-2 border-gray-200 rounded-lg focus:ring-indigo-500 focus:ring-offset-0 transition-all cursor-pointer">
                                </div>
                            </th>
                            <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-left">Topik & Mapel</th>
                            <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Info</th>
                            <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-left">Pratinjau Pertanyaan</th>
                            <th class="px-10 py-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($questions as $question)
                            <tr class="hover:bg-indigo-50/20 transition-all duration-300 group/row">
                                <td class="px-8 py-6">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="question-checkbox w-6 h-6 text-indigo-600 bg-white border-2 border-gray-200 rounded-lg focus:ring-indigo-500 focus:ring-offset-0 transition-all cursor-pointer group-hover/row:border-indigo-300">
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-black text-gray-900 group-hover/row:text-indigo-600 transition-colors uppercase tracking-tight">{{ $question->topic }}</span>
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $question->subject->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <span class="px-3 py-1 bg-gray-50 text-gray-500 text-[9px] font-black uppercase tracking-widest rounded-full border border-gray-100">
                                            KLS {{ $question->jenjang ?? '?' }}
                                        </span>
                                        <span class="px-3 py-1 {{ $question->question_type === 'multiple_choice' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-purple-50 text-purple-600 border-purple-100' }} text-[9px] font-black uppercase tracking-widest rounded-full border">
                                            {{ $question->question_type === 'multiple_choice' ? 'PG' : 'ESAI' }}
                                        </span>
                                        <span class="px-3 py-1 {{ match($question->difficulty_level) {
                                            'easy' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'medium' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            'hard' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        } }} text-[9px] font-black uppercase tracking-widest rounded-full border">
                                            {{ match($question->difficulty_level) { 'easy' => 'MUDAH', 'medium' => 'SEDANG', 'hard' => 'SULIT' } }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 max-w-sm">
                                    <div class="text-[11px] font-bold text-gray-500 line-clamp-2 leading-relaxed opacity-70 group-hover/row:opacity-100 transition-opacity">
                                        {!! Str::limit(strip_tags($question->question_text), 150) !!}
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.questions.show', $question) }}" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 rounded-2xl hover:bg-white hover:text-indigo-600 hover:shadow-lg hover:shadow-indigo-500/10 transition-all duration-300" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.questions.edit', $question) }}" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 rounded-2xl hover:bg-white hover:text-amber-500 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <button type="button" onclick="deleteQuestion({{ $question->id }})" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 rounded-2xl hover:bg-white hover:text-rose-600 hover:shadow-lg hover:shadow-rose-500/10 transition-all duration-300" title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                        <form id="deleteForm{{ $question->id }}" action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-32 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-24 h-24 rounded-[2.5rem] bg-gray-50 flex items-center justify-center text-gray-200 mb-8 animate-pulse">
                                            <i class="fas fa-layer-group text-5xl"></i>
                                        </div>
                                        <h3 class="text-xl font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Soal Tidak Ditemukan</h3>
                                        <p class="text-[11px] font-bold text-gray-300 uppercase tracking-widest max-w-sm leading-relaxed">Belum ada butir soal yang sesuai dengan kriteria filter Anda. Silakan tambah data baru atau impor soal.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- Pagination -->
    <div class="mt-10">
        {{ $questions->withQueryString()->links() }}
    </div>
</div>

<script>
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const questionCheckboxes = document.querySelectorAll('.question-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const overlay = document.getElementById('loadingOverlay');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            questionCheckboxes.forEach(cb => {
                cb.checked = this.checked;
                const row = cb.closest('tr');
                if (this.checked) {
                    row.classList.add('bg-indigo-50/50');
                } else {
                    row.classList.remove('bg-indigo-50/50');
                }
            });
            updateBulkActions();
        });
    }

    questionCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.classList.add('bg-indigo-50/50');
            } else {
                row.classList.remove('bg-indigo-50/50');
            }
            updateBulkActions();
        });
    });

    function updateBulkActions() {
        const checkedCount = Array.from(questionCheckboxes).filter(cb => cb.checked).length;
        if (checkedCount > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteBtn.classList.add('flex');
            bulkDeleteBtn.innerHTML = `<i class="fas fa-trash-alt text-sm"></i> Hapus ${checkedCount} Soal`;
        } else {
            bulkDeleteBtn.classList.add('hidden');
            bulkDeleteBtn.classList.remove('flex');
        }
    }

    bulkDeleteBtn.addEventListener('click', () => {
        const count = Array.from(questionCheckboxes).filter(cb => cb.checked).length;
        Swal.fire({
            title: '<span class="text-xl font-black uppercase tracking-widest">Hapus Soal Terpilih?</span>',
            html: `<p class="text-sm font-bold text-gray-500 uppercase tracking-tight leading-relaxed">Anda akan menghapus <span class="text-rose-600 underline">${count} butir soal</span> secara permanen dari sistem.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'YA, HAPUS SEKARANG',
            cancelButtonText: 'BATALKAN',
            customClass: {
                popup: 'rounded-[2.5rem] border-none shadow-2xl p-10',
                confirmButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest mr-4',
                cancelButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                document.getElementById('bulkDeleteForm').submit();
            }
        });
    });

    function deleteQuestion(id) {
        Swal.fire({
            title: '<span class="text-xl font-black uppercase tracking-widest">Hapus Soal?</span>',
            html: `<p class="text-sm font-bold text-gray-500 uppercase tracking-tight leading-relaxed">Butir soal ini akan dihapus secara permanen dari bank soal.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'YA, HAPUS PERMANEN',
            cancelButtonText: 'BATALKAN',
            customClass: {
                popup: 'rounded-[2.5rem] border-none shadow-2xl p-10',
                confirmButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest mr-4',
                cancelButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                document.getElementById('deleteForm' + id).submit();
            }
        });
    }

    function confirmDeleteAllQuestions() {
        Swal.fire({
            title: '<span class="text-xl font-black text-rose-600 uppercase tracking-widest">⚠ TINDAKAN KRITIKAL!</span>',
            html: `
                <div class="text-center space-y-6 py-4">
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-tight leading-relaxed">Anda akan menghapus <span class="text-rose-600 underline">SELURUH</span> isi bank soal di sistem.</p>
                    <div class="bg-rose-50 border-2 border-rose-100 p-6 rounded-[2rem] shadow-sm">
                        <p class="font-black text-rose-900 mb-4 text-[10px] uppercase tracking-widest">EFEK TINDAKAN:</p>
                        <ul class="text-[10px] text-rose-800 space-y-3 font-black uppercase tracking-wider text-left pl-4 list-disc italic">
                            <li>SEMUA BUTIR SOAL AKAN HILANG PERMANEN</li>
                            <li>SEMUA LAMPIRAN GAMBAR IKUT TERHAPUS</li>
                            <li>DATA UJIAN TERKAIT AKAN TERDAMPAK</li>
                        </ul>
                    </div>
                    <div class="space-y-3">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ketik <span class="text-gray-900 bg-white px-2 py-1 rounded">HAPUS SEMUA</span> untuk konfirmasi:</p>
                        <input type="text" id="confirmInput" placeholder="HAPUS SEMUA" class="w-full px-6 py-4 bg-white border-2 border-rose-100 rounded-2xl focus:ring-4 focus:ring-rose-500/10 text-sm font-black text-center uppercase tracking-widest">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#be123c',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'EKSEKUSI PENGHAPUSAN',
            cancelButtonText: 'BATALKAN',
            customClass: {
                popup: 'rounded-[3rem] border-none shadow-2xl p-10',
                confirmButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest mr-4',
                cancelButton: 'rounded-2xl font-black px-8 py-4 text-[10px] uppercase tracking-widest'
            },
            preConfirm: () => {
                if (document.getElementById('confirmInput').value !== 'HAPUS SEMUA') {
                    Swal.showValidationMessage('Teks konfirmasi tidak sesuai!');
                    return false;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                document.getElementById('deleteAllQuestionsForm').submit();
            }
        });
    }
</script>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    .animate-slideUp { animation: slideUp 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
