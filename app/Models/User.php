<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_display',
        'role',
        'is_active',
        'nis',
        'grade',
        'class_group',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get exam attempts for this student.
     */
    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class, 'student_id');
    }

    /**
     * Get exam sessions for this student.
     */
    public function examSessions()
    {
        return $this->hasMany(ExamSession::class, 'student_id');
    }

    /**
     * Get tokens used by this student.
     */
    public function usedTokens()
    {
        return $this->hasMany(ExamToken::class, 'used_by');
    }

    /**
     * Get action logs performed by this admin.
     */
    public function actionLogs()
    {
        return $this->hasMany(ActionLog::class, 'admin_id');
    }

    /**
     * Check if user is admin or superadmin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }
}
