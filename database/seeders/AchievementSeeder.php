<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            [
                'slug' => 'first_blood',
                'name' => 'First Blood',
                'description' => 'Menyelesaikan ujian pertama kali.',
                'icon' => 'fas fa-fire',
                'color' => '#f97316', // Orange 500
            ],
            [
                'slug' => 'perfect_score',
                'name' => 'Perfect Score',
                'description' => 'Mendapatkan nilai 100 murni tanpa dongkrak.',
                'icon' => 'fas fa-star',
                'color' => '#eab308', // Yellow 500
            ],
            [
                'slug' => 'unstoppable',
                'name' => 'Unstoppable',
                'description' => 'Lulus KKM 3 kali berturut-turut.',
                'icon' => 'fas fa-bolt',
                'color' => '#6366f1', // Indigo 500
            ],
            [
                'slug' => 'early_bird',
                'name' => 'Early Bird',
                'description' => 'Menjadi siswa pertama yang submit di satu sesi ujian.',
                'icon' => 'fas fa-clock',
                'color' => '#22c55e', // Green 500
            ],
            [
                'slug' => 'the_flash',
                'name' => 'The Flash',
                'description' => 'Lulus KKM dengan waktu < 50% dari durasi tersedia.',
                'icon' => 'fas fa-wind',
                'color' => '#06b6d4', // Cyan 500
            ],
            [
                'slug' => 'comeback_king',
                'name' => 'Comeback King',
                'description' => 'Kenaikan nilai > 30 poin dari ujian sebelumnya.',
                'icon' => 'fas fa-crown',
                'color' => '#8b5cf6', // Violet 500
            ],
            [
                'slug' => 'night_owl',
                'name' => 'Night Owl',
                'description' => 'Mengumpulkan ujian di atas jam 21:00.',
                'icon' => 'fas fa-moon',
                'color' => '#1e293b', // Slate 800
            ],
            [
                'slug' => 'social_media_king',
                'name' => 'Social Media King',
                'description' => 'Memperbarui identitas visual (Avatar Kustom).',
                'icon' => 'fas fa-camera',
                'color' => '#ec4899', // Pink 500
            ],
            [
                'slug' => 'hard_worker',
                'name' => 'Hard Worker',
                'description' => 'Total menyelesaikan 10 ujian (akumulatif).',
                'icon' => 'fas fa-hammer',
                'color' => '#4b5563', // Gray 600
            ],
            [
                'slug' => 'scholar_warrior',
                'name' => 'Scholar Warrior',
                'description' => 'Rata-rata seluruh nilai ujian di atas 90.',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#dc2626', // Red 600
            ],
        ];

        foreach ($achievements as $achievement) {
            \App\Models\Achievement::updateOrCreate(
                ['slug' => $achievement['slug']],
                $achievement
            );
        }
    }
}
