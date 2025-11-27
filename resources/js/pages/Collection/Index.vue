<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CardItem from '@/components/CardItem.vue';
import CreateTradeDialog from '@/components/CreateTradeDialog.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// Définir les types pour les cartes
type Card = {
    id: number;
    name: string;
    image: string;
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

type CollectionItem = {
    id: number;
    nbCard: number;
    card: Card;
};

// Définir les propriétés que la page reçoit du contrôleur
const props = defineProps<{
    collection: CollectionItem[];
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

// Fonction appelée quand on clique sur "Proposer un échange"
const handleTrade = (cardId: number) => {
    const collectionItem = props.collection.find(item => item.card.id === cardId);
    if (collectionItem) {
        selectedCard.value = collectionItem.card;
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
                        Vous possédez {{ collection.length }} carte{{ collection.length > 1 ? 's' : '' }} différente{{ collection.length > 1 ? 's' : '' }}
                    </p>
                </div>

                <Link href="/trades">
                    <Button variant="outline">
                        Voir les échanges
                    </Button>
                </Link>
            </div>

            <!-- Grille des cartes -->
            <div v-if="collection.length > 0" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                <CardItem
                    v-for="item in collection"
                    :key="item.id"
                    :card="item.card"
                    :quantity="item.nbCard"
                    :show-trade-button="true"
                    @trade="handleTrade"
                />
            </div>

            <!-- Message si la collection est vide -->
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
                <p class="text-lg font-semibold">Votre collection est vide</p>
                <p class="text-sm text-muted-foreground mt-1">
                    Commencez à collectionner des cartes Pokémon !
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
