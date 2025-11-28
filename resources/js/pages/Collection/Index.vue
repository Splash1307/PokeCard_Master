<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CardItem from '@/components/CardItem.vue';
import CreateTradeDialog from '@/components/CreateTradeDialog.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { ChevronDown, ChevronRight } from 'lucide-vue-next';

type Card = {
    id: number;
    name: string;
    image: string;
    owned: boolean;
    quantity: number;
    localId?: number;
    hp?: number;
    attack?: number;
    defense?: number;
    rarity?: {
        name: string;
    };
    type?: {
        name: string;
    };
    set?: {
        id: number;
        name: string;
        abbreviation: string;
        serie?: {
            id: number;
            name: string;
            abbreviation: string;
        };
    };
};

const props = defineProps<{
    allCards: Card[];
    availableCards: Card[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ma Collection',
        href: '/collection',
    },
];

const showTradeDialog = ref(false);
const selectedCard = ref<Card | null>(null);
const filter = ref<'all' | 'owned' | 'not_owned'>('all');

// État pour gérer les séries et sets dépliés
const expandedSeries = ref<Set<number>>(new Set());
const expandedSets = ref<Set<number>>(new Set());

// Organiser les cartes par série et par set
const organizedCards = computed(() => {
    const filteredCards = props.allCards.filter(card => {
        if (filter.value === 'owned') return card.owned;
        if (filter.value === 'not_owned') return !card.owned;
        return true;
    });

    const seriesMap = new Map();

    filteredCards.forEach(card => {
        if (!card.set?.serie) return;

        const serieId = card.set.serie.id;
        const setId = card.set.id;

        if (!seriesMap.has(serieId)) {
            seriesMap.set(serieId, {
                id: serieId,
                name: card.set.serie.name,
                abbreviation: card.set.serie.abbreviation,
                sets: new Map(),
                totalCards: 0,
                ownedCards: 0,
            });
        }

        const serie = seriesMap.get(serieId);

        if (!serie.sets.has(setId)) {
            serie.sets.set(setId, {
                id: setId,
                name: card.set.name,
                abbreviation: card.set.abbreviation,
                cards: [],
                totalCards: 0,
                ownedCards: 0,
            });
        }

        const set = serie.sets.get(setId);
        set.cards.push(card);
        set.totalCards++;
        serie.totalCards++;

        if (card.owned) {
            set.ownedCards++;
            serie.ownedCards++;
        }
    });

    return Array.from(seriesMap.values()).map(serie => ({
        ...serie,
        sets: Array.from(serie.sets.values()),
    }));
});

const ownedCount = computed(() => {
    return props.allCards.filter(card => card.owned).length;
});

const toggleSerie = (serieId: number) => {
    if (expandedSeries.value.has(serieId)) {
        expandedSeries.value.delete(serieId);
    } else {
        expandedSeries.value.add(serieId);
    }
};

const toggleSet = (setId: number) => {
    if (expandedSets.value.has(setId)) {
        expandedSets.value.delete(setId);
    } else {
        expandedSets.value.add(setId);
    }
};

const handleTrade = (cardId: number) => {
    const card = props.allCards.find(c => c.id === cardId);
    if (card && card.owned) {
        selectedCard.value = card;
        showTradeDialog.value = true;
    }
};

// Déplier tout
const expandAll = () => {
    organizedCards.value.forEach(serie => {
        expandedSeries.value.add(serie.id);
        serie.sets.forEach(set => {
            expandedSets.value.add(set.id);
        });
    });
};

// Replier tout
const collapseAll = () => {
    expandedSeries.value.clear();
    expandedSets.value.clear();
};
</script>

