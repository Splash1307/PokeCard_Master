<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    /**
     * Indique si le modèle doit gérer les timestamps
     */
    public $timestamps = false;

    /**
     * Les attributs qui sont mass assignable
     */
    protected $fillable = [
        'level',
        'nbCoins',
        'nbBooster',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'level' => 'integer',
        'nbCoins' => 'integer',
        'nbBooster' => 'integer',
    ];

    /**
     * Récupère les récompenses pour un niveau spécifique
     */
    public static function getRewards(int $level): ?self
    {
        return self::where('level', $level)->first();
    }
}
