<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@localhost',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        // Create admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@localhost',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create 50 students with NIS and grade/class_group
        $classes = ['10A', '10B', '10C', '11A', '11B', '11C', '12A', '12B', '12C'];
        $classesGrade12 = ['12A', '12B', '12C'];
        $baseNis = 202401;
        $firstNames = ['Ahmad', 'Budi', 'Citra', 'Dewi', 'Eka', 'Firman', 'Gita', 'Hendra', 'Indah', 'Joko', 'Kiki', 'Lina', 'Maman', 'Nina', 'Oka', 'Putri', 'Quasi', 'Rini', 'Sarif', 'Tina', 'Ujang', 'Vika', 'Wati', 'Xena', 'Yenni', 'Zahra'];
        $lastNames = ['Wijaya', 'Kusuma', 'Sutrisno', 'Rahman', 'Santoso', 'Setiawan', 'Handoko', 'Gunawan', 'Pratama', 'Kurniawan', 'Hernandez', 'Martinez', 'Garcia', 'Rodriguez', 'Puspita', 'Nugraha', 'Permana', 'Budiman', 'Wijaksono', 'Kartono'];

        // Create first 50 students
        for ($i = 1; $i <= 50; $i++) {
            $firstName = $firstNames[($i - 1) % count($firstNames)];
            $lastName = $lastNames[($i - 1) % count($lastNames)];
            $name = $firstName . ' ' . $lastName;

            $class = $classes[($i - 1) % count($classes)];
            $grade = substr($class, 0, -1); // Get all but last character (e.g., "10" from "10A")
            $classGroup = substr($class, -1); // Get last character (e.g., "A" from "10A")

            User::create([
                'name' => $name,
                'email' => "student" . str_pad($i, 2, '0', STR_PAD_LEFT) . "@school.local",
                'password' => Hash::make('password'),
                'nis' => (string)($baseNis + $i),
                'grade' => $grade,
                'class_group' => $classGroup,
                'role' => 'student',
                'is_active' => true,
            ]);
        }

        // Create 30 additional students for grade 12
        for ($i = 51; $i <= 80; $i++) {
            $firstName = $firstNames[($i - 1) % count($firstNames)];
            $lastName = $lastNames[($i - 1) % count($lastNames)];
            $name = $firstName . ' ' . $lastName;

            $class = $classesGrade12[($i - 51) % count($classesGrade12)];
            $grade = '12';
            $classGroup = substr($class, -1); // Get last character (e.g., "A" from "12A")

            User::create([
                'name' => $name,
                'email' => "student" . str_pad($i, 2, '0', STR_PAD_LEFT) . "@school.local",
                'password' => Hash::make('password'),
                'nis' => (string)($baseNis + $i),
                'grade' => $grade,
                'class_group' => $classGroup,
                'role' => 'student',
                'is_active' => true,
            ]);
        }
    }
}
