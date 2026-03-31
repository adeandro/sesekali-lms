@extends('layouts.app')

@section('title', 'Pilih Penerima - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Generate Surat')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-fadeIn pb-20" x-data="{ search: '', selected: null }">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-4">
        <nav class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
            <a href="{{ route('admin.letters.index') }}" class="hover:text-indigo-600 transition-colors">Generator Surat</a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="text-indigo-600">Pilih Penerima</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-user-check text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Pilih Penerima</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">
                        Template: <span class="text-indigo-600">{{ $template->name }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.letters.preview', $template) }}" method="POST" id="generateForm" class="space-y-8">
        @csrf
        
        <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 space-y-10" x-data="{ format: 'simple' }">
            
            <!-- Format Surat -->
            <div class="space-y-4">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">
                    Format Nomor Surat
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative flex flex-col p-5 rounded-2xl border-2 transition-all cursor-pointer group"
                           :class="format == 'simple' ? 'bg-indigo-50 border-indigo-600' : 'bg-gray-50 border-transparent hover:bg-gray-100'">
                        <input type="radio" name="format_type" value="simple" class="hidden" x-model="format">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                 :class="format == 'simple' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 bg-white'">
                                <i class="fas fa-check text-[10px] text-white" x-show="format == 'simple'"></i>
                            </div>
                            <span class="text-sm font-bold" :class="format == 'simple' ? 'text-indigo-900' : 'text-gray-900'">Simple Format / Standar</span>
                        </div>
                        <p class="text-[10px] font-bold text-gray-500 mt-2 pl-8">Format sederhana, cocok untuk penggunaan internal sekolah (Contoh: 003/SEd/002/I/2021).</p>
                    </label>
                    
                    <label class="relative flex flex-col p-5 rounded-2xl border-2 transition-all cursor-pointer group"
                           :class="format == 'with_institution' ? 'bg-indigo-50 border-indigo-600' : 'bg-gray-50 border-transparent hover:bg-gray-100'">
                        <input type="radio" name="format_type" value="with_institution" class="hidden" x-model="format">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                 :class="format == 'with_institution' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 bg-white'">
                                <i class="fas fa-check text-[10px] text-white" x-show="format == 'with_institution'"></i>
                            </div>
                            <span class="text-sm font-bold" :class="format == 'with_institution' ? 'text-indigo-900' : 'text-gray-900'">With Institution / Resmi</span>
                        </div>
                        <p class="text-[10px] font-bold text-gray-500 mt-2 pl-8">Menyertakan kode identitas instansi sekolah (Contoh: 003/SEd.SMK.A/002/I/2021).</p>
                    </label>
                </div>
            </div>

            <hr class="border-gray-100 border-dashed border-2">

            <!-- Search & Filter -->
            <div class="relative">
                <input type="text" 
                       x-model="search"
                       placeholder="Cari nama atau nomor induk..." 
                       class="w-full px-8 py-5 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300">
                <i class="fas fa-search absolute right-8 top-1/2 -translate-y-1/2 text-gray-300"></i>
            </div>

            <!-- Recipients List -->
            <div class="space-y-4">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">
                    Daftar Calon Penerima 
                    <span class="text-indigo-600" x-text="'(' + $recipients.filter(r => r.name.toLowerCase().includes(search.toLowerCase()) || r.info.toLowerCase().includes(search.toLowerCase())).length + ' orang)'"></span>
                </label>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[500px] overflow-y-auto custom-scrollbar pr-2">
                    @foreach($recipients as $recipient)
                    <label class="relative flex items-center p-5 rounded-2xl border-2 transition-all cursor-pointer group"
                           :class="selected == '{{ $recipient['id'] }}' ? 'bg-indigo-50 border-indigo-600' : 'bg-gray-50 border-transparent hover:bg-gray-100'"
                           x-show="'{{ strtolower($recipient['name']) }}'.includes(search.toLowerCase()) || '{{ strtolower($recipient['info']) }}'.includes(search.toLowerCase())">
                        <input type="radio" 
                               name="recipient_id" 
                               value="{{ $recipient['id'] }}" 
                               class="hidden"
                               @change="selected = '{{ $recipient['id'] }}'"
                               {{ $loop->first ? 'required' : '' }}>
                        
                        <div class="flex items-center gap-4 w-full">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                                 :class="selected == '{{ $recipient['id'] }}' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-400'">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] font-black text-gray-900 truncate uppercase tracking-tight"
                                     :class="selected == '{{ $recipient['id'] }}' ? 'text-indigo-900' : ''">
                                    {{ $recipient['name'] }}
                                </div>
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">
                                    {{ $recipient['info'] }}
                                </div>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                 :class="selected == '{{ $recipient['id'] }}' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-200 bg-white'">
                                <i class="fas fa-check text-[10px] text-white" x-show="selected == '{{ $recipient['id'] }}'"></i>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
                
                @if($recipients->isEmpty())
                <div class="py-12 text-center space-y-4 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-100">
                    <div class="w-16 h-16 rounded-full bg-white mx-auto flex items-center justify-center text-gray-200 shadow-sm">
                        <i class="fas fa-user-slash text-2xl"></i>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Tidak ada data penerima yang tersedia
                    </p>
                </div>
                @endif
            </div>

            <!-- Submit Section -->
            <div class="pt-8 border-t border-gray-50">
                <button type="submit" 
                        class="w-full h-16 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-indigo-700 transition shadow-2xl shadow-indigo-500/20 flex items-center justify-center gap-4 group disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!selected">
                    <i class="fas fa-eye group-hover:rotate-12 transition-transform"></i> 
                    Preview Surat Sekarang
                </button>
            </div>
        </div>
    </form>
</div>

<style>
/* Custom scrollbar untuk list penerima */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f9fafb;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>
@endsection
