<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rarity extends Model
{
    use HasFactory;

    // Nom de la table au singulier
    protected $table = 'rarity';

    public $timestamps = false;

    protected $fillable = ['name', 'percentageSpawn'];

    // Une rareté a plusieurs cartes
    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
