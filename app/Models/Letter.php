<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LetterTemplate;
use App\Models\User;

class Letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id', 'letter_type_id', 'format_type', 'letter_number', 'sequence_number',
        'year', 'recipient_type', 'recipient_id', 
        'recipient_name', 'body_rendered', 'created_by', 'issued_date'
    ];

    protected $casts = [
        'issued_date' => 'date'
    ];

    public function template(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'template_id');
    }

    public function letterType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LetterType::class, 'letter_type_id');
    }

    public function recipient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
