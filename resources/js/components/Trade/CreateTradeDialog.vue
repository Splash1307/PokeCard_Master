<script setup lang="ts">
import CardSelector from '@/components/FilterCard/CardSelector.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    open: boolean;
    offeredCard: {
        id: number;
        name: string;
        image: string;
        rarity?: {
            name: string;
        };
        primaryType?: {
            name: string;
        };
    };
    availableCards: Array<{
        id: number;
        name: string;
        image: string;
        localId?: number;
        rarity?: {
            id: number;
            name: string;
        };
        primaryType?: {
            id: number;
            name: string;
            logo?: string;
        };
        secondaryType?: {
            id: number;
            name: string;
            logo?: string;
        };
        set?: {
            id: number;
            name: string;
            abbreviation: string;
            serie?: {
                id: number;
                name: string;
                abbreviation: string;
            };
        };
    }>;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const selectedCardId = ref<number | null>(null);

const closeDialog = () => {
    emit('update:open', false);
    selectedCardId.value = null;
};

const createTrade = () => {
    if (!selectedCardId.value) {
        alert('Veuillez sélectionner une carte à demander');
        return;
    }

    router.post(
        '/trades',
        {
            offered_card_id: props.offeredCard.id,
            requested_card_id: selectedCardId.value,
        },
        {
            onSuccess: () => {
                closeDialog();
            },
        },
    );
};
</script>

<template>
    <Dialog :open="open" @update:open="closeDialog">
        <DialogContent
            class="flex h-[calc(100vh-4rem)] max-h-[calc(100vh-4rem)] !w-[calc(100vw-4rem)] !max-w-[calc(100vw-4rem)] flex-col p-6"
        >
            <DialogHeader class="mb-4 shrink-0">
                <DialogTitle class="text-2xl"
                    >Créer une offre d'échange</DialogTitle
                >
                <DialogDescription class="text-base">
                    Sélectionnez la carte que vous souhaitez recevoir en échange
                </DialogDescription>
            </DialogHeader>

            <div class="flex min-h-0 flex-1 flex-col gap-4">
                <!-- Carte offerte -->
                <div class="shrink-0">
                    <Label class="mb-2 block text-base font-semibold"
                        >Vous proposez :</Label
                    >
                    <div
                        class="flex items-center gap-4 rounded-lg border bg-muted/50 p-4"
                    >
                        <img
                            :src="offeredCard.image"
                            :alt="offeredCard.name"
                            class="h-24 w-20 rounded object-cover"
                        />
                        <div class="flex-1">
                            <p class="text-lg font-medium">
                                {{ offeredCard.name }}
                            </p>
                            <div class="mt-2 flex gap-2">
                                <Badge
                                    v-if="offeredCard.primaryType"
                                    variant="outline"
                                    class="text-sm"
                                >
                                    {{ offeredCard.primaryType.name }}
                                </Badge>
                                <Badge
                                    v-if="offeredCard.rarity"
                                    variant="outline"
                                    class="text-sm"
                                >
                                    {{ offeredCard.rarity.name }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sélecteur de carte avec filtres -->
                <div class="min-h-0 flex-1">
                    <CardSelector
                        :available-cards="availableCards"
                        :selected-card-id="selectedCardId"
                        @update:selected-card-id="selectedCardId = $event"
                    />
                </div>
            </div>

            <DialogFooter class="mt-4 shrink-0">
                <Button variant="outline" @click="closeDialog" size="lg">
                    Annuler
                </Button>
                <Button
                    @click="createTrade"
                    :disabled="!selectedCardId"
                    size="lg"
                >
                    Créer l'offre
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
