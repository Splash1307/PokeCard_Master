<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import TradeCard from '@/components/Trade/TradeCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import FilterButton from '@/components/FilterCard/FilterButton.vue';
import type { FilterState } from '@/components/FilterCard/FilterModal.vue';
import FilterModal from '@/components/FilterCard/FilterModal.vue';

const props = defineProps<{
    trades: {
        data: Array<{
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
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        total: number;
    };
    filters: {
        search?: string;
        rarity?: string;
        type?: string;
        serie?: string;
        set?: string;
    };
    filterConfig: {
        series: Array<{ label: string; value: number; count: number }>;
        sets: Array<{ label: string; value: number; count: number }>;
        types: Array<{
            label: string;
            value: string;
            count: number;
            logo?: string;
        }>;
        rarities: Array<{ label: string; value: string; count: number }>;
    };
}>();

// Définir les breadcrumbs (fil d'Ariane)
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Échanges',
        href: '/trades',
    },
];

const isFilterModalOpen = ref(false);

// État local des filtres (synchronisé avec les props)
const localFilterState = ref<FilterState>({
    searchQuery: props.filters.search || '',
    selectedSeries: new Set(
        props.filters.serie ? props.filters.serie.split(',').map(Number) : [],
    ),
    selectedSets: new Set(
        props.filters.set ? props.filters.set.split(',').map(Number) : [],
    ),
    selectedTypes: new Set(
        props.filters.type ? props.filters.type.split(',') : [],
    ),
    selectedRarities: new Set(
        props.filters.rarity ? props.filters.rarity.split(',') : [],
    ),
});

// Compter les filtres actifs
const activeFiltersCount = ref(0);
watch(
    localFilterState,
    () => {
        let count = 0;
        if (localFilterState.value.searchQuery) count++;
        count += localFilterState.value.selectedSeries.size;
        count += localFilterState.value.selectedSets.size;
        count += localFilterState.value.selectedTypes.size;
        count += localFilterState.value.selectedRarities.size;
        activeFiltersCount.value = count;
    },
    { deep: true, immediate: true },
);

// Appliquer les filtres (envoyer au backend)
const applyFilters = (filters: FilterState) => {
    localFilterState.value = filters;

    router.get(
        '/trades',
        {
            search: filters.searchQuery || undefined,
            serie:
                filters.selectedSeries.size > 0
                    ? Array.from(filters.selectedSeries).join(',')
                    : undefined,
            set:
                filters.selectedSets.size > 0
                    ? Array.from(filters.selectedSets).join(',')
                    : undefined,
            type:
                filters.selectedTypes.size > 0
                    ? Array.from(filters.selectedTypes).join(',')
                    : undefined,
            rarity:
                filters.selectedRarities.size > 0
                    ? Array.from(filters.selectedRarities).join(',')
                    : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Réinitialiser les filtres
const resetFilters = () => {
    localFilterState.value = {
        searchQuery: '',
        selectedSeries: new Set(),
        selectedSets: new Set(),
        selectedTypes: new Set(),
        selectedRarities: new Set(),
    };

    router.get(
        '/trades',
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};
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
            <div class="text-sm text-muted-foreground">
                {{ trades.total }} résultat(s) trouvé(s)
            </div>

            <!-- Liste des offres d'échange -->
            <div
                v-if="trades.data.length > 0"
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <TradeCard
                    v-for="trade in trades.data"
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

            <!-- Pagination -->
            <Pagination :links="trades.links" />
        </div>
    </AppLayout>
</template>
