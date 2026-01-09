<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Level;

class UserBoosterObserver
{
    /**
     * Ajoute de l'XP à l'utilisateur et gère la montée de niveau
     */
    public function addExperience(User $user, int $xpAmount): void
    {
        $user->xp += $xpAmount;

        // Vérifier si l'utilisateur monte de niveau
        while ($user->xp >= 100) {
            $user->xp -= 100;
            $user->level_id += 1;

            // Récupérer les récompenses du nouveau niveau
            $levelRewards = Level::where('level', $user->level_id->level)->first();

            if ($levelRewards) {
                $user->coin += $levelRewards->nbCoins;
                $user->nbBooster += $levelRewards->nbBooster;

                // Optionnel : notification de level up
                // $user->notify(new LevelUpNotification($user->level, $levelRewards));
            }
        }

        $user->save();
    }
}
