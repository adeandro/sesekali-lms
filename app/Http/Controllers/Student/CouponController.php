<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\RewardCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = RewardCoupon::where('user_id', Auth::id())
            ->with(['room' => fn($q) => $q->select('id', 'name')])
            ->orderByDesc('created_at')
            ->get();

        return view('student.coupons.index', compact('coupons'));
    }
}
