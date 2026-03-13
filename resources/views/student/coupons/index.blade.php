@extends('layouts.app')

@section('title', 'Kupon Hadiah Saya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-8 relative overflow-hidden group">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-400/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:bg-amber-400/20 transition-all duration-500"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-yellow-400/10 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2 group-hover:bg-yellow-400/20 transition-all duration-500"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
            <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-yellow-500 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/30 rotate-3 group-hover:rotate-6 transition-transform duration-300">
                <i class="fas fa-ticket-alt text-4xl text-white drop-shadow-md"></i>
            </div>
            
            <div class="text-center md:text-left">
                <h1 class="text-2xl font-black text-gray-800">Digital Coupon Wallet</h1>
                <p class="text-gray-500 font-medium mt-1">Klaim hadiah fisik yang kamu dapatkan dari Battle Arena.</p>
            </div>
            
            <div class="md:ml-auto flex items-center gap-3 bg-amber-50 px-5 py-3 rounded-2xl border border-amber-200 shadow-inner">
                <div class="text-right">
                    <p class="text-[10px] font-bold text-amber-700 uppercase tracking-widest">Total Kupon Aktif</p>
                    <p class="text-2xl font-black text-amber-900 leading-none">{{ $coupons->where('status', 'active')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-amber-500">
                    <i class="fas fa-gift text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Coupons List -->
    <div>
        @if($coupons->isEmpty())
            <div class="bg-white rounded-3xl border border-dashed border-gray-300 p-12 text-center">
                <div class="w-24 h-24 mx-auto mb-6 bg-gray-50 rounded-full flex items-center justify-center border-8 border-white shadow-sm">
                    <i class="fas fa-ticket-alt text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Belum Ada Kupon</h3>
                <p class="text-gray-500 mt-2 max-w-sm mx-auto">Kamu belum memiliki kupon hadiah. Menangkan pertandingan di Battle Arena untuk mendapatkan kupon!</p>
                <a href="{{ route('student.arena.index') }}" class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition-colors">
                    <i class="fas fa-gamepad"></i> Ke Battle Arena
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($coupons as $coupon)
                    <div class="relative bg-white rounded-[2rem] border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group {{ $coupon->status === 'used' ? 'opacity-70 grayscale-[50%]' : '' }}">
                        <!-- Perforation Line -->
                        <div class="absolute left-0 right-0 top-32 flex justify-between items-center -mx-2 z-10">
                            <div class="w-4 h-4 bg-gray-50 rounded-full border border-gray-200"></div>
                            <div class="flex-1 h-px border-t-2 border-dashed border-gray-200 mx-2"></div>
                            <div class="w-4 h-4 bg-gray-50 rounded-full border border-gray-200"></div>
                        </div>

                        <!-- Top Half (Details) -->
                        <div class="p-6 pb-8 {{ $coupon->status === 'active' ? 'bg-gradient-to-br from-amber-50 to-yellow-50' : 'bg-gray-50' }}">
                            <div class="flex justify-between items-start mb-4">
                                <div class="px-3 py-1 bg-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm {{ $coupon->status === 'active' ? 'text-amber-600 border border-amber-200' : 'text-gray-500 border border-gray-200' }}">
                                    @if($coupon->status === 'active')
                                        <i class="fas fa-circle text-amber-500 text-[8px] mr-1 animate-pulse"></i> Belum Diklaim
                                    @else
                                        <i class="fas fa-check text-gray-400 mr-1"></i> Telah Diklaim
                                    @endif
                                </div>
                                <div class="text-[10px] font-bold text-gray-400">
                                    {{ $coupon->created_at->format('d M Y') }}
                                </div>
                            </div>
                            
                            <h3 class="text-lg font-black text-gray-800 leading-tight mb-2">{{ $coupon->description }}</h3>
                            <p class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                                <i class="fas fa-trophy text-amber-500"></i>
                                Arena: {{ $coupon->room ? $coupon->room->name : 'Unknown Room' }}
                            </p>
                        </div>

                        <!-- Bottom Half (Code & Action) -->
                        <div class="p-6 pt-8 bg-white text-center relative">
                            <!-- Background Pattern -->
                            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 10px 10px;"></div>
                            
                            <div class="relative z-10">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Kode Voucher</p>
                                <div class="inline-block bg-gray-100 px-4 py-2 rounded-xl mb-5">
                                    <span class="font-mono text-xl font-black tracking-[0.2em] {{ $coupon->status === 'active' ? 'text-gray-800' : 'text-gray-400 line-through' }}">
                                        {{ $coupon->code }}
                                    </span>
                                </div>
                                
                                @if($coupon->status === 'active')
                                    <button onclick="openClaimModal('{{ $coupon->id }}', '{{ $coupon->description }}', '{{ $coupon->code }}')" class="w-full py-3.5 bg-gray-900 hover:bg-gray-800 text-white font-black text-sm uppercase tracking-widest rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                                        Gunakan Voucher
                                    </button>
                                @else
                                    <div class="w-full py-3.5 bg-gray-100 text-gray-400 font-black text-sm uppercase tracking-widest rounded-xl border border-gray-200">
                                        Sudah Digunakan
                                    </div>
                                    <p class="text-[10px] font-medium text-gray-400 mt-2">
                                        Klaim pada {{ $coupon->redeemed_at->format('d M Y, H:i') }}
                                    </p>
                                @endif
                            </div>

                            @if($coupon->status === 'used')
                                <!-- "CLAIMED" Stamp -->
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-20 overflow-hidden">
                                    <div class="border-4 border-red-500 text-red-500 text-4xl font-black uppercase tracking-widest py-2 px-6 rounded-lg -rotate-[15deg] opacity-70" style="text-shadow: 2px 2px 0px rgba(239, 68, 68, 0.2);">
                                        CLAIMED
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Claim Modal -->
<div id="claim-modal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm overflow-y-auto overflow-x-hidden p-4 sm:p-0 flex items-center justify-center" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="relative bg-white rounded-3xl shadow-2xl xl:w-full xl:max-w-md w-full transform transition-all scale-95 opacity-0 duration-300" id="claim-modal-content">
        <!-- Close Button -->
        <button type="button" onclick="closeClaimModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-full transition-colors z-10">
            <i class="fas fa-times"></i>
        </button>

        <div class="p-8 text-center pt-12">
            <div class="w-20 h-20 mx-auto bg-amber-100 text-amber-500 rounded-full flex items-center justify-center text-3xl mb-6 shadow-inner border-4 border-white ring-4 ring-amber-50 relative">
                <i class="fas fa-qrcode"></i>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 border-2 border-white rounded-full flex items-center justify-center text-white text-xs">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            
            <h3 class="text-2xl font-black text-gray-900 mb-2" id="modal-title">Konfirmasi Klaim</h3>
            <p class="text-sm font-medium text-gray-500 mb-6">Tunjukkan layar ini kepada Admin atau Petugas Kantin untuk memproses klaim voucher.</p>
            
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 mb-8">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Voucher</p>
                <p class="font-bold text-gray-800 mb-4" id="modal-desc">Description Here</p>
                
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Kode</p>
                <p class="font-mono text-xl font-black tracking-[0.2em] text-gray-800" id="modal-code">CODE</p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-left flex gap-3 text-amber-800 text-xs mb-8">
                <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                <div>
                    <strong>Peringatan Keamanan:</strong>
                    Aksi ini hanya boleh dilakukan oleh Admin/Petugas saat penukaran hadiah. Jika sudah ditekan, voucher hangus.
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeClaimModal()" class="flex-1 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openClaimModal(id, desc, code) {
        document.getElementById('modal-desc').innerText = desc;
        document.getElementById('modal-code').innerText = code;
        
        const modal = document.getElementById('claim-modal');
        const content = document.getElementById('claim-modal-content');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeClaimModal() {
        const modal = document.getElementById('claim-modal');
        const content = document.getElementById('claim-modal-content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endpush
@endsection
