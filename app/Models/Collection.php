<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    // Pas de timestamps pour cette table
    public $timestamps = false;

    // Les champs qu'on peut remplir directement
    protected $fillable = [
        'user_id',
        'card_id',
        'nbCard',
    ];

    // Une collection appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Une collection contient une carte
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
