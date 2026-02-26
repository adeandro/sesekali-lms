<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixNisOverflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:nis-overflow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix NIS fields that suffered from integer overflow when imported from Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning for students with negative NIS (overflow)...');

        // Find students with negative NIS
        $negativeNisStudents = User::where('role', 'student')
            ->whereRaw('CAST(nis AS SIGNED) < 0')
            ->get();

        if ($negativeNisStudents->isEmpty()) {
            $this->info('✓ No students with negative NIS found. Database is clean!');
            return;
        }

        $this->warn('⚠ Found ' . $negativeNisStudents->count() . ' students with negative NIS:');

        foreach ($negativeNisStudents as $student) {
            $this->line("  - ID: {$student->id}, Name: {$student->name}, NIS: {$student->nis}");
        }

        $this->newLine();

        // Ask user what to do
        $action = $this->choice(
            'What would you like to do?',
            [
                'show' => 'Show detailed info only',
                'delete' => 'Delete these students (requires re-import)',
                'manual' => 'Show manual fix instructions',
            ],
            0
        );

        switch ($action) {
            case 'show':
                $this->showDetailed($negativeNisStudents);
                break;

            case 'delete':
                $this->deleteNegativeNis($negativeNisStudents);
                break;

            case 'manual':
                $this->showManualInstructions($negativeNisStudents);
                break;
        }
    }

    /**
     * Show detailed information about affected students
     */
    private function showDetailed($students)
    {
        $this->table(
            ['ID', 'Name', 'Email', 'NIS (Corrupted)', 'Grade', 'Class'],
            $students->map(fn($s) => [
                $s->id,
                $s->name,
                $s->email,
                $s->nis,
                $s->grade,
                $s->class_group,
            ])->toArray()
        );

        $this->info('These students have corrupted NIS values due to integer overflow from Excel import.');
        $this->info('Run: php artisan fix:nis-overflow --delete to remove them and re-import.');
    }

    /**
     * Delete students with negative NIS values
     */
    private function deleteNegativeNis($students)
    {
        if (!$this->confirm('Delete ' . $students->count() . ' students with negative NIS?')) {
            $this->info('Cancelled.');
            return;
        }

        $count = 0;
        foreach ($students as $student) {
            $student->delete();
            $count++;
        }

        $this->info("✓ Deleted {$count} students with corrupted NIS values.");
        $this->warn('⚠ Remember to:');
        $this->warn('  1. Fix your CSV file (format NIS column as TEXT in Excel)');
        $this->warn('  2. Re-import the students through Admin Dashboard');
    }

    /**
     * Show manual fix instructions
     */
    private function showManualInstructions($students)
    {
        $this->info('Manual Fix Instructions:');
        $this->newLine();

        $this->line('For each student, you need to find the correct NIS value.');
        $this->line('Then run in tinker:');
        $this->newLine();

        $this->warn('php artisan tinker');
        $this->warn('$student = App\\Models\\User::find(ID_HERE);');
        $this->warn('$student->update(["nis" => "CORRECT_NIS_HERE"]);');
        $this->warn('exit');
        $this->newLine();

        $this->line('Or, simply delete these students and re-import with corrected CSV:');
        $this->warn('Run: php artisan fix:nis-overflow');
        $this->warn('Then choose: Delete these students');
    }
}
