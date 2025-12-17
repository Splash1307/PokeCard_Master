<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CollectionHeader from '@/components/Collection/CollectionHeader.vue';
import CollectionFilters from '@/components/Collection/CollectionFilters.vue';
import SerieItem from '@/components/Collection/SerieItem.vue';
import EmptyState from '@/components/Collection/EmptyState.vue';
import PurchaseDialog from '@/components/Collection/PurchaseDialog.vue';
import CreateTradeDialog from '@/components/Trade/CreateTradeDialog.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, toRef, watch } from 'vue';
import { useCollection, type Card } from '@/composables/useCollection';

const props = defineProps<{
    allCards: Card[];
    availableCards: Card[];
}>();

const page = usePage();
const userCoins = computed(() => page.props.auth.user?.coin ?? 0);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ma Collection',
        href: '/collection',
    },
];

const allCardsRef = toRef(props, 'allCards');

const {
    filter,
    expandedSeries,
    expandedSets,
    ownedCount,
    organizedCards,
    toggleSerie,
    toggleSet,
    expandAll,
    collapseAll,
} = useCollection(allCardsRef);

// États des dialogs
const showTradeDialog = ref(false);
const showPurchaseDialog = ref(false);
const selectedCard = ref<Card | null>(null);
const purchasing = ref(false);

// ✅ NOUVEAU: Sauvegarder l'état dans sessionStorage
const STORAGE_KEY = 'collection_state';

// ✅ Charger l'état sauvegardé au montage
const loadState = () => {
    try {
        const savedState = sessionStorage.getItem(STORAGE_KEY);
        if (savedState) {
            const state = JSON.parse(savedState);
            filter.value = state.filter || 'all';
            expandedSeries.value = new Set(state.expandedSeries || []);
            expandedSets.value = new Set(state.expandedSets || []);
        }
    } catch (error) {
        console.error('Erreur lors du chargement de l\'état:', error);
    }
};

// ✅ Sauvegarder l'état à chaque changement
const saveState = () => {
    try {
        const state = {
            filter: filter.value,
            expandedSeries: Array.from(expandedSeries.value),
            expandedSets: Array.from(expandedSets.value),
        };
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (error) {
        console.error('Erreur lors de la sauvegarde de l\'état:', error);
    }
};

// ✅ Charger l'état au démarrage
loadState();

// ✅ Sauvegarder à chaque changement
watch([filter, expandedSeries, expandedSets], saveState, { deep: true });

// Gestionnaires d'événements
const handleTrade = (cardId: number) => {
    const card = props.allCards.find((c) => c.id === cardId);
    if (card && card.owned) {
        selectedCard.value = card;
        showTradeDialog.value = true;
    }
};

const handlePurchase = (cardId: number) => {
    const card = props.allCards.find((c) => c.id === cardId);
    if (card) {
        selectedCard.value = card;
        showPurchaseDialog.value = true;
    }
};

const confirmPurchase = () => {
    if (!selectedCard.value) return;

    purchasing.value = true;

    // ✅ Sauvegarder l'état avant l'achat
    saveState();

    router.post(
        `/shop/purchase/${selectedCard.value.id}`,
        {},
        {
            preserveScroll: true, // ✅ Garde la position de scroll
            preserveState: false,
            onSuccess: () => {
                showPurchaseDialog.value = false;
                selectedCard.value = null;
                // L'état sera rechargé automatiquement depuis sessionStorage
            },
            onFinish: () => {
                purchasing.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Ma Collection" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <CollectionHeader
                :owned-count="ownedCount"
                :total-cards="allCards.length"
                :user-coins="userCoins"
            />

            <CollectionFilters
                v-model:filter="filter"
                :all-cards-count="allCards.length"
                :owned-count="ownedCount"
                @expand-all="expandAll"
                @collapse-all="collapseAll"
            />

            <div v-if="organizedCards.length > 0" class="space-y-4">
                <SerieItem
                    v-for="serie in organizedCards"
                    :key="serie.id"
                    :serie="serie"
                    :is-expanded="expandedSeries.has(serie.id)"
                    :expanded-sets="expandedSets"
                    @toggle-serie="toggleSerie"
                    @toggle-set="toggleSet"
                    @trade="handleTrade"
                    @purchase="handlePurchase"
                />
            </div>

            <EmptyState v-else />
        </div>

        <CreateTradeDialog
            v-if="selectedCard && showTradeDialog"
            v-model:open="showTradeDialog"
            :offered-card="selectedCard"
            :available-cards="availableCards"
        />

        <PurchaseDialog
            v-model:open="showPurchaseDialog"
            :card="selectedCard"
            :user-coins="userCoins"
            :purchasing="purchasing"
            @confirm="confirmPurchase"
        />
    </AppLayout>
</template>
