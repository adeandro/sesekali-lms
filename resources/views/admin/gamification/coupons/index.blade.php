@extends('layouts.app')

@section('title', 'List Kupon & Penukaran Hadiah Fisik')

@section('content')
<div class="space-y-6">
    <!-- Header Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-500 mb-3">
                <i class="fas fa-ticket-alt text-xl"></i>
            </div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Kupon Diterbitkan</p>
            <h3 class="text-3xl font-black text-gray-800 leading-none">{{ $coupons->count() }}</h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center text-green-500 mb-3">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Sudah Diklaim</p>
            <h3 class="text-3xl font-black text-gray-800 leading-none">{{ $coupons->where('status', 'used')->count() }}</h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 mb-3">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Menunggu Klaim</p>
            <h3 class="text-3xl font-black text-gray-800 leading-none">{{ $coupons->where('status', 'active')->count() }}</h3>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden" x-data="{ searchQuery: '', filterStatus: 'all' }">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-gray-800">Daftar Voucher</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola dan proses penukaran voucher hadiah siswa.</p>
            </div>
            <div class="flex items-center gap-3">
                <select x-model="filterStatus" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                    <option value="all">Semua Status</option>
                    <option value="active">Active (Menunggu Klaim)</option>
                    <option value="used">Used (Sudah Diklaim)</option>
                </select>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" x-model="searchQuery" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5" placeholder="Cari Vocher / Nama Siswa">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-400 uppercase bg-gray-50/50">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Kode Voucher</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Siswa (Pemilik)</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Deskripsi Hadiah</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Aksi / Info Klaim</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-gray-50/50 transition-colors" 
                            x-show="(filterStatus === 'all' || '{{ $coupon->status }}' === filterStatus) && 
                                    ('{{ strtolower($coupon->code . ' ' . $coupon->user->name . ' ' . $coupon->description) }}'.includes(searchQuery.toLowerCase()))">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-gray-800 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200">{{ $coupon->code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ substr($coupon->user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800">{{ $coupon->user->name }}</div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">{{ $coupon->room ? $coupon->room->name : 'Unknown Room' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-700">
                                {{ $coupon->description }}
                            </td>
                            <td class="px-6 py-4">
                                @if($coupon->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 border border-amber-200">
                                        <i class="fas fa-circle text-[8px] text-amber-500 animate-pulse"></i> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-500 border border-gray-200">
                                        <i class="fas fa-check text-[10px]"></i> Used
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($coupon->status === 'active')
                                    <form action="{{ route('admin.gamification.coupons.claim', $coupon) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin voucher ini telah diserahkan fisik/hadiahnya ke siswa? Proses ini tidak dapat dibatalkan.');">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                                            <i class="fas fa-qrcode mr-1"></i> Proses Klaim
                                        </button>
                                    </form>
                                @else
                                    <div class="text-xs text-gray-500">
                                        <div>Diklaim pkl <strong>{{ $coupon->redeemed_at->format('H:i, d M Y') }}</strong></div>
                                        <div class="mt-1 flex items-center shadow-sm border border-gray-100 bg-white rounded-md px-2 py-1 gap-1.5 max-w-fit">
                                            <i class="fas fa-user-shield text-[10px] text-gray-400"></i>
                                            <span class="font-medium text-[10px] truncate max-w-[120px]" title="{{ $coupon->redeemer ? $coupon->redeemer->name : 'Unknown' }}">{{ $coupon->redeemer ? explode(' ', $coupon->redeemer->name)[0] : 'Unknown' }}</span>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-ticket-alt text-2xl text-gray-300"></i>
                                </div>
                                <p class="text-gray-500 font-medium">Belum ada kupon hadiah fisik yang diterbitkan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
