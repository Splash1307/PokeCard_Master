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

const props = defineProps<{
    card: {
        id: number;
        name: string;
        image: string;
        localId?: number;
        rarity?: {
            name: string;
            price?: number;
        };
        primaryType?: {
            name: string;
            logo?: string;
        };
        secondaryType?: {
            name: string;
            logo?: string;
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
    showPurchaseButton?: boolean;
}>();

const emit = defineEmits<{
    trade: [cardId: number];
    purchase: [cardId: number];
}>();

const proposeTrade = () => {
    emit('trade', props.card.id);
};

const proposePurchase = () => {
    emit('purchase', props.card.id);
};
</script>

<template>
    <Card
        class="overflow-hidden transition-shadow hover:shadow-lg"
        :class="{ 'opacity-60': owned === false }"
    >
        <CardHeader class="p-4 pb-2">
            <div class="flex items-start justify-between">
                <CardTitle class="line-clamp-1 text-base">
                    {{ card.name }}
                </CardTitle>
                <Badge
                    v-if="quantity && quantity > 0"
                    variant="secondary"
                    class="ml-2 shrink-0"
                >
                    × {{ quantity }}
                </Badge>
                <Badge
                    v-else-if="owned === false"
                    variant="destructive"
                    class="ml-2 shrink-0"
                >
                    Verrouillé
                </Badge>
            </div>
        </CardHeader>

        <CardContent class="p-4 pt-0">
            <div
                class="relative mb-3 aspect-[3/4] overflow-hidden rounded-lg bg-muted"
            >
                <img
                    :src="card.image"
                    :alt="card.name"
                    class="h-full w-full object-cover"
                    :class="{ grayscale: owned === false }"
                />
                <div
                    v-if="owned === false"
                    class="absolute inset-0 flex items-center justify-center bg-black/40"
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
                        <rect
                            width="18"
                            height="11"
                            x="3"
                            y="11"
                            rx="2"
                            ry="2"
                        />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex flex-wrap gap-1">
                    <!-- Type primaire -->
                    <Badge
                        v-if="card.primaryType"
                        variant="outline"
                        class="flex items-center gap-1 text-xs"
                    >
                        <img
                            v-if="card.primaryType.logo"
                            :src="card.primaryType.logo"
                            :alt="card.primaryType.name"
                            class="h-4 w-4 object-contain"
                        />
                        <span>{{ card.primaryType.name }}</span>
                    </Badge>

                    <!-- Type secondaire -->
                    <Badge
                        v-if="card.secondaryType"
                        variant="outline"
                        class="flex items-center gap-1 text-xs"
                    >
                        <img
                            v-if="card.secondaryType.logo"
                            :src="card.secondaryType.logo"
                            :alt="card.secondaryType.name"
                            class="h-4 w-4 object-contain"
                        />
                        <span>{{ card.secondaryType.name }}</span>
                    </Badge>

                    <Badge v-if="card.rarity" variant="outline" class="text-xs">
                        {{ card.rarity.name }}
                    </Badge>
                </div>

                <div v-if="card.set" class="text-xs text-muted-foreground">
                    <div v-if="card.set.serie">{{ card.set.serie.name }}</div>
                    <div>{{ card.set.name }}</div>
                </div>
            </div>
        </CardContent>

        <CardFooter class="flex gap-2 p-4 pt-0">
            <!-- Bouton d'échange (uniquement si possédée) -->
            <Button
                v-if="showTradeButton && owned"
                @click="proposeTrade"
                class="flex-1"
                size="sm"
            >
                Échanger
            </Button>

            <!-- Bouton d'achat (toujours affiché si activé) -->
            <Button
                v-if="showPurchaseButton"
                @click="proposePurchase"
                :class="showTradeButton && owned ? 'flex-1' : 'w-full'"
                size="sm"
                :variant="owned ? 'outline' : 'default'"
            >
                <span v-if="card.rarity?.price">
                    {{ owned ? 'Racheter' : 'Acheter' }} -
                    {{ card.rarity.price }} 💰
                </span>
                <span v-else>Prix non défini</span>
            </Button>
        </CardFooter>
    </Card>
</template>
