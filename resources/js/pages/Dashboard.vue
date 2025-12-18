<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Trophy, ShoppingBag, Album, Users, TrendingUp } from 'lucide-vue-next';

interface Stats {
    total_cards: number;
    unique_cards: number;
    coins: number;
}

interface Challenge {
    id: number;
    title: string;
    reward: number;
    status: string;
    progress_percentage: number;
}

interface RecentCard {
    id: number;
    name: string;
    image: string;
    set_name: string;
    obtained_at: string;
}

interface RecentTrade {
    id: number;
    partner: string;
    offered_card: string;
    requested_card: string;
    status: string;
    is_creator: boolean;
    created_at: string;
}

defineProps<{
    stats: Stats;
    active_challenges: Challenge[];
    recent_cards: RecentCard[];
    recent_trades: RecentTrade[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const getStatusVariant = (status: string) => {
    if (status === 'accepted') return 'default';
    if (status === 'pending') return 'secondary';
    return 'outline';
};

const getStatusLabel = (status: string) => {
    if (status === 'accepted') return 'Accepté';
    if (status === 'pending') return 'En attente';
    if (status === 'cancelled') return 'Annulé';
    return status;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

            <!-- En-tête avec titre -->
            <div>
                <h1 class="text-3xl font-bold">Tableau de bord</h1>
                <p class="text-muted-foreground mt-1">Bienvenue sur votre espace de jeu</p>
            </div>

            <!-- Statistiques principales -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Collection totale</CardTitle>
                        <Album class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_cards }}</div>
                        <p class="text-xs text-muted-foreground">{{ stats.unique_cards }} cartes uniques</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pièces</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.coins }}</div>
                        <p class="text-xs text-muted-foreground">Monnaie disponible</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Challenges actifs</CardTitle>
                        <Trophy class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ active_challenges.length }}</div>
                        <p class="text-xs text-muted-foreground">En cours</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Accès rapide -->
            <div>
                <h2 class="text-xl font-semibold mb-3">Accès rapide</h2>
                <div class="grid gap-4 md:grid-cols-4">
                    <Link href="/boosters">
                        <Card class="cursor-pointer hover:bg-accent transition-colors">
                            <CardHeader class="text-center">
                                <ShoppingBag class="h-8 w-8 mx-auto mb-2" />
                                <CardTitle class="text-base">Boosters</CardTitle>
                                <CardDescription>Ouvrir des boosters</CardDescription>
                            </CardHeader>
                        </Card>
                    </Link>

                    <Link href="/collection">
                        <Card class="cursor-pointer hover:bg-accent transition-colors">
                            <CardHeader class="text-center">
                                <Album class="h-8 w-8 mx-auto mb-2" />
                                <CardTitle class="text-base">Collection</CardTitle>
                                <CardDescription>Voir ma collection</CardDescription>
                            </CardHeader>
                        </Card>
                    </Link>

                    <Link href="/trades">
                        <Card class="cursor-pointer hover:bg-accent transition-colors">
                            <CardHeader class="text-center">
                                <Users class="h-8 w-8 mx-auto mb-2" />
                                <CardTitle class="text-base">Échanges</CardTitle>
                                <CardDescription>Échanger des cartes</CardDescription>
                            </CardHeader>
                        </Card>
                    </Link>

                    <Link href="/challenges">
                        <Card class="cursor-pointer hover:bg-accent transition-colors">
                            <CardHeader class="text-center">
                                <Trophy class="h-8 w-8 mx-auto mb-2" />
                                <CardTitle class="text-base">Challenges</CardTitle>
                                <CardDescription>Relever des défis</CardDescription>
                            </CardHeader>
                        </Card>
                    </Link>
                </div>
            </div>

            <!-- Grille principale -->
            <div class="grid gap-4 md:grid-cols-2">

                <!-- Challenges actifs -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Challenges actifs</CardTitle>
                            <Link href="/challenges">
                                <Button variant="ghost" size="sm">Voir tout</Button>
                            </Link>
                        </div>
                        <CardDescription>Votre progression sur les challenges</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="active_challenges.length > 0" class="space-y-4">
                            <div v-for="challenge in active_challenges" :key="challenge.id" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">{{ challenge.title }}</p>
                                        <p class="text-xs text-muted-foreground">{{ challenge.reward }} pièces</p>
                                    </div>
                                    <Badge variant="secondary">{{ Math.min(100, challenge.progress_percentage) }}%</Badge>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2">
                                    <div
                                        class="bg-primary h-2 rounded-full transition-all"
                                        :style="{ width: Math.min(100, challenge.progress_percentage) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <Trophy class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">Aucun challenge actif</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Activité récente -->
                <Card>
                    <CardHeader>
                        <CardTitle>Activité récente</CardTitle>
                        <CardDescription>Vos dernières actions</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Cartes de la collection -->
                            <div v-if="recent_cards.length > 0">
                                <h3 class="text-sm font-semibold mb-2">Cartes de votre collection</h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="card in recent_cards.slice(0, 3)"
                                        :key="card.id"
                                        class="flex items-center gap-3"
                                    >
                                        <img
                                            :src="card.image"
                                            :alt="card.name"
                                            class="h-12 w-8 object-cover rounded"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium truncate">{{ card.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ card.set_name }} • {{ card.obtained_at }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Échanges récents -->
                            <div v-if="recent_trades.length > 0" class="border-t pt-4">
                                <h3 class="text-sm font-semibold mb-2">Échanges récents</h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="trade in recent_trades.slice(0, 3)"
                                        :key="trade.id"
                                        class="flex items-center justify-between"
                                    >
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm">
                                                <span class="font-medium">{{ trade.partner }}</span>
                                            </p>
                                            <p class="text-xs text-muted-foreground truncate">
                                                {{ trade.is_creator ? trade.offered_card : trade.requested_card }}
                                                ↔
                                                {{ trade.is_creator ? trade.requested_card : trade.offered_card }}
                                            </p>
                                        </div>
                                        <Badge :variant="getStatusVariant(trade.status)" class="ml-2">
                                            {{ getStatusLabel(trade.status) }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <div v-if="recent_cards.length === 0 && recent_trades.length === 0" class="text-center py-8 text-muted-foreground">
                                <p class="text-sm">Aucune activité récente</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>
