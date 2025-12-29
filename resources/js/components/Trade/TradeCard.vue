<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Définir les propriétés que le composant reçoit
const props = defineProps<{
    trade: {
        id: number;
        status: string;
        can_accept?: boolean;
        is_valid?: boolean;
        creator: {
            pseudo: string;
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
        responder?: {
            name: string;
        };
        created_at: string;
    };
    showActions?: boolean;
    showCreator?: boolean;
    isMyOffer?: boolean;
    isNewCard?: boolean; // Nouvelle prop pour afficher le badge NEW
}>();

const isAcceptDialogOpen = ref(false);
const isCancelDialogOpen = ref(false);

// Ouvrir le dialog de confirmation d'acceptation
const acceptTrade = () => {
    isAcceptDialogOpen.value = true;
};

// Confirmer l'acceptation de l'échange
const confirmAccept = () => {
    isAcceptDialogOpen.value = false;
    router.post(`/trades/${props.trade.id}/accept`);
};

// Ouvrir le dialog de confirmation d'annulation
const cancelTrade = () => {
    isCancelDialogOpen.value = true;
};

// Confirmer l'annulation de l'échange
const confirmCancel = () => {
    isCancelDialogOpen.value = false;
    router.post(`/trades/${props.trade.id}/cancel`);
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

const imageUrl = props.trade.requested_card.image;
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <CardTitle class="text-lg"
                    >Échange {{ props.trade.creator.pseudo }}</CardTitle
                >
                <Badge :class="getStatusColor(trade.status)">
                    {{ getStatusText(trade.status) }}
                </Badge>
            </div>
        </CardHeader>

        <CardContent
            v-if="trade.offered_card && trade.requested_card"
            class="p-4"
        >
            <div class="flex flex-col gap-4">
                <!-- Carte offerte -->
                <div class="space-y-2">
                    <p
                        class="text-center text-xs font-semibold text-muted-foreground"
                    >
                        J'offre
                    </p>
                    <div
                        class="relative overflow-hidden rounded-lg border-2 border-primary/20 bg-primary/5 p-3"
                    >
                        <!-- Badge NEW en haut à gauche -->
                        <div
                            v-if="isNewCard"
                            class="absolute top-1 left-1 z-10 flex items-center gap-1 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 px-2 py-1 text-[10px] font-bold text-white shadow-lg"
                        >
                            NEW
                        </div>

                        <div class="mb-2 flex justify-center">
                            <img
                                :src="trade.offered_card.image"
                                :alt="trade.offered_card.name"
                                class="h-32 w-auto object-contain"
                            />
                        </div>
                        <p
                            class="truncate px-1 text-center text-sm font-semibold"
                        >
                            {{ trade.offered_card.name }}
                        </p>
                        <div class="mt-2 flex flex-wrap justify-center gap-1">
                            <Badge
                                v-if="trade.offered_card.type"
                                variant="outline"
                                class="text-xs"
                            >
                                {{ trade.offered_card.type.name }}
                            </Badge>
                            <Badge
                                v-if="trade.offered_card.rarity"
                                variant="secondary"
                                class="text-xs"
                            >
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
                        class="rotate-90 text-primary"
                    >
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </div>

                <!-- Carte demandée -->
                <div class="space-y-2">
                    <p
                        class="text-center text-xs font-semibold text-muted-foreground"
                    >
                        Je veux
                    </p>
                    <div
                        class="overflow-hidden rounded-lg border-2 border-green-500/20 bg-green-500/5 p-3"
                    >
                        <div class="mb-2 flex justify-center">
                            <img
                                :src="imageUrl"
                                :alt="imageUrl"
                                class="h-32 w-auto object-contain"
                            />
                        </div>
                        <p
                            class="truncate px-1 text-center text-sm font-semibold"
                        >
                            {{ trade.requested_card.name }}
                        </p>
                        <div class="mt-2 flex flex-wrap justify-center gap-1">
                            <Badge
                                v-if="trade.requested_card.type"
                                variant="outline"
                                class="text-xs"
                            >
                                {{ trade.requested_card.type.name }}
                            </Badge>
                            <Badge
                                v-if="trade.requested_card.rarity"
                                variant="secondary"
                                class="text-xs"
                            >
                                {{ trade.requested_card.rarity.name }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <p
                v-if="trade.responder"
                class="mt-4 text-center text-sm text-muted-foreground"
            >
                Échangé avec <strong>{{ trade.responder.name }}</strong>
            </p>
        </CardContent>

        <!-- Message si les données sont incomplètes -->
        <CardContent v-else>
            <p class="py-4 text-center text-sm text-muted-foreground">
                Chargement des informations de l'échange...
            </p>
        </CardContent>

        <!-- Actions pour mes offres créées : seulement Annuler -->
        <CardFooter
            v-if="showActions && trade.status === 'pending' && isMyOffer"
            class="flex flex-col gap-2"
        >
            <p
                v-if="trade.is_valid === false"
                class="text-center text-xs text-destructive"
            >
                Vous ne possédez plus cette carte. Cet échange sera
                automatiquement annulé.
            </p>
            <Button @click="cancelTrade" class="w-full" variant="destructive">
                Annuler mon offre
            </Button>
        </CardFooter>

        <!-- Actions pour les offres des autres : seulement Accepter -->
        <CardFooter
            v-else-if="showActions && trade.status === 'pending' && !isMyOffer"
            class="flex flex-col gap-2"
        >
            <Button
                @click="acceptTrade"
                class="w-full"
                variant="default"
                :disabled="trade.can_accept === false"
            >
                {{
                    trade.can_accept === false
                        ? 'Échange impossible'
                        : "Accepter l'échange"
                }}
            </Button>
        </CardFooter>
    </Card>

    <!-- Dialog de confirmation d'acceptation -->
    <Dialog v-model:open="isAcceptDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Confirmer l'acceptation</DialogTitle>
                <DialogDescription>
                    Êtes-vous sûr de vouloir accepter cet échange ?
                    <br />
                    Vous donnerez <strong>{{ trade.requested_card.name }}</strong> et recevrez <strong>{{ trade.offered_card.name }}</strong>.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="isAcceptDialogOpen = false">
                    Annuler
                </Button>
                <Button variant="default" @click="confirmAccept">
                    Accepter l'échange
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Dialog de confirmation d'annulation -->
    <Dialog v-model:open="isCancelDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Confirmer l'annulation</DialogTitle>
                <DialogDescription>
                    Êtes-vous sûr de vouloir annuler cette offre d'échange ?
                    Cette action est irréversible.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="isCancelDialogOpen = false">
                    Retour
                </Button>
                <Button variant="destructive" @click="confirmCancel">
                    Annuler l'offre
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
