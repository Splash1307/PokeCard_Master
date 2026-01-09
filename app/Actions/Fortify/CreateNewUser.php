<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Http\Controllers\ChallengeController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'pseudo' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'pseudo' => $input['pseudo'],
            'email' => $input['email'],
            'password' => $input['password'],
            'coin' => 0,
            'role_id' => 2,
            'lastConnexionAt' => Carbon::now()->subDay(),
        ]);

        // Attribuer automatiquement tous les challenges actifs au nouvel utilisateur
        ChallengeController::assignActiveChallenges($user->id);

        return $user;
    }
}
