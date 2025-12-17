<script setup lang="ts">
import CollectionFilters from '@/components/Collection/CollectionFilters.vue';
import CollectionHeader from '@/components/Collection/CollectionHeader.vue';
import EmptyState from '@/components/Collection/EmptyState.vue';
import PurchaseDialog from '@/components/Collection/PurchaseDialog.vue';
import SerieItem from '@/components/Collection/SerieItem.vue';
import FilterModal from '@/components/FilterCard/FilterModal.vue';
import CreateTradeDialog from '@/components/Trade/CreateTradeDialog.vue';
import { useAdvancedFilters } from '@/composables/useAdvancedFilters';
import { useCollection, type Card } from '@/composables/useCollection';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, toRef, watch } from 'vue';

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

// Filtres avancés
const {
    filterConfig,
    filteredCards,
    applyFilters,
    resetFilters,
    activeFiltersCount,
} = useAdvancedFilters(allCardsRef);

const showFilterModal = ref(false);

// Collection avec cartes filtrées
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
} = useCollection(filteredCards);

// États des dialogs
const showTradeDialog = ref(false);
const showPurchaseDialog = ref(false);
const selectedCard = ref<Card | null>(null);
const purchasing = ref(false);

// SessionStorage
const STORAGE_KEY = 'collection_state';

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
        console.error("Erreur lors du chargement de l'état:", error);
    }
};

const saveState = () => {
    try {
        const state = {
            filter: filter.value,
            expandedSeries: Array.from(expandedSeries.value),
            expandedSets: Array.from(expandedSets.value),
        };
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (error) {
        console.error("Erreur lors de la sauvegarde de l'état:", error);
    }
};

loadState();
watch([filter, expandedSeries, expandedSets], saveState, { deep: true });

// Gestionnaires
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
    saveState();

    router.post(
        `/shop/purchase/${selectedCard.value.id}`,
        {},
        {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                showPurchaseDialog.value = false;
                selectedCard.value = null;
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
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <CollectionHeader
                :owned-count="ownedCount"
                :total-cards="allCards.length"
                :user-coins="userCoins"
            />

            <CollectionFilters
                v-model:filter="filter"
                :all-cards-count="filteredCards.length"
                :owned-count="ownedCount"
                :active-filters-count="activeFiltersCount"
                @expand-all="expandAll"
                @collapse-all="collapseAll"
                @open-advanced-filters="showFilterModal = true"
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

        <!-- Modal de filtres avancés -->
        <FilterModal
            v-model:open="showFilterModal"
            :config="filterConfig"
            search-placeholder="Rechercher par nom ou numéro (ex: Pikachu, 025)..."
            @apply="applyFilters"
            @reset="resetFilters"
        />

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
