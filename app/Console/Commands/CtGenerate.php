<?php

namespace App\Console\Commands;

use App\Models\CtClass;
use App\Models\CtTeam;
use App\Services\CaseGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CtGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ct:generate
                            {kelas : Class name, e.g. 10-A}
                            {jumlah : Number of teams to generate}
                            {--force : Regenerate existing teams too (use with caution)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CT escape room teams for a class (idempotent by default)';

    public function __construct(private readonly CaseGenerator $generator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $className = $this->argument('kelas');
        $count     = (int) $this->argument('jumlah');
        $force     = $this->option('force');

        if ($count < 1 || $count > 50) {
            $this->error('Jumlah kelompok harus antara 1 dan 50.');

            return self::FAILURE;
        }

        $class = CtClass::where('name', $className)->first();
        if (! $class) {
            $this->info("Kelas '{$className}' belum ada. Membuat baru...");
            $class = CtClass::create(['name' => $className]);
        }

        $this->info("Memproses kelas {$class->name}, target {$count} kelompok...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $created  = 0;
        $skipped  = 0;
        $errors   = [];

        for ($number = 1; $number <= $count; $number++) {
            $existing = CtTeam::where('ct_class_id', $class->id)
                ->where('number', $number)
                ->first();

            if ($existing) {
                if (! $force) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // With --force, only regenerate unopened teams
                if ($existing->opened_at !== null) {
                    $this->newLine();
                    $this->warn("Kelompok {$number} sudah terbuka, dilewati.");
                    $skipped++;
                    $bar->advance();
                    continue;
                }
            }

            DB::beginTransaction();
            try {
                $params = $this->generator->generate($existing?->id);
                $data   = [
                    'ct_class_id' => $class->id,
                    'number'      => $number,
                    'params'      => $params,
                    'code_hash'   => $params['code_hash'],
                    'wrong_count' => 0,
                    'hint_count'  => 0,
                ];

                if ($existing) {
                    $existing->update($data);
                } else {
                    CtTeam::create($data);
                    $created++;
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $errors[] = "Kelompok {$number}: " . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Selesai. Dibuat: {$created}, dilewati: {$skipped}.");

        foreach ($errors as $err) {
            $this->error($err);
        }

        return empty($errors) ? self::SUCCESS : self::FAILURE;
    }
}
