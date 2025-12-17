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
        required_qty: number;
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

const showImage = (card: any) => {
    popupCard.value = {
        name: card.card_name,
        image: card.card_image,
    };
};

const closeImage = () => popupCard.value = null;

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
                        <div class="grid grid-cols-2 gap-2 pl-6">
                            <div
                                v-for="card in req.cards"
                                :key="card.card_id"
                                class="flex items-center gap-2 p-2 border rounded cursor-pointer hover:bg-accent"
                                @click="showImage(card)"
                            >
                                <img
                                    :src="card.card_image"
                                    class="h-10 w-7 object-cover rounded"
                                />

                                <div class="flex flex-col">
                                    <span class="text-xs font-medium">{{ card.card_name }}</span>
                                    <span class="text-[10px] text-muted-foreground">
                                        Quantité requise : {{ card.required_qty }}
                                    </span>
                                </div>
                            </div>
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

    </Card>

    <!-- POPUP IMAGE -->
    <div
        v-if="popupCard"
        class="fixed inset-0 bg-black/80 flex justify-center items-center z-50"
        @click="closeImage"
    >
        <img
            :src="popupCard.image"
            class="max-h-[90vh] max-w-[70vw] rounded shadow-2xl"
        />
    </div>

</template>
