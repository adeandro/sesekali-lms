<?php

namespace App\Services;

use App\Models\Season;
use App\Models\Subject;
use App\Models\SubjectLeaderboard;
use Illuminate\Support\Facades\DB;

class SubjectLeaderboardService
{
    /**
     * Update/Recalculate leaderboard for a specific subject in a season.
     */
    public function update(Subject $subject, Season $season): void
    {
        // Calculate average scores per student for this subject in the season
        $performance = DB::table('exam_attempts')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->where('exams.subject_id', $subject->id)
            ->whereNotNull('exam_attempts.submitted_at')
            // Note: Season filter could be added here if exam_attempts have season_id or via dates
            ->select(
                'exam_attempts.student_id',
                DB::raw('AVG(final_score) as average_score'),
                DB::raw('COUNT(*) as exam_count')
            )
            ->groupBy('exam_attempts.student_id')
            ->orderByDesc('average_score')
            ->get();

        $rank = 1;
        foreach ($performance as $p) {
            SubjectLeaderboard::updateOrCreate(
                [
                    'season_id'  => $season->id,
                    'subject_id' => $subject->id,
                    'user_id'    => $p->student_id,
                ],
                [
                    'average_score' => $p->average_score,
                    'exam_count'    => $p->exam_count,
                    'rank'          => $rank,
                    'updated_at'    => now(),
                ]
            );
            $rank++;
        }
    }
}
