<script setup lang="ts">
import CardItem from '@/components/CardItem.vue';
import { ChevronDown, ChevronRight } from 'lucide-vue-next';

export type Set = {
    id: number;
    name: string;
    abbreviation: string;
    totalCards: number;
    ownedCards: number;
    cards: any[];
};

defineProps<{
    set: Set;
    isExpanded: boolean;
}>();

const emit = defineEmits<{
    toggleSet: [setId: number];
    trade: [cardId: number];
    purchase: [cardId: number];
}>();
</script>

<template>
    <div class="border-t">
        <button
            @click="emit('toggleSet', set.id)"
            class="flex w-full items-center justify-between px-6 py-3 transition-colors hover:bg-muted/30"
        >
            <div class="flex items-center gap-3">
                <component
                    :is="isExpanded ? ChevronDown : ChevronRight"
                    class="h-4 w-4"
                />
                <div class="text-left">
                    <h3 class="font-medium">{{ set.name }}</h3>
                    <p class="text-xs text-muted-foreground">
                        {{ set.abbreviation }} • {{ set.ownedCards }}/{{
                            set.totalCards
                        }}
                        cartes
                    </p>
                </div>
            </div>
            <div class="text-sm font-medium">
                {{ Math.round((set.ownedCards / set.totalCards) * 100) }}%
            </div>
        </button>

        <div v-if="isExpanded" class="bg-muted/10 px-6 py-4">
            <div
                class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
            >
                <CardItem
                    v-for="card in set.cards"
                    :key="card.id"
                    :card="card"
                    :quantity="card.quantity"
                    :owned="card.owned"
                    :show-trade-button="true"
                    :show-purchase-button="true"
                    @trade="emit('trade', $event)"
                    @purchase="emit('purchase', $event)"
                />
            </div>
        </div>
    </div>
</template>
