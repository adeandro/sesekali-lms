<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'slot_number',
        'title',
        'original_filename',
        'storage_path',
        'file_size_bytes',
        'has_css',
        'has_js',
        'has_images',
        'has_audio',
        'has_video',
        'uploaded_at',
    ];

    protected $casts = [
        'slot_number'     => 'integer',
        'file_size_bytes' => 'integer',
        'has_css'         => 'boolean',
        'has_js'          => 'boolean',
        'has_images'      => 'boolean',
        'has_audio'       => 'boolean',
        'has_video'       => 'boolean',
        'uploaded_at'     => 'datetime',
    ];

    // Relations
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ProjectAssignment::class, 'assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Helpers
    public function getStorageBasePath(): string
    {
        return storage_path('app/' . rtrim($this->storage_path, '/') . '/');
    }

    public function getIndexUrl(): string
    {
        return route('gallery.serve', [
            'assignment' => $this->assignment?->slug ?? $this->assignment_id,
            'studentId'  => $this->student_id,
            'slot'       => $this->slot_number,
            'filePath'   => 'index.html',
        ]);
    }

    public function fileSizeFormatted(): string
    {
        $bytes = (int) $this->file_size_bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }
}
