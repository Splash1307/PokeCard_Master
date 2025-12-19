<script setup lang="ts">
import TradeCard from '@/components/Trade/TradeCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, toRef } from 'vue';

import FilterButton from '@/components/FilterCard/FilterButton.vue';
import FilterModal from '@/components/FilterCard/FilterModal.vue';
import { useAdvancedFilters } from '@/composables/useAdvancedFilters';
import type { Card } from '@/composables/useCollection';

const props = defineProps<{
    trades: Array<{
        id: number;
        status: string;
        can_accept?: boolean;
        user_has_offered_card?: boolean;
        creator: {
            pseudo: string;
        };
        offered_card: {
            id: number;
            name: string;
            image: string;
            localId?: string | number;
            rarity?: {
                id: number;
                name: string;
            };
            primary_type?: {
                id: number;
                name: string;
                logo?: string;
            };
            type?: {
                name: string;
            };
            set?: {
                id: number;
                name: string;
                abbreviation?: string;
                serie?: {
                    id: number;
                    name: string;
                    abbreviation?: string;
                };
            };
        };
        requested_card: {
            id: number;
            name: string;
            image: string;
            localId?: string | number;
            rarity?: {
                id: number;
                name: string;
            };
            primary_type?: {
                id: number;
                name: string;
                logo?: string;
            };
            type?: {
                name: string;
            };
            set?: {
                id: number;
                name: string;
                abbreviation?: string;
                serie?: {
                    id: number;
                    name: string;
                    abbreviation?: string;
                };
            };
        };
        created_at: string;
    }>;
}>();

// Définir les breadcrumbs (fil d'Ariane)
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Échanges',
        href: '/trades',
    },
];

// Transformer les trades en liste de "Card" pour le filtre
const allCards = computed<Card[]>(() =>
    props.trades.map((trade) => ({
        id: trade.offered_card.id,
        name: trade.offered_card.name,
        image: trade.offered_card.image,
        localId: trade.offered_card.localId,
        rarity: trade.offered_card.rarity,
        primaryType: trade.offered_card.primary_type
            ? {
                  id: trade.offered_card.primary_type.id,
                  name: trade.offered_card.primary_type.name,
                  logo: trade.offered_card.primary_type.logo,
              }
            : undefined,
        set: trade.offered_card.set,
        owned: false,
        secondaryType: undefined,
    })),
);

const allCardsRef = toRef(() => allCards.value);
const {
    filterConfig,
    filteredCards,
    applyFilters,
    resetFilters,
    activeFiltersCount,
} = useAdvancedFilters(allCardsRef);

const isFilterModalOpen = ref(false);

const filteredTrades = computed(() => {
    const allowedIds = new Set(filteredCards.value.map((c) => c.id));
    return props.trades.filter((trade) =>
        allowedIds.has(trade.offered_card.id),
    );
});
</script>

<template>
    <Head title="Échanges de cartes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- En-tête de la page -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Échanges de cartes</h1>
                    <p class="mt-1 text-muted-foreground">
                        Découvrez toutes les offres d'échange disponibles
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <FilterButton
                        :active-filters-count="activeFiltersCount"
                        @click="isFilterModalOpen = true"
                    />

                    <Link href="/trades/my">
                        <Button variant="outline"> Mes échanges </Button>
                    </Link>

                    <Link href="/collection">
                        <Button variant="outline"> Faire un échange </Button>
                    </Link>
                </div>
            </div>

            <!-- Modal de filtres -->
            <FilterModal
                :open="isFilterModalOpen"
                :config="filterConfig"
                search-placeholder="Nom ou numéro de carte offerte..."
                @update:open="(val) => (isFilterModalOpen = val)"
                @apply="applyFilters"
                @reset="resetFilters"
            />

            <!-- Compteur de résultats -->
            <div
                v-if="activeFiltersCount > 0"
                class="text-sm text-muted-foreground"
            >
                {{ filteredTrades.length }} résultat(s)
            </div>

            <!-- Liste des offres d'échange -->
            <div
                v-if="filteredTrades.length > 0"
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <TradeCard
                    v-for="trade in filteredTrades"
                    :key="trade.id"
                    :trade="trade"
                    :show-actions="true"
                    :show-creator="true"
                    :is-my-offer="false"
                    :is-new-card="!trade.user_has_offered_card"
                />
            </div>

            <!-- Message si aucune offre -->
            <div
                v-else
                class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed"
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
                    class="mb-4 text-muted-foreground"
                >
                    <path d="M3 3h18v18H3zM15 9l-6 6m0-6l6 6" />
                </svg>
                <p class="text-lg font-semibold">
                    Aucune offre d'échange disponible
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        activeFiltersCount > 0
                            ? "Essayez d'ajuster les filtres."
                            : 'Revenez plus tard ou créez votre propre offre !'
                    }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
