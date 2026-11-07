<?php

namespace App\Console\Commands;

use App\Models\CtClass;
use App\Models\CtTeam;
use App\Services\CaseGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CtReshuffle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ct:reshuffle
                            {kelas? : Nama kelas (opsional, contoh: 10-A)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reshuffle log lines and folder files for unopened CT teams with new randomized rules';

    public function __construct(private readonly CaseGenerator $generator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $className = $this->argument('kelas');

        $query = CtClass::query();
        if ($className) {
            $query->where('name', $className);
        }

        $classes = $query->orderBy('name')->get();

        if ($classes->isEmpty()) {
            if ($className) {
                $this->error("Kelas '{$className}' tidak ditemukan.");
            } else {
                $this->info("Tidak ada kelas yang ditemukan.");
            }

            return self::FAILURE;
        }

        $updated = [];
        $skipped = [];

        foreach ($classes as $class) {
            $teams = $class->teams()->orderBy('number')->get();

            if ($teams->isEmpty()) {
                continue;
            }

            // Collect used perpetrator positions from teams that are ALREADY opened
            $usedPositions = [];
            foreach ($teams as $team) {
                if ($team->opened_at !== null && is_array($team->params)) {
                    $pos = $this->generator->getPerpetratorIndex($team->params);
                    if ($pos !== null) {
                        $usedPositions[] = $pos;
                    }
                }
            }

            foreach ($teams as $team) {
                if ($team->opened_at !== null) {
                    $skipped[] = "Kelas {$class->name} - Tim {$team->number} (sudah dibuka pada {$team->opened_at->format('d/m/Y H:i')})";
                    continue;
                }

                DB::transaction(function () use ($team, &$usedPositions, &$updated, $class) {
                    $newParams = $this->generator->reshuffleParams($team->params, $usedPositions);
                    $team->update(['params' => $newParams]);

                    $pos = $this->generator->getPerpetratorIndex($newParams);
                    if ($pos !== null) {
                        $usedPositions[] = $pos;
                    }

                    $updated[] = "Kelas {$class->name} - Tim {$team->number}";
                });
            }
        }

        $this->newLine();
        if (! empty($updated)) {
            $this->info("Berhasil me-reshuffle " . count($updated) . " kelompok:");
            foreach ($updated as $item) {
                $this->line("  ✓ {$item}");
            }
        } else {
            $this->info("Tidak ada kelompok yang perlu di-reshuffle.");
        }

        if (! empty($skipped)) {
            $this->newLine();
            $this->warn("Dilewati (" . count($skipped) . " kelompok sudah terbuka):");
            foreach ($skipped as $item) {
                $this->line("  - {$item}");
            }
        }

        return self::SUCCESS;
    }
}
