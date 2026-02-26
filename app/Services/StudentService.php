<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentService
{
    /**
     * Generate a secure random password
     *
     * @param int $length
     * @return string
     */
    public static function generatePassword(int $length = 10): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';

        $allChars = $uppercase . $lowercase . $numbers;
        $password = '';

        // Ensure at least one of each type
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];

        // Fill the rest randomly
        for ($i = 3; $i < $length; $i++) {
            $password .= $allChars[rand(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        $password = str_shuffle($password);

        return $password;
    }

    /**
     * Create a new student with auto-generated password and email
     *
     * @param array $data
     * @return array ['student' => User, 'password' => string]
     */
    public static function createStudent(array $data): array
    {
        $password = static::generatePassword();

        // Extract grade and class_group if class is provided in old format
        $grade = $data['grade'] ?? null;
        $classGroup = $data['class_group'] ?? null;
        
        // Auto-generate email if not provided
        $email = $data['email'] ?? null;
        if (empty($email)) {
            // Generate email based on NIS
            $email = 'student_' . $data['nis'] . '@sesekalicbt.local';
        }

        $student = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($password),
            'password_display' => $password,
            'nis' => $data['nis'],
            'grade' => $grade,
            'class_group' => $classGroup,
            'role' => 'student',
            'is_active' => true,
        ]);

        return [
            'student' => $student,
            'password' => $password,
        ];
    }

    /**
     * Reset a student's password
     *
     * @param User $student
     * @return string The new password
     */
    public static function resetPassword(User $student): string
    {
        $newPassword = static::generatePassword();

        $student->update([
            'password' => Hash::make($newPassword),
            'password_display' => $newPassword,
        ]);

        return $newPassword;
    }

    /**
     * Validate student data for creation
     *
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateStudentData(array $data): array
    {
        $errors = [];

        // NIS validation
        if (empty($data['nis'])) {
            $errors['nis'] = 'NIS is required';
        } elseif (User::where('nis', $data['nis'])->exists()) {
            $errors['nis'] = 'NIS already exists';
        }

        // Name validation
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        // Grade validation
        if (empty($data['grade'])) {
            $errors['grade'] = 'Grade is required';
        }

        // Class Group validation
        if (empty($data['class_group'])) {
            $errors['class_group'] = 'Class group is required';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }
}
