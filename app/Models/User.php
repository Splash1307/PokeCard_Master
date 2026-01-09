<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Désactiver les timestamps automatiques car la table n'a pas created_at/updated_at
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pseudo',
        'email',
        'password',
        'coin',
        'lastConnexionAt',
        'role_id',
        'level_id',
        'xp',
        'nbBooster'
    ];

    /**
     * Accesseur pour 'name' qui retourne 'pseudo'
     * Permet la compatibilité avec le code existant
     */
    public function getNameAttribute()
    {
        return $this->pseudo;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lastConnexionAt' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // Les cartes que possède l'utilisateur (via sa collection)
    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    // Les échanges créés par l'utilisateur
    public function createdTrades()
    {
        return $this->hasMany(Trade::class, 'creator_id');
    }

    // Les échanges acceptés par l'utilisateur
    public function respondedTrades()
    {
        return $this->hasMany(Trade::class, 'responder_id');
    }

    // Les dons faits par l'utilisateur pour des défis
    public function challengeDonations()
    {
        return $this->hasMany(ChallengeDonation::class);
    }

    // La progression de l'utilisateur sur chaque requirement (demande du défi)
    public function requirementProgresses()
    {
        return $this->hasMany(UserRequirementProgress::class);
    }

    // Les défis auxquels l'utilisateur participe et leur statut
    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function addXp(int $amount): void
    {
        $this->xp += $amount;

        // Gestion de la montée de niveau
        while ($this->xp >= 100) {
            $this->xp -= 100;
            $this->level_id += 1;

            // Récupérer et appliquer les récompenses
            $levelRewards = Level::where('level', $this->level_id)->first();

            if ($levelRewards) {
                $this->coin += $levelRewards->nbCoins;
                $this->nbBooster += $levelRewards->nbBooster;
            }
        }

        $this->save();
    }


}
