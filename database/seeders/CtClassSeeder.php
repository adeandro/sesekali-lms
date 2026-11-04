<?php

namespace Database\Seeders;

use App\Models\CtClass;
use Illuminate\Database\Seeder;

class CtClassSeeder extends Seeder
{
    /**
     * Seed default classes 10-A through 10-D.
     * Idempotent: skips classes that already exist.
     */
    public function run(): void
    {
        $classes = ['10-A', '10-B', '10-C', '10-D'];

        foreach ($classes as $name) {
            CtClass::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }

        $this->command->info('CT classes seeded: ' . implode(', ', $classes));
    }
}
