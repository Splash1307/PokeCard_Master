<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TradeCard from '@/components/TradeCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// Définir les propriétés que la page reçoit du contrôleur
defineProps<{
    createdTrades: Array<{
        id: number;
        status: string;
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
        responder?: {
            name: string;
        };
        created_at: string;
    }>;
    respondedTrades: Array<{
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
    {
        title: 'Mes échanges',
        href: '/trades/my',
    },
];

</script>

<template>
    <Head title="Mes échanges" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- En-tête de la page -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Mes échanges</h1>
                    <p class="text-muted-foreground mt-1">
                        Gérez vos offres d'échange et suivez vos transactions
                    </p>
                </div>

                <div class="flex gap-2">
                    <Link href="/trades">
                        <Button variant="outline">
                            Voir toutes les offres
                        </Button>
                    </Link>

                </div>
            </div>



            <!-- Mes offres créées -->
            <div>
                <h2 class="text-2xl font-semibold mb-4">Mes offres créées</h2>

                <div v-if="createdTrades.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <TradeCard
                        v-for="trade in createdTrades"
                        :key="trade.id"
                        :trade="trade"
                        :show-actions="trade.status === 'pending'"
                        :show-creator="false"
                        :is-my-offer="true"
                    />
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center min-h-[200px] border rounded-lg border-dashed"
                >
                    <p class="text-muted-foreground">Vous n'avez créé aucune offre d'échange</p>
                </div>
            </div>

            <!-- Échanges que j'ai acceptés -->
            <div>
                <h2 class="text-2xl font-semibold mb-4">Échanges acceptés</h2>

                <div v-if="respondedTrades.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <TradeCard
                        v-for="trade in respondedTrades"
                        :key="trade.id"
                        :trade="trade"
                        :show-actions="false"
                        :show-creator="true"
                        :is-my-offer="false"
                    />
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center min-h-[200px] border rounded-lg border-dashed"
                >
                    <p class="text-muted-foreground">Vous n'avez accepté aucun échange</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