<template>
    <Head title="Ma Collection" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- En-tête de la page -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Ma Collection</h1>
                    <p class="text-muted-foreground mt-1">
                        Vous possédez {{ ownedCount }} carte{{ ownedCount > 1 ? 's' : '' }} sur {{ allCards.length }}
                    </p>
                </div>

                <Link href="/trades">
                    <Button variant="outline">
                        Voir les échanges
                    </Button>
                </Link>
            </div>

            <!-- Filtres et contrôles -->
            <div class="flex gap-2 flex-wrap items-center">
                <Button
                    @click="filter = 'all'"
                    :variant="filter === 'all' ? 'default' : 'outline'"
                    size="sm"
                >
                    Toutes ({{ allCards.length }})
                </Button>
                <Button
                    @click="filter = 'owned'"
                    :variant="filter === 'owned' ? 'default' : 'outline'"
                    size="sm"
                >
                    Possédées ({{ ownedCount }})
                </Button>
                <Button
                    @click="filter = 'not_owned'"
                    :variant="filter === 'not_owned' ? 'default' : 'outline'"
                    size="sm"
                >
                    Non possédées ({{ allCards.length - ownedCount }})
                </Button>

                <div class="ml-auto flex gap-2">
                    <Button @click="expandAll" variant="outline" size="sm">
                        Déplier tout
                    </Button>
                    <Button @click="collapseAll" variant="outline" size="sm">
                        Replier tout
                    </Button>
                </div>
            </div>

            <!-- Liste des séries -->
            <div v-if="organizedCards.length > 0" class="space-y-4">
                <div
                    v-for="serie in organizedCards"
                    :key="serie.id"
                    class="border rounded-lg overflow-hidden"
                >
                    <!-- En-tête de série -->
                    <button
                        @click="toggleSerie(serie.id)"
                        class="w-full px-4 py-3 flex items-center justify-between bg-muted/50 hover:bg-muted transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <component
                                :is="expandedSeries.has(serie.id) ? ChevronDown : ChevronRight"
                                class="w-5 h-5"
                            />
                            <div class="text-left">
                                <h2 class="text-lg font-semibold">{{ serie.name }}</h2>
                                <p class="text-sm text-muted-foreground">
                                    {{ serie.abbreviation }} • {{ serie.ownedCards }}/{{ serie.totalCards }} cartes
                                </p>
                            </div>
                        </div>
                        <div class="text-sm font-medium">
                            {{ Math.round((serie.ownedCards / serie.totalCards) * 100) }}%
                        </div>
                    </button>

                    <!-- Sets de la série -->
                    <div v-if="expandedSeries.has(serie.id)" class="bg-background">
                        <div
                            v-for="set in serie.sets"
                            :key="set.id"
                            class="border-t"
                        >
                            <!-- En-tête de set -->
                            <button
                                @click="toggleSet(set.id)"
                                class="w-full px-6 py-3 flex items-center justify-between hover:bg-muted/30 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <component
                                        :is="expandedSets.has(set.id) ? ChevronDown : ChevronRight"
                                        class="w-4 h-4"
                                    />
                                    <div class="text-left">
                                        <h3 class="font-medium">{{ set.name }}</h3>
                                        <p class="text-xs text-muted-foreground">
                                            {{ set.abbreviation }} • {{ set.ownedCards }}/{{ set.totalCards }} cartes
                                        </p>
                                    </div>
                                </div>
                                <div class="text-sm font-medium">
                                    {{ Math.round((set.ownedCards / set.totalCards) * 100) }}%
                                </div>
                            </button>

                            <!-- Cartes du set -->
                            <div
                                v-if="expandedSets.has(set.id)"
                                class="px-6 py-4 bg-muted/10"
                            >
                                <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                                    <CardItem
                                        v-for="card in set.cards"
                                        :key="card.id"
                                        :card="card"
                                        :quantity="card.quantity"
                                        :owned="card.owned"
                                        :show-trade-button="card.owned"
                                        @trade="handleTrade"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message si aucune carte -->
            <div
                v-else
                class="flex flex-col items-center justify-center min-h-[400px] border rounded-lg border-dashed"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="48"
                    height="48"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="text-muted-foreground mb-4"
                >
                    <rect width="18" height="18" x="3" y="3" rx="2" />
                    <path d="M3 9h18" />
                    <path d="M9 21V9" />
                </svg>
                <p class="text-lg font-semibold">Aucune carte trouvée</p>
                <p class="text-sm text-muted-foreground mt-1">
                    Essayez un autre filtre
                </p>
            </div>
        </div>

        <!-- Dialog pour créer une offre d'échange -->
        <CreateTradeDialog
            v-if="selectedCard"
            v-model:open="showTradeDialog"
            :offered-card="selectedCard"
            :available-cards="availableCards"
        />
    </AppLayout>
</template>
