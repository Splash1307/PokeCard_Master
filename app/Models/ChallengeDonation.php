<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeDonation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'card_id',
        'qty',
        'donated_at',
    ];

    protected function casts(): array
    {
        return [
            'donated_at' => 'datetime',
        ];
    }

    // L'utilisateur qui a fait le don
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Le défi pour lequel la carte est donnée
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    // La carte donnée
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
