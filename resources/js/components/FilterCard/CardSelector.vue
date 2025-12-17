<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAdvancedFilters } from '@/composables/useAdvancedFilters';
import { Check } from 'lucide-vue-next';
import { computed, ref, toRef } from 'vue';

export type SelectableCard = {
    id: number;
    name: string;
    image: string;
    owned: boolean;
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
};

const props = defineProps<{
    availableCards: SelectableCard[];
    selectedCardId: number | null;
}>();

const emit = defineEmits<{
    'update:selectedCardId': [value: number];
}>();

const allCardsRef = toRef(props, 'availableCards');

const ownershipFilter = ref<'all' | 'owned' | 'not_owned'>('all');

// Filtrer selon la possession
const filteredByOwnership = computed(() => {
    if (ownershipFilter.value === 'owned') {
        return allCardsRef.value.filter((card) => card.owned);
    }
    if (ownershipFilter.value === 'not_owned') {
        return allCardsRef.value.filter((card) => !card.owned);
    }
    return allCardsRef.value;
});

const filteredCardsRef = toRef(() => filteredByOwnership.value);

const {
    filterConfig,
    filteredCards,
    resetFilters,
    activeFiltersCount,
    filterState,
} = useAdvancedFilters(filteredCardsRef);

const selectCard = (cardId: number) => {
    emit('update:selectedCardId', cardId);
};

// Compter les cartes possédées
const ownedCount = computed(
    () => allCardsRef.value.filter((c) => c.owned).length,
);
</script>

