<script setup lang="ts">
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';

// Définir les propriétés que le composant reçoit
const props = defineProps<{
    trade: {
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
        responder?: {
            name: string;
        };
        created_at: string;
    };
    showActions?: boolean;
    showCreator?: boolean;
    isMyOffer?: boolean; // True si c'est une offre créée par l'utilisateur
}>();

// Fonction pour accepter l'échange
const acceptTrade = () => {
    if (confirm('Voulez-vous vraiment accepter cet échange ?')) {
        router.post(`/trades/${props.trade.id}/accept`);
    }
};

// Fonction pour annuler l'échange
const cancelTrade = () => {
    if (confirm('Voulez-vous vraiment annuler cette offre ?')) {
        router.post(`/trades/${props.trade.id}/cancel`);
    }
};

// Définir la couleur du badge selon le statut
const getStatusColor = (status: string) => {
    if (status === 'pending') return 'bg-yellow-500';
    if (status === 'completed') return 'bg-green-500';
    if (status === 'cancelled') return 'bg-red-500';
    return 'bg-gray-500';
};

// Définir le texte du statut
const getStatusText = (status: string) => {
    if (status === 'pending') return 'En attente';
    if (status === 'completed') return 'Complété';
    if (status === 'cancelled') return 'Annulé';
    return status;
};
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <CardTitle class="text-lg">Échange de cartes</CardTitle>
                <Badge :class="getStatusColor(trade.status)">
                    {{ getStatusText(trade.status) }}
                </Badge>
            </div>
        </CardHeader>

        <CardContent v-if="trade.offered_card && trade.requested_card" class="p-4">
            <div class="flex flex-col gap-4">
                <!-- Carte offerte -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-center text-muted-foreground">J'offre</p>
                    <div class="border-2 border-primary/20 rounded-lg p-3 bg-primary/5 overflow-hidden">
                        <div class="flex justify-center mb-2">
                            <img
                                :src="trade.offered_card.image"
                                :alt="trade.offered_card.name"
                                class="h-32 w-auto object-contain"
                            />
                        </div>
                        <p class="text-sm font-semibold text-center truncate px-1">
                            {{ trade.offered_card.name }}
                        </p>
                        <div class="flex gap-1 justify-center mt-2 flex-wrap">
                            <Badge v-if="trade.offered_card.type" variant="outline" class="text-xs">
                                {{ trade.offered_card.type.name }}
                            </Badge>
                            <Badge v-if="trade.offered_card.rarity" variant="secondary" class="text-xs">
                                {{ trade.offered_card.rarity.name }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <!-- Flèche d'échange -->
                <div class="flex items-center justify-center py-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="text-primary rotate-90"
                    >
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </div>

                <!-- Carte demandée -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-center text-muted-foreground">Je veux</p>
                    <div class="border-2 border-green-500/20 rounded-lg p-3 bg-green-500/5 overflow-hidden">
                        <div class="flex justify-center mb-2">
                            <img
                                :src="trade.requested_card.image"
                                :alt="trade.requested_card.name"
                                class="h-32 w-auto object-contain"
                            />
                        </div>
                        <p class="text-sm font-semibold text-center truncate px-1">
                            {{ trade.requested_card.name }}
                        </p>
                        <div class="flex gap-1 justify-center mt-2 flex-wrap">
                            <Badge v-if="trade.requested_card.type" variant="outline" class="text-xs">
                                {{ trade.requested_card.type.name }}
                            </Badge>
                            <Badge v-if="trade.requested_card.rarity" variant="secondary" class="text-xs">
                                {{ trade.requested_card.rarity.name }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <p v-if="trade.responder" class="text-sm text-muted-foreground mt-4 text-center">
                Échangé avec <strong>{{ trade.responder.name }}</strong>
            </p>
        </CardContent>

        <!-- Message si les données sont incomplètes -->
        <CardContent v-else>
            <p class="text-sm text-muted-foreground text-center py-4">
                Chargement des informations de l'échange...
            </p>
        </CardContent>

        <!-- Actions pour mes offres créées : seulement Annuler -->
        <CardFooter v-if="showActions && trade.status === 'pending' && isMyOffer" class="flex gap-2">
            <Button @click="cancelTrade" class="w-full" variant="destructive">
                Annuler mon offre
            </Button>
        </CardFooter>

        <!-- Actions pour les offres des autres : seulement Accepter -->
        <CardFooter v-else-if="showActions && trade.status === 'pending' && !isMyOffer" class="flex gap-2">
            <Button @click="acceptTrade" class="w-full" variant="default">
                Accepter l'échange
            </Button>
        </CardFooter>
    </Card>
</template>
