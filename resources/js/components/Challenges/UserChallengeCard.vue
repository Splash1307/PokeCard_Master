<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { Trophy, CheckCircle, Clock } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Requirement {
    id: number;
    type: string;
    set_id: number | null;
    set_name: string | null;
    target_count: number;
    progress_count: number;
    completed: boolean;
    cards: Array<{
        card_id: number;
        card_name: string;
        card_image: string;
        card_set_name: string | null;
        required_qty: number;
        owned_qty: number;
        donated_qty: number;
    }>;
}

interface Challenge {
    id: number;
    title: string;
    description: string;
    start_date: string | null;
    end_date: string | null;
    reward: number;
    status: string;
    completed_at: string | null;
    claimed_at: string | null;
    can_claim: boolean;
    requirements: Requirement[];
}

const props = defineProps<{ challenge: Challenge }>();

const isClaiming = ref(false);
const popupCard = ref<null | {
    name: string;
    image: string;
}> (null);
const showCardList = ref<Requirement | null>(null);

const showImage = (card: any) => {
    popupCard.value = {
        name: card.card_name,
        image: card.card_image,
    };
};

const closeImage = () => popupCard.value = null;

const openCardList = (requirement: Requirement) => {
    showCardList.value = requirement;
};

const closeCardList = () => {
    showCardList.value = null;
};

const statusVariant = computed(() => {
    if (props.challenge.status === 'Récompense récupérée') return 'outline';
    if (props.challenge.status === 'Complété') return 'default';
    return 'secondary';
});

const totalProgress = computed(() => {
    const total = props.challenge.requirements.reduce((acc, req) => acc + req.target_count, 0);
    const current = props.challenge.requirements.reduce((acc, req) => acc + Math.min(req.progress_count, req.target_count), 0);
    return total > 0 ? Math.round((current / total) * 100) : 0;
});

const formatDate = (date: string | null) => {
    if (!date) return 'Non définie';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

const getRequirementTypeLabel = (type: string) => {
    switch (type) {
        case 'CARD_LIST':
            return 'Collecter des cartes';
        case 'OPEN_PACKS':
            return 'Ouvrir des boosters';
        case 'OWN_CARDS':
            return 'Posséder des cartes';
        default:
            return type;
    }
};

const claimReward = () => {
    if (!props.challenge.can_claim || isClaiming.value) return;

    isClaiming.value = true;
    router.post(
        `/challenges/${props.challenge.id}/claim`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isClaiming.value = false;
            },
        }
    );
};

const donatingCard = ref<number | null>(null);

const donateCard = (cardId: number) => {
    if (donatingCard.value) return;

    donatingCard.value = cardId;
    router.post(
        `/challenges/${props.challenge.id}/donate`,
        {
            card_id: cardId,
            qty: 1,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                donatingCard.value = null;
            },
        }
    );
};

const canDonateCard = (card: any) => {
    return card.owned_qty > 0 && card.donated_qty < card.required_qty;
};

const getCardState = (card: any) => {
    if (card.donated_qty >= card.required_qty) return 'completed';
    if (card.owned_qty > 0) return 'owned';
    return 'not_owned';
};
</script>

