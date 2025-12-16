<script setup lang="ts">
import BoosterRevealAnimation from '@/components/Booster/BoosterRevealAnimation.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Card = {
    id: number;
    name: string;
    localId?: number;
    image: string;
    isNew?: boolean; // Ajout du champ isNew
    hp?: number;
    attack?: number;
    defense?: number;
    rarity?: {
        id: number;
        name: string;
    };
    primaryType?: {
        id: number;
        name: string;
    };
    secondaryType?: {
        id: number;
        name: string;
    };
    set: {
        id: number;
        name: string;
        abbreviation: string;
    };
};

type Set = {
    id: number;
    name: string;
    abbreviation: string;
};

const props = defineProps<{
    cards: Card[];
    set: Set;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Boosters',
        href: '/boosters',
    },
    {
        title: 'Ouverture',
        href: '#',
    },
];

const showAnimation = ref(true);

const newCardsCount = computed(() => {
    return props.cards.filter((card) => card.isNew).length;
});

const handleAnimationComplete = () => {
    showAnimation.value = false;
};

const goToCollection = () => {
    router.visit('/collection');
};

const goToBoosters = () => {
    router.visit('/boosters');
};
</script>

<template>
    <Head title="Ouverture de booster" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Animation de révélation des cartes -->
            <BoosterRevealAnimation
                v-if="showAnimation"
                :cards="cards"
                :set="set"
                @complete="handleAnimationComplete"
            />

            <!-- Actions après l'animation -->
            <div v-else class="space-y-6">
                <div class="text-center">
                    <div
                        v-if="newCardsCount > 0"
                        class="mt-2 flex items-center justify-center gap-2"
                    >
                        <Badge
                            class="bg-gradient-to-r from-green-500 to-emerald-600 text-white"
                        >
                            {{ newCardsCount }} nouvelle{{
                                newCardsCount > 1 ? 's' : ''
                            }}
                            carte{{ newCardsCount > 1 ? 's' : '' }} !
                        </Badge>
                    </div>
                </div>

                <!-- Aperçu de toutes les cartes - 2 lignes de 5 -->
                <div class="mx-auto max-w-5xl space-y-4">
                    <!-- Première ligne -->
                    <div class="grid grid-cols-5 gap-3">
                        <div
                            v-for="(card, index) in cards.slice(0, 5)"
                            :key="card.id + '-final-' + index"
                            class="relative aspect-[3/4] overflow-hidden rounded-lg border-2 border-primary/20 shadow-lg transition-all hover:scale-105 hover:border-primary"
                        >
                            <img
                                :src="card.image"
                                :alt="card.name"
                                class="h-full w-full object-cover"
                            />
                            <!-- Badge NEW -->
                            <div
                                v-if="card.isNew"
                                class="absolute top-2 left-2 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 px-2 py-1 text-xs font-bold text-white shadow-lg"
                            >
                                NEW
                            </div>
                        </div>
                    </div>

                    <!-- Deuxième ligne -->
                    <div class="grid grid-cols-5 gap-3">
                        <div
                            v-for="(card, index) in cards.slice(5, 10)"
                            :key="card.id + '-final-' + (index + 5)"
                            class="relative aspect-[3/4] overflow-hidden rounded-lg border-2 border-primary/20 shadow-lg transition-all hover:scale-105 hover:border-primary"
                        >
                            <img
                                :src="card.image"
                                :alt="card.name"
                                class="h-full w-full object-cover"
                            />
                            <!-- Badge NEW -->
                            <div
                                v-if="card.isNew"
                                class="absolute top-2 left-2 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 px-2 py-1 text-xs font-bold text-white shadow-lg"
                            >
                                NEW
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-center gap-4">
                    <Button @click="goToBoosters" variant="outline" size="lg">
                        Ouvrir un autre booster
                    </Button>
                    <Button @click="goToCollection" size="lg">
                        Voir ma collection
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
