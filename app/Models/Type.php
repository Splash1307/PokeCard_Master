<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    protected $table = 'types';

    public $timestamps = false;

    protected $fillable = ['name', 'logo'];

    // Relation pour les cartes qui ont ce type comme type principal
    public function primaryCards()
    {
        return $this->hasMany(Card::class, 'primaryType_id');
    }

    // Relation pour les cartes qui ont ce type comme type secondaire
    public function secondaryCards()
    {
        return $this->hasMany(Card::class, 'secondaryType_id');
    }

    // Relation globale (toutes les cartes avec ce type)
    public function cards()
    {
        return $this->hasMany(Card::class, 'primaryType_id');
    }
}
