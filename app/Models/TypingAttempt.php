<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingAttempt extends Model
{
    protected $fillable = [
        'typing_test_id', 'student_id', 'words_generated', 'words_typed',
        'characters_correct', 'characters_wrong', 'characters_total',
        'words_correct', 'wpm', 'accuracy', 'final_score',
        'status', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(TypingTest::class, 'typing_test_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
