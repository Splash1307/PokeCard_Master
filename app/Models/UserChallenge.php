<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChallenge extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'status',
        'completed_at',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'claimed_at'   => 'datetime',
        ];
    }

    // L'utilisateur participant
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Le défi concerné
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}
