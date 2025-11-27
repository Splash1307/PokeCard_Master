<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    // Pas de timestamps pour cette table
    public $timestamps = false;

    // Les champs qu'on peut remplir directement
    protected $fillable = [
        'name',
        'localId',
        'logo',
        'set_id',
        'rarity_id',
        'primaryType',
        'secondaryType',
    ];

    // Ajouter l'accesseur 'image' à la sérialisation JSON
    protected $appends = ['image'];

    // Accesseur pour 'image' qui retourne 'logo'
    // Permet la compatibilité avec le code frontend
    public function getImageAttribute()
    {
        return $this->logo;
    }

    // Une carte peut être dans plusieurs collections
    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    // Une carte appartient à une rareté
    public function rarity()
    {
        return $this->belongsTo(Rarity::class);
    }

    // Le type principal de la carte
    public function type()
    {
        return $this->belongsTo(Type::class, 'primaryType');
    }

    // Le type principal (alias)
    public function primaryType()
    {
        return $this->belongsTo(Type::class, 'primaryType');
    }

    // Le type secondaire
    public function secondaryType()
    {
        return $this->belongsTo(Type::class, 'secondaryType');
    }

    // Une carte appartient à un set
    public function set()
    {
        return $this->belongsTo(Set::class);
    }
}
