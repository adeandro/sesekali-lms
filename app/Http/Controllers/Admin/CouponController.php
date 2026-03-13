<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = RewardCoupon::with(['user', 'room', 'redeemer'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.gamification.coupons.index', compact('coupons'));
    }

    public function claim(Request $request, RewardCoupon $coupon)
    {
        if ($coupon->status !== 'active') {
            return back()->with('error', 'Kupon ini sudah digunakan sebelumnya pada ' . $coupon->redeemed_at->format('d M Y, H:i'));
        }

        $coupon->update([
            'status'      => 'used',
            'redeemed_at' => now(),
            'redeemed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Kupon berhasil diklaim & ditandai sebagai terpakai!');
    }
}
