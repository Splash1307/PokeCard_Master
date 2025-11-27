<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    // Les champs qu'on peut remplir directement
    protected $fillable = [
        'creator_id',
        'offered_card_id',
        'requested_card_id',
        'responder_id',
        'status',
        'completed_at',
    ];

    // Convertir automatiquement completed_at en date
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // L'utilisateur qui crée l'offre d'échange
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // L'utilisateur qui accepte l'échange
    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    // La carte offerte par le créateur
    public function offeredCard()
    {
        return $this->belongsTo(Card::class, 'offered_card_id');
    }

    // La carte demandée par le créateur
    public function requestedCard()
    {
        return $this->belongsTo(Card::class, 'requested_card_id');
    }

    // Vérifier si l'échange est en attente
    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Vérifier si l'échange est complété
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    // Vérifier si l'échange est annulé
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