<template>
    <div class="flex h-full gap-4">
        <!-- Panneau de filtres à gauche -->
        <div
            class="w-64 shrink-0 overflow-y-auto rounded-lg border bg-muted/30 p-3"
        >
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold">Filtres</h3>
                    <Button
                        v-if="
                            activeFiltersCount > 0 || ownershipFilter !== 'all'
                        "
                        @click="
                            () => {
                                resetFilters();
                                ownershipFilter = 'all';
                            }
                        "
                        variant="ghost"
                        size="sm"
                        class="h-6 text-[10px]"
                    >
                        Reset
                    </Button>
                </div>

                <!-- Filtre possession -->
                <div class="space-y-1">
                    <Label class="text-[11px] font-medium">Possession</Label>
                    <div class="flex gap-1">
                        <Button
                            @click="ownershipFilter = 'all'"
                            :variant="
                                ownershipFilter === 'all'
                                    ? 'default'
                                    : 'outline'
                            "
                            size="sm"
                            class="h-7 flex-1 text-[10px]"
                        >
                            Toutes
                        </Button>
                        <Button
                            @click="ownershipFilter = 'owned'"
                            :variant="
                                ownershipFilter === 'owned'
                                    ? 'default'
                                    : 'outline'
                            "
                            size="sm"
                            class="h-7 flex-1 text-[10px]"
                        >
                            Possédées
                        </Button>
                        <Button
                            @click="ownershipFilter = 'not_owned'"
                            :variant="
                                ownershipFilter === 'not_owned'
                                    ? 'default'
                                    : 'outline'
                            "
                            size="sm"
                            class="h-7 flex-1 text-[10px]"
                        >
                            Manquantes
                        </Button>
                    </div>
                </div>

                <!-- Barre de recherche -->
                <div class="space-y-1">
                    <Label for="search-cards" class="text-[11px] font-medium"
                        >Rechercher</Label
                    >
                    <Input
                        id="search-cards"
                        v-model="filterState.searchQuery"
                        type="text"
                        placeholder="Nom ou numéro..."
                        class="h-8 text-xs"
                    />
                </div>

                <!-- Filtres par série -->
                <div
                    v-if="filterConfig.series && filterConfig.series.length > 0"
                    class="space-y-1"
                >
                    <Label class="text-[11px] font-medium">Séries</Label>
                    <div class="space-y-0.5">
                        <Button
                            v-for="serie in filterConfig.series"
                            :key="serie.value"
                            @click="
                                filterState.selectedSeries.has(serie.value)
                                    ? filterState.selectedSeries.delete(
                                          serie.value,
                                      )
                                    : filterState.selectedSeries.add(
                                          serie.value,
                                      )
                            "
                            :variant="
                                filterState.selectedSeries.has(serie.value)
                                    ? 'default'
                                    : 'outline'
                            "
                            size="sm"
                            class="h-7 w-full justify-between text-[11px]"
                        >
                            <span class="truncate">{{ serie.label }}</span>
                            <span class="shrink-0 text-[9px] opacity-70">{{
                                serie.count
                            }}</span>
                        </Button>
                    </div>
                </div>

                <!-- Filtres par set -->
                <div
                    v-if="filterConfig.sets && filterConfig.sets.length > 0"
                    class="space-y-1"
                >
                    <Label class="text-[11px] font-medium">Extensions</Label>
                    <div class="space-y-0.5">
                        <Button
                            v-for="set in filterConfig.sets"
                            :key="set.value"
                            @click="
                                filterState.selectedSets.has(set.value)
                                    ? filterState.selectedSets.delete(set.value)
                                    : filterState.selectedSets.add(set.value)
                            "
                            :variant="
                                filterState.selectedSets.has(set.value)
                                    ? 'default'
                                    : 'outline'
                            "
                            size="sm"
                            class="h-7 w-full justify-between text-[11px]"
                        >
                            <span class="truncate">{{ set.label }}</span>
                            <span class="shrink-0 text-[9px] opacity-70">{{
                                set.count
                            }}</span>
                        </Button>
                    </div>
                </div>

                <!-- Filtres par type -->
                <div
                    v-if="filterConfig.types && filterConfig.types.length > 0"
                    class="space-y-1"
                >
                    <Label class="text-[11px] font-medium">Types</Label>
                    <div class="grid grid-cols-4 gap-1">
                        <Button
                            v-for="type in filterConfig.types"
                            :key="type.value"
                            @click="
                                filterState.selectedTypes.has(type.value)
                                    ? filterState.selectedTypes.delete(
                                          type.value,
                                      )
                                    : filterState.selectedTypes.add(type.value)
                            "
                            :variant="
                                filterState.selectedTypes.has(type.value)
                                    ? 'default'
                                    : 'outline'
                            "
                            size="sm"
                            class="flex h-12 flex-col items-center gap-0.5 p-0.5"
                            :title="type.label"
                        >
                            <div
                                class="flex h-6 w-6 items-center justify-center"
                            >
                                <img
                                    v-if="type.logo"
                                    :src="type.logo"
                                    :alt="type.label"
                                    class="max-h-full max-w-full object-contain"
                                />
                            </div>
                            <span class="text-[8px]">{{ type.count }}</span>
                        </Button>
                    </div>
                </div>

                <!-- Filtres par rareté -->
                <div
                    v-if="
                        filterConfig.rarities &&
                        filterConfig.rarities.length > 0
                    "
                    class="space-y-1"
                >
                    <Label class="text-[11px] font-medium">Raretés</Label>
                    <div class="space-y-0.5">
                        <Button
                            v-for="rarity in filterConfig.rarities"
                            :key="rarity.value"
                            @click="
                                filterState.selectedRarities.has(rarity.value)
                                    ? filterState.selectedRarities.delete(
                                          rarity.value,
                                      )
                                    : filterState.selectedRarities.add(
                                          rarity.value,
                                      )
                            "
                            :variant="
                                filterState.selectedRarities.has(rarity.value)
                                    ? 'default'
                                    : 'outline'
                            "
                            size="sm"
                            class="h-7 w-full justify-between text-[11px]"
                        >
                            <span class="truncate">{{ rarity.label }}</span>
                            <span class="shrink-0 text-[9px] opacity-70">{{
                                rarity.count
                            }}</span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grille des cartes à droite -->
        <div class="flex-1 overflow-y-auto">
            <div
                class="sticky top-0 z-10 mb-3 flex items-center justify-between bg-background pb-2"
            >
                <p class="text-sm font-medium text-muted-foreground">
                    {{ filteredCards.length }} carte(s) -
                    {{ ownedCount }} possédée(s)
                </p>
            </div>

            <div class="grid grid-cols-5 gap-3 pb-4 2xl:grid-cols-7">
                <div
                    v-for="card in filteredCards"
                    :key="card.id"
                    @click="selectCard(card.id)"
                    class="relative cursor-pointer rounded-lg border p-2 transition-all hover:shadow-lg"
                    :class="{
                        'ring-2 ring-primary': selectedCardId === card.id,
                        'hover:border-primary': selectedCardId !== card.id,
                        'opacity-60': !card.owned,
                    }"
                >
                    <!-- Badge "Possédée" en haut à droite -->
                    <div
                        v-if="card.owned"
                        class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-green-500 text-white shadow-md"
                        title="Carte possédée"
                    >
                        <Check class="h-4 w-4" />
                    </div>

                    <!-- Image de la carte -->
                    <img
                        :src="card.image"
                        :alt="card.name"
                        class="mb-2 aspect-[3/4] w-full rounded object-cover"
                    />

                    <!-- Nom de la carte -->
                    <p class="mb-1 line-clamp-1 text-xs font-medium">
                        {{ card.name }}
                    </p>

                    <!-- Type avec logo -->
                    <div
                        v-if="card.primaryType"
                        class="mb-1 flex items-center gap-1"
                    >
                        <img
                            v-if="card.primaryType.logo"
                            :src="card.primaryType.logo"
                            :alt="card.primaryType.name"
                            class="h-3 w-3 object-contain"
                        />
                        <span class="text-[10px] text-muted-foreground">
                            {{ card.primaryType.name }}
                        </span>
                    </div>

                    <!-- Série -->
                    <div v-if="card.set?.serie" class="mb-0.5">
                        <span class="text-[9px] text-muted-foreground">
                            {{ card.set.serie.name }}
                        </span>
                    </div>

                    <!-- Set -->
                    <div v-if="card.set" class="mb-0.5">
                        <span class="text-[9px] text-muted-foreground">
                            {{ card.set.name }}
                        </span>
                    </div>

                    <!-- Rareté -->
                    <div v-if="card.rarity" class="mt-1">
                        <Badge variant="outline" class="px-1 py-0 text-[9px]">
                            {{ card.rarity.name }}
                        </Badge>
                    </div>
                </div>

                <!-- Message si aucune carte trouvée -->
                <div
                    v-if="filteredCards.length === 0"
                    class="col-span-full py-12 text-center text-muted-foreground"
                >
                    Aucune carte trouvée
                </div>
            </div>
        </div>
    </div>
</template>
