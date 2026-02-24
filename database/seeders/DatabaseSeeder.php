<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use App\Models\Question;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users first (admin, superadmin, and 50 students)
        $this->call([
            UserSeeder::class,
        ]);

        // Create sample subjects
        $subjects = [
            'Pemrograman Web Dasar',
            'Database Design',
            'Web Security',
            'API Development',
            'Frontend Frameworks',
        ];

        $subjectModels = [];
        foreach ($subjects as $subjectName) {
            $subj = Subject::firstOrCreate(
                ['name' => $subjectName]
            );
            $subjectModels[] = $subj;

            // Create 20 sample questions for each subject
            for ($i = 1; $i <= 20; $i++) {
                // Distribute questions across all 3 grade levels
                $jenjangValues = ['10', '11', '12'];
                $jenjang = $jenjangValues[($i - 1) % 3];

                Question::firstOrCreate(
                    [
                        'subject_id' => $subj->id,
                        'topic' => "$subjectName Topic " . ceil($i / 4),
                        'question_text' => "$subjectName Question $i: Apa jawaban yang benar untuk pertanyaan ini tentang $subjectName?",
                    ],
                    [
                        'jenjang' => $jenjang,
                        'difficulty_level' => $i % 3 === 0 ? 'hard' : ($i % 2 === 0 ? 'medium' : 'easy'),
                        'question_type' => 'multiple_choice',
                        'option_a' => 'Jawaban A - Tidak benar',
                        'option_b' => 'Jawaban B - Benar',
                        'option_c' => 'Jawaban C - Tidak benar',
                        'option_d' => 'Jawaban D - Tidak benar',
                        'option_e' => 'Jawaban E - Tidak benar',
                        'correct_answer' => 'b',
                        'explanation' => "Penjelasan untuk pertanyaan $i. Jawaban B adalah yang paling tepat karena...",
                    ]
                );
            }
        }

        // Create 1 PUBLISHED exam for grade 10
        $publishedExam = Exam::firstOrCreate(
            ['title' => 'Ujian Pemrograman Web Dasar - Published'],
            [
                'subject_id' => $subjectModels[0]->id,
                'jenjang' => '10',
                'duration_minutes' => 120,
                'total_questions' => 20,
                'start_time' => Carbon::now()->subDay(),
                'end_time' => Carbon::now()->addDays(30),
                'randomize_questions' => true,
                'randomize_options' => true,
                'show_score_after_submit' => true,
                'status' => 'published',
            ]
        );

        // Attach questions matching grade 10 to published exam
        $webQuestions = Question::where('subject_id', $subjectModels[0]->id)
            ->where('jenjang', '10')
            ->limit(20)
            ->pluck('id')
            ->toArray();
        if (!empty($webQuestions)) {
            $publishedExam->questions()->sync($webQuestions);
        }

        // Create 2 DRAFT exams (one for grade 11, one for grade 12)
        $draftExam1 = Exam::firstOrCreate(
            ['title' => 'Ujian Database Design - Draft'],
            [
                'subject_id' => $subjectModels[1]->id,
                'jenjang' => '11',
                'duration_minutes' => 90,
                'total_questions' => 15,
                'start_time' => Carbon::now()->addDays(5),
                'end_time' => Carbon::now()->addDays(35),
                'randomize_questions' => false,
                'randomize_options' => false,
                'show_score_after_submit' => false,
                'status' => 'draft',
            ]
        );

        $dbQuestions = Question::where('subject_id', $subjectModels[1]->id)
            ->where('jenjang', '11')
            ->limit(15)
            ->pluck('id')
            ->toArray();
        if (!empty($dbQuestions)) {
            $draftExam1->questions()->sync($dbQuestions);
        }

        $draftExam2 = Exam::firstOrCreate(
            ['title' => 'Ujian Web Security - Draft'],
            [
                'subject_id' => $subjectModels[2]->id,
                'jenjang' => '12',
                'duration_minutes' => 100,
                'total_questions' => 18,
                'start_time' => Carbon::now()->addDays(10),
                'end_time' => Carbon::now()->addDays(40),
                'randomize_questions' => true,
                'randomize_options' => true,
                'show_score_after_submit' => true,
                'status' => 'draft',
            ]
        );

        $secQuestions = Question::where('subject_id', $subjectModels[2]->id)
            ->where('jenjang', '12')
            ->limit(18)
            ->pluck('id')
            ->toArray();
        if (!empty($secQuestions)) {
            $draftExam2->questions()->sync($secQuestions);
        }

        $this->command->info('Database seeded successfully with:');
        $this->command->info('- 1 Superadmin (superadmin@localhost)');
        $this->command->info('- 1 Admin (admin@localhost)');
        $this->command->info('- 80 Students (student01@school.local - student80@school.local)');
        $this->command->info('  • Grade 10: ~18 students');
        $this->command->info('  • Grade 11: ~17 students');
        $this->command->info('  • Grade 12: ~45 students (includes 30 additional)');
        $this->command->info('- 5 Subjects with 20 questions each (100 questions total, distributed across grades 10, 11, 12)');
        $this->command->info('- 1 Published Exam for Grade 10 (Ujian Pemrograman Web Dasar)');
        $this->command->info('- 1 Draft Exam for Grade 11 (Ujian Database Design)');
        $this->command->info('- 1 Draft Exam for Grade 12 (Ujian Web Security)');
    }
}
