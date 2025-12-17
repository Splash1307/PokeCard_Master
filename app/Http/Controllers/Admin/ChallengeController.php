<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeRequirement;
use App\Models\RequirementCard;
use App\Models\Set;
use App\Models\Card;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChallengeController extends Controller
{
    /**
     * Afficher la liste de tous les challenges
     */
    public function index()
    {
        // Récupérer tous les challenges avec leurs relations
        $challenges = Challenge::with([
            'requirements.set',
            'requirements.requirementCards',
        ])
            ->latest('id')
            ->get()
            ->map(function ($challenge) {
                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'description' => $challenge->description,
                    'start_date' => $challenge->start_date?->format('Y-m-d'),
                    'end_date' => $challenge->end_date?->format('Y-m-d'),
                    'status' => $challenge->status,
                    'reward' => $challenge->reward,
                    'requirements' => $challenge->requirements->map(function ($req) {
                        return [
                            'id' => $req->id,
                            'type' => $req->type,
                            'set_name' => $req->set->name ?? null,
                            'target_count' => $req->target_count,
                            'cards_count' => $req->requirementCards->count(),
                        ];
                    })->toArray(),
                ];
            });

        return Inertia::render('Admin/Challenges/Index', [
            'challenges' => $challenges
        ]);
    }

    /**
     * Basculer le statut d'un challenge (Actif <-> Inactif)
     */
    public function toggleStatus(Challenge $challenge)
    {
        $newStatus = $challenge->status === 'Actif' ? 'Inactif' : 'Actif';

        $challenge->update([
            'status' => $newStatus
        ]);

        return back()->with('success', "Le challenge a été {$newStatus}.");
    }

    /**
     * Afficher le formulaire de création d'un challenge
     */
    public function create()
    {
        // Récupérer tous les sets pour les requirements
        $sets = Set::with('serie')->orderBy('name')->get();

        // Récupérer toutes les cartes pour le type CARD_LIST
        $cards = Card::with(['set', 'rarity', 'primaryType'])
            ->orderBy('name')
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'localId' => $card->localId,
                    'image' => $card->image,
                    'set_name' => $card->set->name ?? null,
                    'rarity' => $card->rarity->name ?? null,
                ];
            });

        return Inertia::render('Admin/Challenges/Create', [
            'sets' => $sets,
            'cards' => $cards,
        ]);
    }

    /**
     * Enregistrer un nouveau challenge
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:Actif,Inactif,Archivé',
            'reward' => 'required|integer|min:0',
            'requirements' => 'required|array|min:1',
            'requirements.*.type' => 'required|in:CARD_LIST,OPEN_PACKS,OWN_CARDS',
            'requirements.*.set_id' => 'nullable|exists:sets,id',
            'requirements.*.target_count' => 'required|integer|min:1',
            'requirements.*.cards' => 'nullable|array',
            'requirements.*.cards.*.card_id' => 'required_if:requirements.*.type,CARD_LIST|exists:cards,id',
            'requirements.*.cards.*.required_qty' => 'required_if:requirements.*.type,CARD_LIST|integer|min:1',
        ]);

        // Créer le challenge
        $challenge = Challenge::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'reward' => $validated['reward'],
        ]);

        // Créer les requirements
        foreach ($validated['requirements'] as $reqData) {
            $requirement = ChallengeRequirement::create([
                'challenge_id' => $challenge->id,
                'type' => $reqData['type'],
                'set_id' => $reqData['set_id'] ?? null,
                'target_count' => $reqData['target_count'],
            ]);

            // Si type CARD_LIST, créer les cartes associées
            if ($reqData['type'] === 'CARD_LIST' && !empty($reqData['cards'])) {
                // Vérifier les doublons
                $cardIds = array_column($reqData['cards'], 'card_id');
                if (count($cardIds) !== count(array_unique($cardIds))) {
                    return back()->withErrors([
                        'requirements' => 'Vous ne pouvez pas ajouter la même carte plusieurs fois dans un requirement. Augmentez plutôt la quantité.'
                    ])->withInput();
                }

                foreach ($reqData['cards'] as $cardData) {
                    RequirementCard::create([
                        'requirement_id' => $requirement->id,
                        'card_id' => $cardData['card_id'],
                        'required_qty' => $cardData['required_qty'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Challenge créé avec succès !');
    }

    /**
     * Afficher le formulaire d'édition d'un challenge
     */
    public function edit(Challenge $challenge)
    {
        // Charger le challenge avec ses relations
        $challenge->load(['requirements.requirementCards.card', 'requirements.set']);

        // Récupérer tous les sets pour les requirements
        $sets = Set::with('serie')->orderBy('name')->get();

        // Récupérer toutes les cartes pour le type CARD_LIST
        $cards = Card::with(['set', 'rarity', 'primaryType'])
            ->orderBy('name')
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'localId' => $card->localId,
                    'image' => $card->image,
                    'set_name' => $card->set->name ?? null,
                    'rarity' => $card->rarity->name ?? null,
                ];
            });

        // Formatter le challenge pour le frontend
        $formattedChallenge = [
            'id' => $challenge->id,
            'title' => $challenge->title,
            'description' => $challenge->description,
            'start_date' => $challenge->start_date?->format('Y-m-d'),
            'end_date' => $challenge->end_date?->format('Y-m-d'),
            'status' => $challenge->status,
            'reward' => $challenge->reward,
            'requirements' => $challenge->requirements->map(function ($req) {
                return [
                    'id' => $req->id,
                    'type' => $req->type,
                    'set_id' => $req->set_id,
                    'target_count' => $req->target_count,
                    'cards' => $req->requirementCards->map(function ($reqCard) {
                        return [
                            'card_id' => $reqCard->card_id,
                            'required_qty' => $reqCard->required_qty,
                            'card' => [
                                'id' => $reqCard->card->id,
                                'name' => $reqCard->card->name,
                                'image' => $reqCard->card->image,
                            ],
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];

        return Inertia::render('Admin/Challenges/Edit', [
            'challenge' => $formattedChallenge,
            'sets' => $sets,
            'cards' => $cards,
        ]);
    }

    /**
     * Mettre à jour un challenge
     */
    public function update(Request $request, Challenge $challenge)
    {
        // Validation
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:Actif,Inactif,Archivé',
            'reward' => 'required|integer|min:0',
            'requirements' => 'required|array|min:1',
            'requirements.*.type' => 'required|in:CARD_LIST,OPEN_PACKS,OWN_CARDS',
            'requirements.*.set_id' => 'nullable|exists:sets,id',
            'requirements.*.target_count' => 'required|integer|min:1',
            'requirements.*.cards' => 'nullable|array',
            'requirements.*.cards.*.card_id' => 'required_if:requirements.*.type,CARD_LIST|exists:cards,id',
            'requirements.*.cards.*.required_qty' => 'required_if:requirements.*.type,CARD_LIST|integer|min:1',
        ]);

        // Mettre à jour le challenge
        $challenge->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'reward' => $validated['reward'],
        ]);

        // Supprimer les anciens requirements
        $challenge->requirements()->delete();

        // Créer les nouveaux requirements
        foreach ($validated['requirements'] as $reqData) {
            $requirement = ChallengeRequirement::create([
                'challenge_id' => $challenge->id,
                'type' => $reqData['type'],
                'set_id' => $reqData['set_id'] ?? null,
                'target_count' => $reqData['target_count'],
            ]);

            // Si type CARD_LIST, créer les cartes associées
            if ($reqData['type'] === 'CARD_LIST' && !empty($reqData['cards'])) {
                // Vérifier les doublons
                $cardIds = array_column($reqData['cards'], 'card_id');
                if (count($cardIds) !== count(array_unique($cardIds))) {
                    return back()->withErrors([
                        'requirements' => 'Vous ne pouvez pas ajouter la même carte plusieurs fois dans un requirement. Augmentez plutôt la quantité.'
                    ])->withInput();
                }

                foreach ($reqData['cards'] as $cardData) {
                    RequirementCard::create([
                        'requirement_id' => $requirement->id,
                        'card_id' => $cardData['card_id'],
                        'required_qty' => $cardData['required_qty'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Challenge mis à jour avec succès !');
    }

    /**
     * Supprimer un challenge
     */
    public function destroy(Challenge $challenge)
    {
        $challenge->delete();

        return back()->with('success', 'Le challenge a été supprimé avec succès.');
    }
}
