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
        'image',
        'set_id',
        'rarity_id',
        'primaryType',
        'secondaryType',
    ];

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

    // Le type principal
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

    // Les échanges où la carte est offerte
    public function offeredTrades()
    {
        return $this->hasMany(Trade::class, 'offered_card_id');
    }

    // Les échanges où la carte est demandée
    public function requestedTrades()
    {
        return $this->hasMany(Trade::class, 'requested_card_id');
    }

    // Les requirements qui ciblent cette carte (via requirement_cards, demande de défis)
    public function requirementCards()
    {
        return $this->hasMany(RequirementCard::class);
    }

    // Les dons de cette carte dans les défis
    public function challengeDonations()
    {
        return $this->hasMany(ChallengeDonation::class);
    }


}
