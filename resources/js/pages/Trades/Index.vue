<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TradeCard from '@/components/Trade/TradeCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

// Définir les propriétés que la page reçoit du contrôleur
const props = defineProps<{
    trades: Array<{
        id: number;
        status: string;
        can_accept?: boolean;
        creator: {
            name: string;
        };
        offered_card: {
            id: number;
            name: string;
            image: string;
            localId?: string;
            rarity?: {
                name: string;
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
            localId?: string;
            rarity?: {
                name: string;
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
    filters: {
        search: string;
    };
}>();

// Définir les breadcrumbs (fil d'Ariane)
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Échanges',
        href: '/trades',
    },
];

// Recherche
const search = ref(props.filters.search || '');

const performSearch = useDebounceFn(() => {
    router.get('/trades', { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch(search, () => {
    performSearch();
});
</script>

<template>
    <Head title="Échanges de cartes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- En-tête de la page -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Échanges de cartes</h1>
                    <p class="text-muted-foreground mt-1">
                        Découvrez toutes les offres d'échange disponibles
                    </p>
                </div>

                <Link href="/trades/my">
                    <Button variant="outline">
                        Mes échanges
                    </Button>
                </Link>

                <Link href="/collection">
                    <Button variant="outline">
                        Faire un échange
                    </Button>
                </Link>
            </div>

            <!-- Barre de recherche -->
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-md">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                    >
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher une carte (offerte ou demandée)..."
                        class="pl-10"
                    />
                </div>
                <div v-if="search" class="text-sm text-muted-foreground">
                    {{ trades.length }} résultat(s)
                </div>
            </div>

            <!-- Liste des offres d'échange -->
            <div v-if="trades.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <TradeCard
                    v-for="trade in trades"
                    :key="trade.id"
                    :trade="trade"
                    :show-actions="true"
                    :show-creator="true"
                    :is-my-offer="false"
                />
            </div>

            <!-- Message si aucune offre -->
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
                    <path d="M3 3h18v18H3zM15 9l-6 6m0-6l6 6" />
                </svg>
                <p class="text-lg font-semibold">Aucune offre d'échange disponible</p>
                <p class="text-sm text-muted-foreground mt-1">
                    Revenez plus tard ou créez votre propre offre !
                </p>
            </div>
        </div>
    </AppLayout>
</template>
