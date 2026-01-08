<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Trash2, X } from 'lucide-vue-next';

interface Card {
    id: number;
    name: string;
    localId: string;
    image: string;
    set_name: string | null;
    rarity: string | null;
}

interface Set {
    id: number;
    name: string;
    abbreviation: string;
    serie: {
        id: number;
        name: string;
    };
}

interface Requirement {
    type: string;
    set_id: number | null;
    target_count: number;
    cards: Array<{
        card_id: number;
        required_qty: number;
        card?: Card;
    }>;
}

interface ChallengeData {
    id?: number;
    title: string;
    description: string;
    start_date: string | null;
    end_date: string | null;
    reward: number;
    requirements: Requirement[];
}

interface Props {
    challenge?: ChallengeData;
    sets: Set[];
    cards: Card[];
    submitUrl: string;
    submitMethod: 'post' | 'put';
    submitLabel: string;
}

const props = defineProps<Props>();

// Initialiser le formulaire
const form = useForm<ChallengeData>({
    title: props.challenge?.title || '',
    description: props.challenge?.description || '',
    start_date: props.challenge?.start_date || null,
    end_date: props.challenge?.end_date || null,
    reward: props.challenge?.reward || 0,
    requirements: props.challenge?.requirements || [],
});

// Ajouter un nouveau requirement
const addRequirement = () => {
    form.requirements.push({
        type: 'OPEN_PACKS',
        set_id: null,
        target_count: 1,
        cards: [],
    });
};

// S'assurer que target_count est calculé pour tous les CARD_LIST
const ensureTargetCount = () => {
    form.requirements.forEach((req, index) => {
        if (req.type === 'CARD_LIST') {
            calculateCardListTargetCount(index);
        }
    });
};

// Soumettre le formulaire avec vérification
const submitWithValidation = () => {
    // S'assurer que target_count est défini pour tous les requirements
    ensureTargetCount();
    submit();
};

// Supprimer un requirement
const removeRequirement = (index: number) => {
    form.requirements.splice(index, 1);
};

// Ajouter une carte à un requirement CARD_LIST
const addCardToRequirement = (reqIndex: number) => {
    form.requirements[reqIndex].cards.push({
        card_id: 0,
        required_qty: 1,
    });
    // Recalculer le target_count
    calculateCardListTargetCount(reqIndex);
};

// Vérifier si une carte est déjà sélectionnée dans un requirement
const isCardAlreadySelected = (
    reqIndex: number,
    cardId: number,
    currentCardIndex: number,
) => {
    return form.requirements[reqIndex].cards.some(
        (card, index) => index !== currentCardIndex && card.card_id === cardId,
    );
};

// Supprimer une carte d'un requirement
const removeCardFromRequirement = (reqIndex: number, cardIndex: number) => {
    form.requirements[reqIndex].cards.splice(cardIndex, 1);
    // Recalculer le target_count
    calculateCardListTargetCount(reqIndex);
};

// Calculer le target_count pour un requirement CARD_LIST
const calculateCardListTargetCount = (reqIndex: number) => {
    const req = form.requirements[reqIndex];
    if (req.type === 'CARD_LIST') {
        if (req.cards && req.cards.length > 0) {
            // Somme de toutes les quantités requises
            const total = req.cards.reduce(
                (sum, card) => sum + (card.required_qty || 0),
                0,
            );
            form.requirements[reqIndex].target_count = total || 1;
        } else {
            // Pas de cartes = 1 par défaut
            form.requirements[reqIndex].target_count = 1;
        }
    }
};

// Changer le type d'un requirement
const onRequirementTypeChange = (reqIndex: number, newType: string) => {
    form.requirements[reqIndex].type = newType;

    // Réinitialiser les cartes si on change de type
    if (newType !== 'CARD_LIST') {
        form.requirements[reqIndex].cards = [];
    } else {
        // Pour CARD_LIST, calculer le target_count
        calculateCardListTargetCount(reqIndex);
    }
};

