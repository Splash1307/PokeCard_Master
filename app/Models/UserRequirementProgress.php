<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRequirementProgress extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'requirement_id',
        'progress_count',
        'updated_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'updated_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // L'utilisateur concerné
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Le requirement concerné
    public function requirement()
    {
        return $this->belongsTo(ChallengeRequirement::class, 'requirement_id');
    }
}
