@extends('layouts.app')

@section('title', 'Generate Massal - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Generate Massal')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 animate-fadeIn pb-20" x-data="{ 
    search: '', 
    selectedIds: [],
    recipients: {{ json_encode($recipients) }},
    get filteredRecipients() {
        return this.recipients.filter(r => 
            r.name.toLowerCase().includes(this.search.toLowerCase()) || 
            r.info.toLowerCase().includes(this.search.toLowerCase())
        );
    },
    toggleAll() {
        if (this.selectedIds.length === this.filteredRecipients.length) {
            this.selectedIds = [];
        } else {
            this.selectedIds = this.filteredRecipients.map(r => r.id.toString());
        }
    }
}">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-4">
        <nav class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
            <a href="{{ route('admin.letters.index') }}" class="hover:text-indigo-600 transition-colors">Generator Surat</a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="text-indigo-600">Generate Massal</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-100">
                    <i class="fas fa-layer-group text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Generate Massal</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">
                        Template: <span class="text-amber-600">{{ $template->name }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.letters.bulk.generate', $template) }}" method="POST" id="bulkGenerateForm" class="space-y-8">
        @csrf
        
        <!-- Warning Section -->
        <div class="bg-indigo-50 rounded-3xl p-6 border border-indigo-100 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center 
                        justify-center text-white flex-shrink-0">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-[11px] font-black text-indigo-900 uppercase tracking-widest">
                    Informasi Generate Massal
                </h4>
                <p class="text-[10px] font-bold text-indigo-700/80 leading-relaxed uppercase tracking-tight">
                    Pilih penerima yang diinginkan di bawah ini. 
                    Anda dapat menggunakan filter kelas untuk mempermudah pencarian. 
                    Hasil akan dikemas dalam satu file ZIP.
                </p>
            </div>
        </div>

        {{-- Filter Angkatan --}}
        @if($template->category === 'siswa')
        <div class="bg-amber-50 rounded-3xl p-6 border border-amber-100">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div class="flex items-center gap-3 shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-amber-600 flex items-center justify-center text-white">
                        <i class="fas fa-graduation-cap text-[10px]"></i>
                    </div>
                    <span class="text-[10px] font-black text-amber-900 uppercase tracking-widest">
                        Filter per Angkatan
                    </span>
                </div>
                <div class="flex flex-wrap gap-2 flex-1">
                    <a href="{{ route('admin.letters.bulk.form', $template) }}"
                       class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all
                              {{ !$selectedLevel ? 'bg-amber-600 text-white shadow-lg' : 'bg-white text-amber-600 hover:bg-amber-100' }}">
                        Semua
                    </a>
                    @foreach(['X', 'XI', 'XII'] as $level)
                        <a href="{{ route('admin.letters.bulk.form', $template) }}?level={{ $level }}"
                           class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all
                                  {{ $selectedLevel == $level ? 'bg-amber-600 text-white shadow-lg' : 'bg-white text-amber-600 hover:bg-amber-100' }}">
                            Angkatan {{ $level }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($template->category === 'siswa' && $classes->count() > 0)
        <div class="bg-indigo-50 rounded-3xl p-6 border border-indigo-100">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div class="flex items-center gap-3 shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                        <i class="fas fa-filter text-[10px]"></i>
                    </div>
                    <span class="text-[10px] font-black text-indigo-900 uppercase tracking-widest">
                        Filter per Kelas
                    </span>
                </div>
                <div class="flex flex-wrap gap-2 flex-1">
                    <a href="{{ route('admin.letters.bulk.form', $template) }}{{ $selectedLevel ? '?level='.$selectedLevel : '' }}"
                       class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all
                              {{ !$selectedClass ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-indigo-600 hover:bg-indigo-100' }}">
                        Semua {{ $selectedLevel ? 'Angkatan ' . $selectedLevel : '' }}
                    </a>
                    @foreach($classes as $class)
                        <a href="{{ route('admin.letters.bulk.form', $template) }}?class_id={{ $class->id }}{{ $selectedLevel ? '&level='.$selectedLevel : '' }}"
                           class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all
                                  {{ $selectedClass == $class->id ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-indigo-600 hover:bg-indigo-100' }}">
                            {{ $class->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 p-8 md:p-12 space-y-8">
            <!-- Search & Actions -->
            <div class="flex flex-col md:flex-row gap-6">
                <div class="relative flex-1">
                    <input type="text" 
                           x-model="search"
                           placeholder="Cari nama atau nomor induk..." 
                           class="w-full px-8 py-5 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm font-bold transition-all placeholder:text-gray-300">
                    <i class="fas fa-search absolute right-8 top-1/2 -translate-y-1/2 text-gray-300"></i>
                </div>
                <div class="flex gap-3">
                    <button type="button" 
                            @click="toggleAll()"
                            class="h-16 px-8 bg-gray-50 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center gap-3">
                        <i class="fas" :class="selectedIds.length === filteredRecipients.length ? 'fa-check-double' : 'fa-list-ul'"></i>
                        <span x-text="selectedIds.length === filteredRecipients.length ? 'Batalkan Semua' : 'Pilih Semua'"></span>
                    </button>
                </div>
            </div>

            <!-- Recipients Grid -->
            <div class="space-y-4">
                {{-- Counter & Warning --}}
                <div class="flex items-center justify-between px-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Pilih Penerima 
                        <span class="text-indigo-600" x-text="'(' + filteredRecipients.length + ' hasil)'"></span>
                    </label>
                    <div class="flex items-center gap-3">
                        <div class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full text-indigo-600 bg-indigo-50">
                            <span x-text="selectedIds.length"></span> Terpilih
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto custom-scrollbar pr-2">
                    <template x-for="r in filteredRecipients" :key="r.id">
                        <label class="relative flex items-center p-4 rounded-2xl border-2 transition-all cursor-pointer"
                               :class="selectedIds.includes(r.id.toString()) ? 'bg-indigo-50 border-indigo-600' : 'bg-gray-50 border-transparent hover:bg-gray-100'">
                            <input type="checkbox" 
                                   name="recipient_ids[]" 
                                   :value="r.id" 
                                   class="hidden"
                                   x-model="selectedIds">
                            
                            <div class="flex items-center gap-3 w-full">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                     :class="selectedIds.includes(r.id.toString()) ? 'bg-indigo-600 text-white' : 'bg-white text-gray-400'">
                                    <i class="fas fa-user text-[10px]"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[10px] font-black text-gray-900 truncate uppercase tracking-tight"
                                         :class="selectedIds.includes(r.id.toString()) ? 'text-indigo-900' : ''"
                                         x-text="r.name">
                                    </div>
                                    <div class="text-[8px] font-black text-gray-400 uppercase tracking-widest mt-0.5" x-text="r.info">
                                    </div>
                                </div>
                                <div class="w-4 h-4 rounded border flex items-center justify-center transition-all"
                                     :class="selectedIds.includes(r.id.toString()) ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 bg-white'">
                                    <i class="fas fa-check text-[8px] text-white" x-show="selectedIds.includes(r.id.toString())"></i>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>
                
                <div x-show="filteredRecipients.length === 0" class="py-12 text-center space-y-4 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-100">
                    <div class="w-16 h-16 rounded-full bg-white mx-auto flex items-center justify-center text-gray-200 shadow-sm">
                        <i class="fas fa-search text-2xl"></i>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Penerima tidak ditemukan
                    </p>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="pt-8 border-t border-gray-50">
                <button type="submit" 
                        class="w-full h-16 bg-amber-600 text-white text-[11px] 
                               font-black uppercase tracking-widest rounded-2xl 
                               hover:bg-amber-700 transition shadow-2xl 
                               shadow-amber-500/20 flex items-center justify-center 
                               gap-4 group disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="selectedIds.length === 0"
                        @click.prevent="$el.closest('form').submit()">
                    <i class="fas fa-file-archive group-hover:rotate-12 transition-transform"></i>
                    <span>Generate & Download ZIP (<span x-text="selectedIds.length"></span>)</span>
                </button>
            </div>
        </div>
    </form>
</div>

<style>
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