// Soumettre le formulaire
const submit = () => {
    if (props.submitMethod === 'post') {
        form.post(props.submitUrl);
    } else {
        form.put(props.submitUrl);
    }
};

// Trouver une carte par ID
const findCard = (cardId: number) => {
    return props.cards.find((c) => c.id === cardId);
};

// Au montage du composant, recalculer les target_count pour les CARD_LIST existants
onMounted(() => {
    form.requirements.forEach((req, index) => {
        if (req.type === 'CARD_LIST' && req.cards && req.cards.length > 0) {
            calculateCardListTargetCount(index);
        }
    });
});
</script>

<template>
    <form @submit.prevent="submitWithValidation" class="space-y-6">
        <!-- Informations du challenge -->
        <Card>
            <CardHeader>
                <CardTitle>Informations du challenge</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <!-- Titre -->
                <div>
                    <Label for="title">Titre *</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        type="text"
                        required
                        :class="{ 'border-red-500': form.errors.title }"
                    />
                    <p
                        v-if="form.errors.title"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.title }}
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <Label for="description">Description *</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        required
                        :class="{ 'border-red-500': form.errors.description }"
                    />
                    <p
                        v-if="form.errors.description"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="start_date">Date de début</Label>
                        <Input
                            id="start_date"
                            v-model="form.start_date"
                            type="date"
                            :class="{
                                'border-red-500': form.errors.start_date,
                            }"
                        />
                        <p
                            v-if="form.errors.start_date"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.start_date }}
                        </p>
                    </div>
                    <div>
                        <Label for="end_date">Date de fin</Label>
                        <Input
                            id="end_date"
                            v-model="form.end_date"
                            type="date"
                            :class="{ 'border-red-500': form.errors.end_date }"
                        />
                        <p
                            v-if="form.errors.end_date"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.end_date }}
                        </p>
                    </div>
                </div>

                <!-- Récompense -->
                <div>
                    <Label for="reward">Récompense (coins) *</Label>
                    <Input
                        id="reward"
                        v-model.number="form.reward"
                        type="number"
                        min="0"
                        required
                        :class="{ 'border-red-500': form.errors.reward }"
                    />
                    <p
                        v-if="form.errors.reward"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.reward }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Requirements -->
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle
                    >Conditions ({{ form.requirements.length }})</CardTitle
                >
                <Button type="button" @click="addRequirement" size="sm">
                    <Plus class="mr-2 h-4 w-4" />
                    Ajouter une condition
                </Button>
            </CardHeader>
            <CardContent class="space-y-4">
                <p
                    v-if="form.errors.requirements"
                    class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-500 dark:bg-red-950"
                >
                    {{ form.errors.requirements }}
                </p>
                <p
                    v-if="form.requirements.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    Aucun condition. Ajoutez-en au moins un.
                </p>

                <Card
                    v-for="(req, reqIndex) in form.requirements"
                    :key="reqIndex"
                    class="border-2"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base"
                                >Condition {{ reqIndex + 1 }}</CardTitle
                            >
                            <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                                @click="removeRequirement(reqIndex)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Type de requirement -->
                        <div>
                            <Label>Type de condition *</Label>
                            <select
                                :value="req.type"
                                @change="
                                    onRequirementTypeChange(
                                        reqIndex,
                                        ($event.target as HTMLSelectElement)
                                            .value,
                                    )
                                "
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="CARD_LIST">
                                    Liste de cartes spécifiques
                                </option>
                                <option value="OPEN_PACKS">
                                    Ouvrir des boosters
                                </option>
                                <option value="OWN_CARDS">
                                    Posséder des cartes
                                </option>
                            </select>
                        </div>

                        <!-- Set (pour OPEN_PACKS et OWN_CARDS) -->
                        <div
                            v-if="
                                req.type === 'OPEN_PACKS' ||
                                req.type === 'OWN_CARDS'
                            "
                        >
                            <Label>Set *</Label>
                            <select
                                v-model="req.set_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option :value="null">
                                    Sélectionner un set
                                </option>
                                <option
                                    v-for="set in sets"
                                    :key="set.id"
                                    :value="set.id"
                                >
                                    {{ set.name }} ({{ set.abbreviation }})
                                </option>
                            </select>
                        </div>

                        <!-- Target count (pas utilisé pour CARD_LIST) -->
                        <div v-if="req.type !== 'CARD_LIST'">
                            <Label>
                                {{
                                    req.type === 'OPEN_PACKS'
                                        ? 'Nombre de boosters à ouvrir'
                                        : 'Nombre de cartes à posséder'
                                }}
                                *
                            </Label>
                            <Input
                                v-model.number="req.target_count"
                                type="number"
                                min="1"
                                required
                            />
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{
                                    req.type === 'OPEN_PACKS'
                                        ? 'Nombre de boosters que le joueur doit ouvrir de ce set'
                                        : 'Nombre de cartes que le joueur doit posséder de ce set'
                                }}
                            </p>
                        </div>

                        <!-- Cartes (pour CARD_LIST) -->
                        <div v-if="req.type === 'CARD_LIST'" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <Label>Cartes requises *</Label>
                                        <span
                                            v-if="req.cards.length > 0"
                                            class="rounded bg-primary/10 px-2 py-1 text-xs font-medium text-primary"
                                        >
                                            Total :
                                            {{ req.target_count || 0 }} carte(s)
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        Le total est calculé automatiquement en
                                        fonction des quantités
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="addCardToRequirement(reqIndex)"
                                >
                                    <Plus class="mr-2 h-3 w-3" />
                                    Ajouter une carte
                                </Button>
                            </div>

                            <div
                                v-if="req.cards.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                Aucune carte sélectionnée
                            </div>

                            <div
                                v-for="(card, cardIndex) in req.cards"
                                :key="cardIndex"
                                class="flex items-end gap-2 rounded border p-2"
                                :class="{
                                    'border-red-500':
                                        card.card_id > 0 &&
                                        isCardAlreadySelected(
                                            reqIndex,
                                            card.card_id,
                                            cardIndex,
                                        ),
                                }"
                            >
                                <div class="flex-1">
                                    <Label class="text-xs">Carte</Label>
                                    <select
                                        v-model="card.card_id"
                                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option :value="0">
                                            Sélectionner une carte
                                        </option>
                                        <option
                                            v-for="c in cards"
                                            :key="c.id"
                                            :value="c.id"
                                            :disabled="
                                                isCardAlreadySelected(
                                                    reqIndex,
                                                    c.id,
                                                    cardIndex,
                                                )
                                            "
                                        >
                                            {{ c.name }} - {{ c.set_name }}
                                            {{
                                                isCardAlreadySelected(
                                                    reqIndex,
                                                    c.id,
                                                    cardIndex,
                                                )
                                                    ? '(déjà sélectionnée)'
                                                    : ''
                                            }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="
                                            card.card_id > 0 &&
                                            isCardAlreadySelected(
                                                reqIndex,
                                                card.card_id,
                                                cardIndex,
                                            )
                                        "
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        ⚠️ Cette carte est déjà sélectionnée
                                        dans ce requirement
                                    </p>
                                </div>
                                <div class="w-24">
                                    <Label class="text-xs">Quantité</Label>
                                    <Input
                                        v-model.number="card.required_qty"
                                        type="number"
                                        min="1"
                                        class="h-9"
                                        @input="
                                            calculateCardListTargetCount(
                                                reqIndex,
                                            )
                                        "
                                    />
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="
                                        removeCardFromRequirement(
                                            reqIndex,
                                            cardIndex,
                                        )
                                    "
                                    class="h-9"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </CardContent>
        </Card>

        <!-- Boutons de soumission -->
        <div class="flex justify-end gap-2">
            <Button
                type="button"
                variant="outline"
                @click="$inertia.visit('/admin/challenges')"
            >
                Annuler
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'En cours...' : submitLabel }}
            </Button>
        </div>
    </form>
</template>
