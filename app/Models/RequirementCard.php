<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequirementCard extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'requirement_id',
        'card_id',
        'required_qty',
    ];

    // Le requirement concerné
    public function requirement()
    {
        return $this->belongsTo(ChallengeRequirement::class, 'requirement_id');
    }

    // La carte demandée
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }
}
