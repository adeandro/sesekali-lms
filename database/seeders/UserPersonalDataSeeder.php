<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserPersonalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Update existing students with sample personal data
        $students = User::where('role', '=', 'student')->get();
        
        $genders = ['Laki-laki', 'Perempuan'];
        $places = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Yogyakarta', 'Semarang', 'Makassar'];

        foreach ($students as $index => $student) {
            $student->update([
                'nisn' => '00' . (12345678 + $index),
                'gender' => $genders[$index % 2],
                'place_of_birth' => $places[$index % count($places)],
                'date_of_birth' => now()->subYears(15 + ($index % 3))->subDays($index * 10)->toDateString(),
            ]);
        }

        // 2. Create a sample TU (Staff) user
        User::updateOrCreate(
            ['email' => 'tu@localhost'],
            [
                'name' => 'Staf Tata Usaha',
                'password' => Hash::make('password'),
                'role' => 'tu',
                'status' => 'Aktif',
            ]
        );
    }
}
