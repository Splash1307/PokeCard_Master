<script setup lang="ts">
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';


// Définir les propriétés que le composant reçoit
const props = defineProps<{
    card: {
        id: number;
        name: string;
        image: string;
        hp?: number;
        attack?: number;
        defense?: number;
        localId?: number;
        rarity?: {
            name: string;
        };
        type?: {
            name: string;
        };
        set?: {
            name: string;
            abbreviation: string;
            serie?: {
                name: string;
                abbreviation: string;
            };
        };
    };
    quantity?: number;
    owned?: boolean;
    showTradeButton?: boolean;
}>();

// Événements émis par le composant
const emit = defineEmits<{
    trade: [cardId: number];
}>();

// Fonction pour proposer un échange
const proposeTrade = () => {
    emit('trade', props.card.id);
};
</script>

<template>
    <Card class="overflow-hidden hover:shadow-lg transition-shadow" :class="{ 'opacity-60': owned === false }">
        <CardHeader class="p-4 pb-2">
            <div class="flex items-start justify-between">
                <CardTitle class="text-base line-clamp-1">
                    {{ card.name }}
                </CardTitle>
                <Badge v-if="quantity && quantity > 0" variant="secondary" class="ml-2 shrink-0">
                    × {{ quantity }}
                </Badge>
                <Badge v-else-if="owned === false" variant="destructive" class="ml-2 shrink-0">
                    Verrouillé
                </Badge>
            </div>
        </CardHeader>

        <CardContent class="p-4 pt-0">
            <!-- Image de la carte -->
            <div class="aspect-[3/4] rounded-lg overflow-hidden bg-muted mb-3 relative">
                <img
                    :src="card.image"
                    :alt="card.name"
                    class="w-full h-full object-cover"
                    :class="{ 'grayscale': owned === false }"
                />
                <!-- Overlay pour les cartes non possédées -->
                <div
                    v-if="owned === false"
                    class="absolute inset-0 bg-black/40 flex items-center justify-center"
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
                        class="text-white"
                    >
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
            </div>

            <!-- Informations de la carte -->
            <div class="space-y-2">
                <!-- Statistiques -->
                <div v-if="card.hp || card.attack || card.defense" class="grid grid-cols-3 gap-2 text-xs">
                    <div v-if="card.hp" class="text-center">
                        <div class="text-muted-foreground">HP</div>
                        <div class="font-semibold">{{ card.hp }}</div>
                    </div>
                    <div v-if="card.attack" class="text-center">
                        <div class="text-muted-foreground">ATK</div>
                        <div class="font-semibold">{{ card.attack }}</div>
                    </div>
                    <div v-if="card.defense" class="text-center">
                        <div class="text-muted-foreground">DEF</div>
                        <div class="font-semibold">{{ card.defense }}</div>
                    </div>
                </div>

                <!-- Type et Rareté -->
                <div class="flex flex-wrap gap-1">
                    <Badge v-if="card.type" variant="outline" class="text-xs">
                        {{ card.type.name }}
                    </Badge>
                    <Badge v-if="card.rarity" variant="outline" class="text-xs">
                        {{ card.rarity.name }}
                    </Badge>
                </div>

                <!-- Set et Série -->
                <div v-if="card.set" class="text-xs text-muted-foreground">
                    <div v-if="card.set.serie">{{ card.set.serie.name }}</div>
                    <div>{{ card.set.name }}</div>
                </div>
            </div>
        </CardContent>

        <CardFooter v-if="showTradeButton" class="p-4 pt-0">
            <Button @click="proposeTrade" class="w-full" size="sm">
                Proposer un échange
            </Button>
        </CardFooter>
    </Card>
</template>
