<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'color',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'achievement_user')
            ->withPivot('achieved_at')
            ->withTimestamps();
    }
}
