<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TradeCard from '@/components/TradeCard.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

// Définir les propriétés que la page reçoit du contrôleur
defineProps<{
    trades: Array<{
        id: number;
        status: string;
        creator: {
            name: string;
        };
        offered_card: {
            id: number;
            name: string;
            image: string;
            rarity?: {
                name: string;
            };
            type?: {
                name: string;
            };
        };
        requested_card: {
            id: number;
            name: string;
            image: string;
            rarity?: {
                name: string;
            };
            type?: {
                name: string;
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
