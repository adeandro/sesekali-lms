<?php

namespace App\Http\Controllers\Admin;

use App\Models\Exam;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TokenController extends Controller
{
    /**
     * Display all exams with their global tokens.
     * Sistem: 1 Exam = 1 Global Token
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');

        $query = Exam::query();

        // Scoping for Teacher
        if (auth()->user()->role === 'teacher') {
            $mySubjectIds = auth()->user()->subjects->pluck('id');
            $query->whereIn('subject_id', $mySubjectIds);
        }

        $exams = $query->when($search, function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('token', 'like', '%' . $search . '%');
            })
            ->when($status === 'active', function ($query) {
                return $query->where('status', 'published')
                    ->whereNotNull('token');
            })
            ->when($status === 'inactive', function ($query) {
                return $query->where(function ($q) {
                    $q->where('status', '!=', 'published')
                        ->orWhereNull('token');
                });
            })
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('admin.tokens.index', compact('exams', 'search', 'status'));
    }

    /**
     * Get tokens for an exam - for API endpoint.
     */
    public function listTokens(Exam $exam)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized subject access'], 403);
        }

        if (!$exam->token) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian ini belum memiliki token atau belum dipublikasikan',
                'token' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'exam_id' => $exam->id,
            'exam_title' => $exam->title,
            'token' => $exam->token,
            'status' => $exam->status,
            'created_at' => $exam->token_last_updated ? $exam->token_last_updated->format('Y-m-d H:i:s') : null,
            'minutes_until_refresh' => $exam->minutesUntilTokenRefresh(),
            'next_refresh_at' => $exam->tokenRefreshTime()?->format('Y-m-d H:i:s'),
            'needs_refresh' => $exam->tokenNeedsRefresh(),
            'hour_until_refresh' => $exam->minutesUntilTokenRefresh() / 60,
        ]);
    }

    /**
     * Refresh token manually (admin action).
     * Generate token baru dan replace yang lama
     */
    public function refreshToken(Exam $exam)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized subject access'], 403);
        }

        if ($exam->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya ujian dengan status published yang bisa di-refresh token-nya',
            ], 422);
        }

        // Generate token baru
        $newToken = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        $exam->update([
            'token' => $newToken,
            'token_last_updated' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil di-refresh',
            'token' => $newToken,
            'token_last_updated' => $exam->token_last_updated->format('Y-m-d H:i:s'),
            'next_refresh_at' => $exam->tokenRefreshTime()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Copy token to clipboard (helper endpoint).
     */
    public function copyToken(Exam $exam)
    {
        // Security check for Teacher
        if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized subject access'], 403);
        }

        if (!$exam->token) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian ini belum memiliki token',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'token' => $exam->token,
            'message' => 'Token `' . $exam->token . '` siap dicopy',
        ]);
    }
}
