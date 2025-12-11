<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'reward',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    // Les requirements associés à ce défi
    public function requirements()
    {
        return $this->hasMany(ChallengeRequirement::class);
    }

    // Les participations des utilisateurs à ce défi
    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    // Les dons associés à ce défi
    public function challengeDonations()
    {
        return $this->hasMany(ChallengeDonation::class);
    }
}