<template>
    <Card class="flex flex-col">

        <CardHeader>
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1">
                    <CardTitle class="text-xl">{{ challenge.title }}</CardTitle>
                    <CardDescription class="mt-1">
                        {{ challenge.description }}
                    </CardDescription>
                </div>
                <Badge :variant="statusVariant">
                    {{ challenge.status }}
                </Badge>
            </div>
        </CardHeader>

        <CardContent class="flex-1 space-y-6">

            <!-- Requirements -->
            <div class="space-y-4">

                <div
                    v-for="req in challenge.requirements"
                    :key="req.id"
                    class="border-t pt-3 space-y-2"
                >

                    <!-- Titre + progression -->
                    <div class="flex justify-between text-sm font-medium">
                        <span>{{ getRequirementTypeLabel(req.type) }}</span>
                        <span class="text-muted-foreground">
                            {{ req.progress_count }} / {{ req.target_count }}
                        </span>
                    </div>

                    <!-- TYPE CARD_LIST -->
                    <template v-if="req.type === 'CARD_LIST' && req.cards.length">
                        <div class="pl-6">
                            <Button
                                @click="openCardList(req)"
                                variant="outline"
                                size="sm"
                                class="w-full"
                            >
                                Voir les {{ req.target_count }} carte(s) requise(s)
                            </Button>
                        </div>
                    </template>

                    <!-- TYPE OPEN_PACKS : affichage extension -->
                    <template v-if="req.type === 'OPEN_PACKS'">
                        <div class="pl-6 text-xs text-muted-foreground">
                            Booster requis :
                            <span class="font-semibold text-primary">
                                {{ req.set_name ?? 'Extension libre' }}
                            </span>
                        </div>
                    </template>

                    <!-- TYPE OWN_CARDS : affichage du set -->
                    <template v-if="req.type === 'OWN_CARDS'">
                        <div class="pl-6 text-xs text-muted-foreground">
                            Cartes à posséder :
                            <span class="font-semibold text-primary">
                                {{ req.set_name ?? 'N’importe quelle carte' }}
                            </span>
                        </div>
                    </template>

                    <!-- Barre progression -->
                    <div class="w-full bg-muted rounded-full h-1.5">
                        <div
                            class="h-1.5 rounded-full transition-all"
                            :class="req.completed ? 'bg-green-500':'bg-blue-500'"
                            :style="{ width: Math.min((req.progress_count / req.target_count) * 100, 100) + '%' }"
                        ></div>
                    </div>

                </div>
            </div>

        </CardContent>

        <CardFooter class="flex flex-col gap-3 border-t pt-4">
            <!-- Dates -->
            <div class="flex justify-between text-xs text-muted-foreground w-full">
                <div class="flex items-center gap-1">
                    <Clock class="w-3 h-3" />
                    <span>Début: {{ formatDate(challenge.start_date) }}</span>
                </div>
                <span>Fin: {{ formatDate(challenge.end_date) }}</span>
            </div>

            <!-- Récompense et bouton claim -->
            <div class="flex items-center justify-between w-full gap-3">
                <div class="flex items-center gap-2">
                    <Trophy class="w-4 h-4 text-yellow-500" />
                    <span class="font-semibold text-yellow-600">{{ challenge.reward }} pièces</span>
                </div>

                <Button
                    v-if="challenge.can_claim"
                    @click="claimReward"
                    :disabled="isClaiming"
                    variant="default"
                    size="sm"
                >
                    {{ isClaiming ? 'Récupération...' : 'Récupérer la récompense' }}
                </Button>
            </div>
        </CardFooter>

    </Card>

    <!-- POPUP IMAGE -->
    <div
        v-if="popupCard"
        class="fixed inset-0 bg-black/80 flex justify-center items-center z-[60]"
        @click="closeImage"
    >
        <img
            :src="popupCard.image"
            class="max-h-[90vh] max-w-[70vw] rounded shadow-2xl"
        />
    </div>

    <!-- POPUP LISTE DES CARTES -->
    <div
        v-if="showCardList"
        class="fixed inset-0 bg-black/80 flex justify-center items-center z-50 p-4"
        @click.self="closeCardList"
    >
        <div class="bg-background rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b">
                <div>
                    <h3 class="text-lg font-semibold">{{ getRequirementTypeLabel(showCardList.type) }}</h3>
                    <p class="text-sm text-muted-foreground mt-1">
                        Progression : {{ showCardList.progress_count }} / {{ showCardList.target_count }}
                    </p>
                </div>
                <Button
                    @click="closeCardList"
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 p-0"
                >
                    ✕
                </Button>
            </div>

            <!-- Content avec scroll -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="grid grid-cols-1 gap-3">
                    <template v-for="card in showCardList.cards" :key="card.card_id">
                        <div
                            class="flex items-center gap-3 p-3 border rounded transition-all"
                            :class="{
                                'border-green-500/50 bg-green-500/5': getCardState(card) === 'completed',
                                'border-blue-500/30 bg-blue-500/5': getCardState(card) === 'owned',
                                'border-border opacity-60': getCardState(card) === 'not_owned',
                            }"
                        >
                            <img
                                :src="card.card_image"
                                class="h-16 w-12 object-cover rounded cursor-pointer hover:scale-105 transition-transform"
                                @click="showImage(card)"
                            />

                            <div class="flex-1 flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-medium">{{ card.card_name }}</span>
                                        <span v-if="card.card_set_name" class="text-xs text-muted-foreground">{{ card.card_set_name }}</span>
                                    </div>
                                    <Badge
                                        v-if="getCardState(card) === 'completed'"
                                        variant="outline"
                                        class="border-green-500 text-green-600"
                                    >
                                        <CheckCircle class="w-3 h-3 mr-1" />
                                        Validée
                                    </Badge>
                                    <Badge
                                        v-else-if="getCardState(card) === 'not_owned'"
                                        variant="outline"
                                    >
                                        Non possédée
                                    </Badge>
                                </div>

                                <div v-if="getCardState(card) !== 'completed'" class="flex items-center gap-3 text-xs text-muted-foreground">
                                    <span>Requis: {{ card.required_qty }}</span>
                                    <span>Possédée: {{ card.owned_qty }}</span>
                                </div>

                                <Button
                                    v-if="canDonateCard(card)"
                                    @click="donateCard(card.card_id)"
                                    :disabled="donatingCard === card.card_id"
                                    size="sm"
                                    class="mt-1 w-full"
                                >
                                    {{ donatingCard === card.card_id ? 'Don en cours...' : 'Donner cette carte' }}
                                </Button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t flex justify-end">
                <Button @click="closeCardList" variant="outline">
                    Fermer
                </Button>
            </div>
        </div>
    </div>

</template>
