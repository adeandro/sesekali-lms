<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanTmpUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gallery:clean-tmp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean temporary project upload directories older than 2 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tmpDir = storage_path('app/tmp');

        if (!File::isDirectory($tmpDir)) {
            $this->info('No tmp directory found. Nothing to clean.');
            return Command::SUCCESS;
        }

        $now = time();
        $cutoff = $now - (2 * 3600); // 2 hours ago
        $deletedCount = 0;

        $directories = File::directories($tmpDir);

        foreach ($directories as $dir) {
            $lastModified = File::lastModified($dir);

            if ($lastModified < $cutoff) {
                File::deleteDirectory($dir);
                $deletedCount++;
                $this->line("Deleted expired tmp directory: " . basename($dir));
            }
        }

        $this->info("Cleaned {$deletedCount} expired temporary upload directory(ies).");

        return Command::SUCCESS;
    }
}
