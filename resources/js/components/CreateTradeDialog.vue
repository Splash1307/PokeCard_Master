<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Définir les propriétés que le composant reçoit
const props = defineProps<{
    open: boolean;
    offeredCard: {
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
    availableCards: Array<{
        id: number;
        name: string;
        image: string;
        rarity?: {
            name: string;
        };
        type?: {
            name: string;
        };
    }>;
}>();

// Événements émis par le composant
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// État local
const selectedCardId = ref<number | null>(null);
const searchQuery = ref('');

// Cartes filtrées par la recherche
const filteredCards = computed(() => {
    if (!searchQuery.value) {
        return props.availableCards;
    }

    const query = searchQuery.value.toLowerCase();
    return props.availableCards.filter(card =>
        card.name.toLowerCase().includes(query)
    );
});

// Fonction pour fermer le dialog
const closeDialog = () => {
    emit('update:open', false);
    selectedCardId.value = null;
    searchQuery.value = '';
};

// Fonction pour créer l'offre d'échange
const createTrade = () => {
    if (!selectedCardId.value) {
        alert('Veuillez sélectionner une carte à demander');
        return;
    }

    router.post('/trades', {
        offered_card_id: props.offeredCard.id,
        requested_card_id: selectedCardId.value,
    }, {
        onSuccess: () => {
            closeDialog();
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="closeDialog">
        <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Créer une offre d'échange</DialogTitle>
                <DialogDescription>
                    Sélectionnez la carte que vous souhaitez recevoir en échange de votre carte
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <!-- Carte offerte -->
                <div>
                    <Label class="text-sm font-semibold mb-2 block">Vous proposez :</Label>
                    <div class="flex items-center gap-3 p-3 border rounded-lg bg-muted/50">
                        <img
                            :src="offeredCard.image"
                            :alt="offeredCard.name"
                            class="w-16 h-20 object-cover rounded"
                        />
                        <div class="flex-1">
                            <p class="font-medium">{{ offeredCard.name }}</p>
                            <div class="flex gap-1 mt-1">
                                <Badge v-if="offeredCard.type" variant="outline" class="text-xs">
                                    {{ offeredCard.type.name }}
                                </Badge>
                                <Badge v-if="offeredCard.rarity" variant="outline" class="text-xs">
                                    {{ offeredCard.rarity.name }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recherche -->
                <div>
                    <Label for="search" class="text-sm font-semibold mb-2 block">
                        Vous demandez :
                    </Label>
                    <Input
                        id="search"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher une carte..."
                        class="mb-3"
                    />
                </div>

                <!-- Liste des cartes disponibles -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[400px] overflow-y-auto p-1">
                    <div
                        v-for="card in filteredCards"
                        :key="card.id"
                        @click="selectedCardId = card.id"
                        class="cursor-pointer border rounded-lg p-2 transition-all hover:shadow-md"
                        :class="{
                            'ring-2 ring-primary': selectedCardId === card.id,
                            'hover:border-primary': selectedCardId !== card.id
                        }"
                    >
                        <img
                            :src="card.image"
                            :alt="card.name"
                            class="w-full aspect-[3/4] object-cover rounded mb-2"
                        />
                        <p class="text-sm font-medium line-clamp-1">{{ card.name }}</p>
                        <div class="flex gap-1 mt-1">
                            <Badge v-if="card.type" variant="outline" class="text-xs">
                                {{ card.type.name }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Message si aucune carte trouvée -->
                    <div
                        v-if="filteredCards.length === 0"
                        class="col-span-full text-center py-8 text-muted-foreground"
                    >
                        Aucune carte trouvée
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="closeDialog">
                    Annuler
                </Button>
                <Button @click="createTrade" :disabled="!selectedCardId">
                    Créer l'offre
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
