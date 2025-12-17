<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeRequirement extends Model
{
use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'challenge_id',
        'type',
        'set_id',
        'target_count',
    ];

    // Le défi parent
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    // Le set visé (pour OPEN_PACKS / OWN_CARDS)
    public function set()
    {
        return $this->belongsTo(Set::class);
    }

    // Les cartes cibles (si type = CARD_LIST)
    public function requirementCards()
    {
        return $this->hasMany(RequirementCard::class, 'requirement_id');
    }

    // Les progressions des utilisateurs sur ce requirement
    public function progresses()
    {
        return $this->hasMany(UserRequirementProgress::class, 'requirement_id');
    }
}
