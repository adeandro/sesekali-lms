<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $lore = 'Langkah nyata menuju puncak intelektual.';

        $achievements = [
            [
                'slug'           => 'first_blood',
                'name'           => 'First Blood',
                'title'          => 'First Blood',
                'description'    => 'Menyelesaikan ujian pertama kali.',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-fire',
                'color'          => '#f97316',
                'criteria_type'  => 'exam_count',
                'criteria_value' => '1',
                'xp_reward'      => 50,
                'is_active'      => true,
            ],
            [
                'slug'           => 'perfect_score',
                'name'           => 'Perfect Score',
                'title'          => 'Perfect Score',
                'description'    => 'Mendapatkan nilai 100 murni tanpa dongkrak.',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-star',
                'color'          => '#eab308',
                'criteria_type'  => 'final_score',
                'criteria_value' => '100',
                'xp_reward'      => 200,
                'is_active'      => true,
            ],
            [
                'slug'           => 'unstoppable',
                'name'           => 'Unstoppable',
                'title'          => 'Unstoppable',
                'description'    => 'Lulus KKM 3 kali berturut-turut.',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-bolt',
                'color'          => '#6366f1',
                'criteria_type'  => 'consecutive_pass',
                'criteria_value' => '3',
                'xp_reward'      => 150,
                'is_active'      => true,
            ],
            [
                'slug'           => 'early_bird',
                'name'           => 'Early Bird',
                'title'          => 'Early Bird',
                'description'    => 'Menjadi siswa pertama yang submit di satu sesi ujian.',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-clock',
                'color'          => '#22c55e',
                'criteria_type'  => 'first_submit',
                'criteria_value' => '1',
                'xp_reward'      => 300,
                'is_active'      => true,
            ],
            [
                'slug'           => 'the_flash',
                'name'           => 'The Flash',
                'title'          => 'The Flash',
                'description'    => 'Lulus KKM dengan waktu < 50% dari durasi ujian.',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-wind',
                'color'          => '#06b6d4',
                'criteria_type'  => 'completion_time_pct',
                'criteria_value' => '50',
                'xp_reward'      => 100,
                'is_active'      => true,
            ],
            [
                'slug'           => 'comeback_king',
                'name'           => 'Comeback King',
                'title'          => 'Comeback King',
                'description'    => 'Kenaikan nilai > 30 poin dari ujian sebelumnya.',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-crown',
                'color'          => '#8b5cf6',
                'criteria_type'  => 'score_increase',
                'criteria_value' => '30',
                'xp_reward'      => 250,
                'is_active'      => true,
            ],
            [
                'slug'           => 'night_owl',
                'name'           => 'Night Owl',
                'title'          => 'Night Owl',
                'description'    => 'Mengumpulkan ujian setelah pukul 21:00 WIB.',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-moon',
                'color'          => '#1e293b',
                'criteria_type'  => 'submission_hour',
                'criteria_value' => '21',
                'xp_reward'      => 100,
                'is_active'      => true,
            ],
            [
                'slug'           => 'social_media_king',
                'name'           => 'Social Media King',
                'title'          => 'Social Media King',
                'description'    => 'Memperbarui identitas visual (Avatar Kustom).',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-camera',
                'color'          => '#ec4899',
                'criteria_type'  => 'custom_avatar',
                'criteria_value' => '1',
                'xp_reward'      => 50,
                'is_active'      => true,
            ],
            [
                'slug'           => 'hard_worker',
                'name'           => 'Hard Worker',
                'title'          => 'Hard Worker',
                'description'    => 'Total menyelesaikan 10 ujian (akumulatif).',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-hammer',
                'color'          => '#4b5563',
                'criteria_type'  => 'exam_count',
                'criteria_value' => '10',
                'xp_reward'      => 300,
                'is_active'      => true,
            ],
            [
                'slug'           => 'scholar_warrior',
                'name'           => 'Scholar Warrior',
                'title'          => 'Scholar Warrior',
                'description'    => 'Rata-rata seluruh nilai ujian di atas 90.',
                'lore_text'      => $lore,
                'icon'           => 'fas fa-graduation-cap',
                'color'          => '#dc2626',
                'criteria_type'  => 'avg_score',
                'criteria_value' => '90',
                'xp_reward'      => 500,
                'is_active'      => true,
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
