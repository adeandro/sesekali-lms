<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\LearningProgress;
use App\Services\AchievementService;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    /**
     * Display list of subjects with available learning materials.
     */
    public function index()
    {
        $student = auth()->user();
        
        // Only show subjects that have published materials
        $subjects = Subject::whereHas('learningMaterials', function($q) {
            $q->where('is_published', true);
        })
        ->with(['learningMaterials' => function($q) {
            $q->where('is_published', true)->orderBy('order', 'asc');
        }])
        ->orderBy('name')
        ->get();

        return view('student.learning.index', compact('subjects'));
    }

    /**
     * Display the specified learning material with its sections.
     */
    public function show(LearningMaterial $material)
    {
        if (!$material->is_published && !in_array(auth()->user()->role, ['teacher', 'superadmin', 'principal'])) {
            abort(404);
        }

        $material->load(['subject', 'sections' => function($q) {
            $q->orderBy('order', 'asc');
        }]);

        $isCompleted = auth()->user()->learningProgress()
            ->where('learning_material_id', $material->id)
            ->exists();

        return view('student.learning.show', compact('material', 'isCompleted'));
    }

    /**
     * Mark the learning material as completed and award XP.
     */
    public function complete(LearningMaterial $material, AchievementService $achievementService)
    {
        $user = auth()->user();

        // Only students get XP
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Hanya siswa yang dapat menyelesaikan materi.'], 403);
        }

        // Check if already completed
        $progress = LearningProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'learning_material_id' => $material->id,
            ],
            [
                'completed_at' => now(),
            ]
        );

        if ($progress->wasRecentlyCreated) {
            // Award XP
            $achievementService->awardXp($user, 100);
            
            return response()->json([
                'success' => true,
                'message' => 'Selamat! Kamu mendapatkan +100 XP karena telah menyelesaikan materi ini.',
                'xp_reward' => 100,
                'new_level' => $user->current_level,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Materi sudah diselesaikan sebelumnya.',
        ]);
    }
}
