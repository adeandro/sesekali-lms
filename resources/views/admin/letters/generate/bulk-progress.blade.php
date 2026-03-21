@extends('layouts.app')

@section('title', 'Proses Generate Masal - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('content')
<div class="max-w-2xl mx-auto space-y-8 animate-fadeIn py-12"
     x-data="{
         progress: 0,
         status: 'Mempersiapkan...',
         done: false,
         error: false,
         errorMessage: '',
         downloadUrl: '',
         
         async startGenerate() {
             this.status = 'Mengirim data ke server...';
             this.progress = 5;
             
             try {
                 const formData = new FormData(
                     document.getElementById('bulkProgressForm')
                 );
                 
                 this.status = 'Memproses {{ $totalCount }} surat...';
                 this.progress = 10;
                 
                 const response = await fetch(
                     '{{ route('admin.letters.bulk.progress', $template) }}',
                     {
                         method: 'POST',
                         body: formData,
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         }
                     }
                 );

                 // Simulasi progress saat menunggu response
                 const progressInterval = setInterval(() => {
                     if (this.progress < 85) {
                         this.progress += Math.random() * 3;
                         const approxDone = Math.floor(
                             (this.progress / 85) * {{ $totalCount }}
                         );
                         this.status = `Memproses surat ${Math.min(approxDone, {{ $totalCount }})} dari {{ $totalCount }}...`;
                     }
                 }, 800);

                 const data = await response.json();
                 clearInterval(progressInterval);

                 if (data.success) {
                     this.progress = 95;
                     this.status = 'Membuat file ZIP...';
                     
                     await new Promise(r => setTimeout(r, 500));
                     
                     this.progress = 100;
                     this.status = 'Selesai! File ZIP siap didownload.';
                     this.downloadUrl = data.download_url;
                     this.done = true;
                     
                     // Auto trigger download
                     setTimeout(() => {
                         window.location.href = data.download_url;
                     }, 1000);
                 } else {
                     throw new Error(data.message || 'Terjadi kesalahan.');
                 }
             } catch (err) {
                 this.error = true;
                 this.errorMessage = err.message;
                 this.status = 'Gagal memproses surat.';
             }
         }
     }"
     x-init="startGenerate()">

    {{-- Header --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center 
                        justify-center text-amber-600 shrink-0">
                <i class="fas fa-layer-group text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase">
                    Generate Massal
                </h2>
                <p class="text-[11px] font-black text-gray-400 uppercase 
                           tracking-widest mt-1">
                    {{ $template->name }} • {{ $totalCount }} Penerima
                </p>
            </div>
        </div>
    </div>

    {{-- Progress Card --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-10 space-y-8">
        
        {{-- Status Icon --}}
        <div class="flex justify-center">
            <div x-show="!done && !error"
                 class="w-20 h-20 rounded-full bg-amber-50 flex items-center 
                         justify-center text-amber-500">
                <i class="fas fa-cog text-3xl animate-spin"></i>
            </div>
            <div x-show="done" x-cloak
                 class="w-20 h-20 rounded-full bg-emerald-50 flex items-center 
                         justify-center text-emerald-500">
                <i class="fas fa-check-circle text-3xl"></i>
            </div>
            <div x-show="error" x-cloak
                 class="w-20 h-20 rounded-full bg-rose-50 flex items-center 
                         justify-center text-rose-500">
                <i class="fas fa-times-circle text-3xl"></i>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-black text-gray-400 uppercase 
                             tracking-widest" x-text="status"></span>
                <span class="text-[10px] font-black text-gray-400 uppercase 
                             tracking-widest" 
                      x-text="Math.floor(progress) + '%'"></span>
            </div>
            <div class="h-4 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                     :class="done ? 'bg-emerald-500' : (error ? 'bg-rose-500' : 'bg-amber-500')"
                     :style="'width: ' + Math.min(Math.floor(progress), 100) + '%'">
                </div>
            </div>
        </div>

        {{-- Error Message --}}
        <div x-show="error" x-cloak
             class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
            <p class="text-[10px] font-black text-rose-600 uppercase 
                       tracking-widest" x-text="errorMessage"></p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-4 pt-4">
            <a href="{{ route('admin.letters.bulk.form', $template) }}"
               class="flex-1 h-14 bg-gray-50 text-gray-400 text-[10px] 
                       font-black uppercase tracking-widest rounded-2xl 
                       hover:bg-gray-100 transition flex items-center justify-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <a x-show="done" x-cloak
               :href="downloadUrl"
               class="flex-[2] h-14 bg-emerald-600 text-white text-[10px] 
                       font-black uppercase tracking-widest rounded-2xl 
                       hover:bg-emerald-700 transition shadow-lg 
                       shadow-emerald-100 flex items-center justify-center gap-3">
                <i class="fas fa-download"></i> Download ZIP
            </a>
        </div>
    </div>

    {{-- Hidden form dengan semua data --}}
    <form id="bulkProgressForm" class="hidden">
        @csrf
        @foreach($recipientIds as $id)
            <input type="hidden" name="recipient_ids[]" value="{{ $id }}">
        @endforeach
        @foreach($customFields as $key => $value)
            <input type="hidden" name="custom_fields[{{ $key }}]" 
                   value="{{ $value }}">
        @endforeach
        <input type="hidden" name="batch_id" value="{{ $batchId }}">
    </form>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(10px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
</style>
@endsection
