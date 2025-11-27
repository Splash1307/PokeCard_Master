<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    // Nom de la table au pluriel
    protected $table = 'types';

    public $timestamps = false;

    protected $fillable = ['name', 'logo'];

    // Un type a plusieurs cartes
    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
