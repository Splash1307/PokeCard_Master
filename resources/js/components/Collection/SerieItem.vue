<script setup lang="ts">
import { ChevronDown, ChevronRight } from 'lucide-vue-next';
import SetItem from './SetItem.vue';

export type Serie = {
    id: number;
    name: string;
    abbreviation: string;
    totalCards: number;
    ownedCards: number;
    sets: Array<{
        id: number;
        name: string;
        abbreviation: string;
        totalCards: number;
        ownedCards: number;
        cards: any[];
    }>;
};

defineProps<{
    serie: Serie;
    isExpanded: boolean;
    expandedSets: Set<number>;
}>();

const emit = defineEmits<{
    toggleSerie: [serieId: number];
    toggleSet: [setId: number];
    trade: [cardId: number];
    purchase: [cardId: number];
}>();
</script>

<template>
    <div class="overflow-hidden rounded-lg border">
        <button
            @click="emit('toggleSerie', serie.id)"
            class="flex w-full items-center justify-between bg-muted/50 px-4 py-3 transition-colors hover:bg-muted"
        >
            <div class="flex items-center gap-3">
                <component
                    :is="isExpanded ? ChevronDown : ChevronRight"
                    class="h-5 w-5"
                />
                <div class="text-left">
                    <h2 class="text-lg font-semibold">{{ serie.name }}</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ serie.abbreviation }} • {{ serie.ownedCards }}/{{
                            serie.totalCards
                        }}
                        cartes
                    </p>
                </div>
            </div>
            <div class="text-sm font-medium">
                {{ Math.round((serie.ownedCards / serie.totalCards) * 100) }}%
            </div>
        </button>

        <div v-if="isExpanded" class="bg-background">
            <SetItem
                v-for="set in serie.sets"
                :key="set.id"
                :set="set"
                :is-expanded="expandedSets.has(set.id)"
                @toggle-set="emit('toggleSet', $event)"
                @trade="emit('trade', $event)"
                @purchase="emit('purchase', $event)"
            />
        </div>
    </div>
</template>
