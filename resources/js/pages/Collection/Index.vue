<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CardItem from '@/components/CardItem.vue';
import CreateTradeDialog from '@/components/CreateTradeDialog.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

// Définir les types pour les cartes
type Card = {
    id: number;
    name: string;
    image: string;
    owned: boolean;
    quantity: number;
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
        name: string;
        series?: {
            name: string;
        };
    };
};

// Définir les propriétés que la page reçoit du contrôleur
const props = defineProps<{
    allCards: Card[];
    availableCards: Card[];
}>();

// Définir les breadcrumbs (fil d'Ariane)
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ma Collection',
        href: '/collection',
    },
];

// État pour gérer le dialog de création d'échange
const showTradeDialog = ref(false);
const selectedCard = ref<Card | null>(null);

// Filtre actif : 'all' | 'owned' | 'not_owned'
const filter = ref<'all' | 'owned' | 'not_owned'>('all');

// Cartes filtrées selon le filtre actif
const filteredCards = computed(() => {
    if (filter.value === 'owned') {
        return props.allCards.filter(card => card.owned);
    }
    if (filter.value === 'not_owned') {
        return props.allCards.filter(card => !card.owned);
    }
    return props.allCards;
});

// Nombre de cartes possédées
const ownedCount = computed(() => {
    return props.allCards.filter(card => card.owned).length;
});

// Fonction appelée quand on clique sur "Proposer un échange"
const handleTrade = (cardId: number) => {
    const card = props.allCards.find(c => c.id === cardId);
    if (card && card.owned) {
        selectedCard.value = card;
        showTradeDialog.value = true;
    }
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

            <!-- Filtres -->
            <div class="flex gap-2">
                <Button
                    @click="filter = 'all'"
                    :variant="filter === 'all' ? 'default' : 'outline'"
                >
                    Toutes ({{ allCards.length }})
                </Button>
                <Button
                    @click="filter = 'owned'"
                    :variant="filter === 'owned' ? 'default' : 'outline'"
                >
                    Possédées ({{ ownedCount }})
                </Button>
                <Button
                    @click="filter = 'not_owned'"
                    :variant="filter === 'not_owned' ? 'default' : 'outline'"
                >
                    Non possédées ({{ allCards.length - ownedCount }})
                </Button>
            </div>

            <!-- Grille des cartes -->
            <div v-if="filteredCards.length > 0" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                <CardItem
                    v-for="card in filteredCards"
                    :key="card.id"
                    :card="card"
                    :quantity="card.quantity"
                    :owned="card.owned"
                    :show-trade-button="card.owned"
                    @trade="handleTrade"
                />
            </div>

            <!-- Message si aucune carte avec ce filtre -->
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
