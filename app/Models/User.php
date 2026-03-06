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
        'photo',
        'signature',
        'is_signature_active',
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
            'is_signature_active' => 'boolean',
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
     * The subjects assigned to the teacher.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
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
     * Get the photo URL for the student.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists('profiles/' . $this->photo)) {
            return asset('storage/profiles/' . $this->photo);
        }

        // Fallback to UI-Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random&color=fff&size=400';
    }

    /**
     * Get the signature URL.
     */
    public function getSignatureUrlAttribute(): ?string
    {
        if ($this->signature && \Illuminate\Support\Facades\Storage::disk('public')->exists('signatures/' . $this->signature)) {
            return asset('storage/signatures/' . $this->signature);
        }

        return null;
    }

    /**
     * Check if user is teacher or superadmin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'teacher']);
    }
}
