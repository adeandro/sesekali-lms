@extends('layouts.app')

@section('title', 'Kelola DU/DI: ' . $student->name . ' - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Kelola Kegiatan DU/DI')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 animate-fadeIn pb-24">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.dudi.index', ['class_id' => $student->class_id, 'semester' => $semester, 'academic_year' => $academicYear]) }}" 
            class="flex items-center gap-3 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition group">
            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </div>
            Kembali ke Daftar
        </a>
        <div class="flex items-center gap-3">
            <span class="px-4 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-[10px] font-black uppercase tracking-widest border border-gray-200">
                Semester {{ $semester }}
            </span>
            <span class="px-4 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100">
                TA {{ $academicYear }}
            </span>
        </div>
    </div>

    <!-- Student Profile Header -->
    <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-10 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50/30 rounded-full -mr-32 -mt-32"></div>
        
        <div class="w-24 h-24 rounded-3xl bg-indigo-600 flex items-center justify-center text-white text-3xl font-black shadow-xl shadow-indigo-100 relative z-10 shrink-0 capitalize">
            {{ substr($student->name, 0, 1) }}
        </div>
        
        <div class="text-center md:text-left relative z-10 flex-1">
            <h2 class="text-4xl font-black text-gray-900 tracking-tight uppercase leading-none mb-4">{{ $student->name }}</h2>
            <div class="flex flex-wrap justify-center md:justify-start gap-y-4 gap-x-8">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Nomor Induk Siswa</p>
                    <p class="font-bold text-gray-700">{{ $student->nis ?? '-' }}</p>
                </div>
                <div class="w-px h-10 bg-gray-100 hidden md:block"></div>
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Kelas & Jenjang</p>
                    <p class="font-bold text-gray-700 capitalize">{{ $student->class->name ?? '-' }} (Tingkat {{ $student->jenjang ?? '-' }})</p>
                </div>
                <div class="w-px h-10 bg-gray-100 hidden md:block"></div>
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Program Keahlian</p>
                    <p class="font-bold text-gray-700">{{ $configs['program_studi'] ?? 'Default SMK' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Form -->
    <form action="{{ route('admin.dudi.update', $student->id) }}" method="POST" 
        x-data="{ 
            rows: {{ $dudis->count() > 0 ? $dudis->toJson() : '[{activity_name:\'\', institution_name:\'\', institution_address:\'\', period:\'\', grade:\'\'}]' }},
            addRow() {
                this.rows.push({activity_name: '', institution_name: '', institution_address: '', period: '', grade: ''});
            },
            removeRow(index) {
                if(this.rows.length > 1) {
                    this.rows.splice(index, 1);
                } else {
                    this.rows = [{activity_name: '', institution_name: '', institution_address: '', period: '', grade: ''}];
                }
            }
        }">
        @csrf
        @method('PUT')
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="academic_year" value="{{ $academicYear }}">

        <div class="space-y-6">
            <template x-for="(row, index) in rows" :key="index">
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-10 relative group transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/5">
                    <div class="absolute -left-3 top-10 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-[10px] font-black text-white shadow-lg" x-text="index + 1"></div>
                    
                    <button type="button" @click="removeRow(index)" 
                        class="absolute -right-3 -top-3 w-10 h-10 bg-white border border-rose-100 text-rose-500 rounded-xl shadow-lg flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all opacity-0 group-hover:opacity-100 scale-90 group-hover:scale-100 z-20">
                        <i class="fas fa-times"></i>
                    </button>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Kegiatan</label>
                            <input type="text" :name="`dudis[${index}][activity_name]`" x-model="row.activity_name" required
                                placeholder="Contoh: Prakerin Jaringan Dasar"
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold text-gray-900 transition-all">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Instansi / Perusahaan</label>
                            <input type="text" :name="`dudis[${index}][institution_name]`" x-model="row.institution_name" required
                                placeholder="Nama PT/CV/Instansi"
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold text-gray-900 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Waktu Pelaksanaan</label>
                            <input type="text" :name="`dudis[${index}][period]`" x-model="row.period" required
                                placeholder="Januari - Maret 2025"
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold text-gray-900 transition-all">
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Alamat Instansi</label>
                            <input type="text" :name="`dudis[${index}][institution_address]`" x-model="row.institution_address"
                                placeholder="Jl. Raya No. 123, Kota"
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold text-gray-900 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nilai / Predikat</label>
                            <input type="text" :name="`dudis[${index}][grade]`" x-model="row.grade"
                                placeholder="Angka (85) atau Huruf (A)"
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold text-gray-900 transition-all uppercase tracking-widest">
                        </div>
                    </div>
                </div>
            </template>

            <!-- Add Row Button -->
            <button type="button" @click="addRow()" 
                class="w-full py-8 rounded-[2.5rem] border-2 border-dashed border-gray-200 text-gray-400 hover:border-indigo-400 hover:text-indigo-600 transition-all flex items-center justify-center gap-3 group">
                <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                    <i class="fas fa-plus"></i>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em]">Tambah Baris Kegiatan Baru</span>
            </button>

            <!-- Form Actions -->
            <div class="fixed bottom-10 left-1/2 -translate-x-1/2 w-full max-w-xl px-6 z-50">
                <div class="bg-gray-900 text-white rounded-[2rem] shadow-2xl p-4 flex gap-4 border border-white/10 backdrop-blur-md">
                    <button type="submit" class="flex-1 h-14 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20">
                        SIMPAN PERUBAHAN
                    </button>
                    <a href="{{ route('admin.dudi.index', ['class_id' => $student->class_id, 'semester' => $semester, 'academic_year' => $academicYear]) }}" 
                        class="px-10 h-14 bg-white/10 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-white/20 transition flex items-center justify-center">
                        BATAL
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
