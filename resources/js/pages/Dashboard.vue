<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Trophy, ShoppingBag, Album, Users, TrendingUp } from 'lucide-vue-next';
import { ref } from 'vue';

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
    rarity_name?: string;
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

const selectedCard = ref<RecentCard | null>(null);
const isDialogOpen = ref(false);

const openCardModal = (card: RecentCard) => {
    selectedCard.value = card;
    isDialogOpen.value = true;
};

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
            <div class="grid gap-4 md:grid-cols-2">
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

                <!-- Cartes rares -->
                <Card>
                    <CardHeader>
                        <CardTitle>Cartes les plus rares</CardTitle>
                        <CardDescription>Vos cartes les plus précieuses</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recent_cards.length > 0" class="space-y-2">
                            <div
                                v-for="card in recent_cards.slice(0, 3)"
                                :key="card.id"
                                class="flex items-center gap-3 cursor-pointer hover:bg-accent/50 rounded-md p-2 transition-colors"
                                @click="openCardModal(card)"
                            >
                                <img
                                    :src="card.image"
                                    :alt="card.name"
                                    class="h-16 w-12 object-cover rounded shadow-sm"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ card.name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ card.set_name }}
                                        <span v-if="card.rarity_name"> • {{ card.rarity_name }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <Album class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">Aucune carte dans votre collection</p>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>

        <!-- Modal pour afficher la carte en grand -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ selectedCard?.name }}</DialogTitle>
                </DialogHeader>
                <div v-if="selectedCard" class="space-y-4">
                    <div class="flex justify-center">
                        <img
                            :src="selectedCard.image"
                            :alt="selectedCard.name"
                            class="max-w-full h-auto rounded-lg shadow-lg"
                        />
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Set:</span>
                            <span class="font-medium">{{ selectedCard.set_name }}</span>
                        </div>
                        <div v-if="selectedCard.rarity_name" class="flex justify-between">
                            <span class="text-muted-foreground">Rareté:</span>
                            <span class="font-medium">{{ selectedCard.rarity_name }}</span>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
