<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { computed, ref } from 'vue';

export type FilterOption = {
    label: string;
    value: string | number;
    count?: number;
    logo?: string;
};

export type FilterConfig = {
    series?: FilterOption[];
    sets?: FilterOption[];
    types?: FilterOption[];
    rarities?: FilterOption[];
};

export type FilterState = {
    searchQuery: string;
    selectedSeries: Set<string | number>;
    selectedSets: Set<string | number>;
    selectedTypes: Set<string | number>;
    selectedRarities: Set<string | number>;
};

const props = defineProps<{
    open: boolean;
    config: FilterConfig;
    searchPlaceholder?: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    apply: [filters: FilterState];
    reset: [];
}>();

// État local des filtres
const searchQuery = ref('');
const selectedSeries = ref<Set<string | number>>(new Set());
const selectedSets = ref<Set<string | number>>(new Set());
const selectedTypes = ref<Set<string | number>>(new Set());
const selectedRarities = ref<Set<string | number>>(new Set());

// Compteur de filtres actifs
const activeFiltersCount = computed(() => {
    let count = 0;
    if (searchQuery.value) count++;
    count += selectedSeries.value.size;
    count += selectedSets.value.size;
    count += selectedTypes.value.size;
    count += selectedRarities.value.size;
    return count;
});

// Toggle une option dans un Set
const toggleOption = (set: Set<string | number>, value: string | number) => {
    if (set.has(value)) {
        set.delete(value);
    } else {
        set.add(value);
    }
};

// Réinitialiser tous les filtres
const resetFilters = () => {
    searchQuery.value = '';
    selectedSeries.value.clear();
    selectedSets.value.clear();
    selectedTypes.value.clear();
    selectedRarities.value.clear();
    emit('reset');
};

// Appliquer les filtres
const applyFilters = () => {
    emit('apply', {
        searchQuery: searchQuery.value,
        selectedSeries: new Set(selectedSeries.value),
        selectedSets: new Set(selectedSets.value),
        selectedTypes: new Set(selectedTypes.value),
        selectedRarities: new Set(selectedRarities.value),
    });
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] max-w-3xl overflow-hidden">
            <DialogHeader>
                <DialogTitle class="flex items-center justify-between">
                    <span>Filtres avancés</span>
                    <span
                        v-if="activeFiltersCount > 0"
                        class="text-sm font-normal text-muted-foreground"
                    >
                        {{ activeFiltersCount }} filtre(s) actif(s)
                    </span>
                </DialogTitle>
            </DialogHeader>

            <!-- Contenu avec scroll natif -->
            <div class="max-h-[calc(90vh-180px)] overflow-y-auto pr-4">
                <div class="space-y-6 py-4">
                    <!-- Barre de recherche -->
                    <div class="space-y-2">
                        <Label for="search">Rechercher par nom ou numéro</Label>
                        <Input
                            id="search"
                            v-model="searchQuery"
                            type="text"
                            :placeholder="
                                searchPlaceholder || 'Ex: Pikachu, 025...'
                            "
                            class="w-full"
                        />
                    </div>

                    <!-- Filtres par série -->
                    <div
                        v-if="config.series && config.series.length > 0"
                        class="space-y-2"
                    >
                        <Label class="text-base font-semibold">Séries</Label>
                        <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
                            <Button
                                v-for="serie in config.series"
                                :key="serie.value"
                                @click="
                                    toggleOption(selectedSeries, serie.value)
                                "
                                :variant="
                                    selectedSeries.has(serie.value)
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                class="h-auto py-2 text-left whitespace-normal"
                            >
                                <span
                                    class="flex w-full items-center justify-between gap-2"
                                >
                                    <span class="flex-1">{{
                                        serie.label
                                    }}</span>
                                    <span
                                        v-if="serie.count !== undefined"
                                        class="shrink-0 text-xs opacity-70"
                                    >
                                        {{ serie.count }}
                                    </span>
                                </span>
                            </Button>
                        </div>
                    </div>

                    <!-- Filtres par set -->
                    <div
                        v-if="config.sets && config.sets.length > 0"
                        class="space-y-2"
                    >
                        <Label class="text-base font-semibold"
                            >Extensions</Label
                        >
                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                            <Button
                                v-for="set in config.sets"
                                :key="set.value"
                                @click="toggleOption(selectedSets, set.value)"
                                :variant="
                                    selectedSets.has(set.value)
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                class="h-auto py-2 text-left whitespace-normal"
                            >
                                <span
                                    class="flex w-full items-center justify-between gap-2"
                                >
                                    <span class="flex-1">{{ set.label }}</span>
                                    <span
                                        v-if="set.count !== undefined"
                                        class="shrink-0 text-xs opacity-70"
                                    >
                                        {{ set.count }}
                                    </span>
                                </span>
                            </Button>
                        </div>
                    </div>

                    <!-- Filtres par type -->
                    <div
                        v-if="config.types && config.types.length > 0"
                        class="space-y-2"
                    >
                        <Label class="text-base font-semibold">Types</Label>
                        <div class="grid grid-cols-4 gap-2 lg:grid-cols-6">
                            <Button
                                v-for="type in config.types"
                                :key="type.value"
                                @click="toggleOption(selectedTypes, type.value)"
                                :variant="
                                    selectedTypes.has(type.value)
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                class="flex h-auto flex-col items-center justify-center gap-1 p-3"
                                :title="type.label"
                            >
                                <!-- Logo uniquement -->
                                <div
                                    class="flex h-10 w-10 items-center justify-center"
                                >
                                    <img
                                        v-if="type.logo"
                                        :src="type.logo"
                                        :alt="type.label"
                                        class="max-h-full max-w-full object-contain"
                                    />
                                    <span v-else class="text-xs">{{
                                        type.label
                                    }}</span>
                                </div>

                                <!-- Badge avec le nombre -->
                                <span
                                    v-if="type.count !== undefined"
                                    class="text-[10px] opacity-70"
                                >
                                    {{ type.count }}
                                </span>
                            </Button>
                        </div>
                    </div>

                    <!-- Filtres par rareté -->
                    <div
                        v-if="config.rarities && config.rarities.length > 0"
                        class="space-y-2"
                    >
                        <Label class="text-base font-semibold">Raretés</Label>
                        <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
                            <Button
                                v-for="rarity in config.rarities"
                                :key="rarity.value"
                                @click="
                                    toggleOption(selectedRarities, rarity.value)
                                "
                                :variant="
                                    selectedRarities.has(rarity.value)
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                class="h-auto py-2 text-left whitespace-normal"
                            >
                                <span
                                    class="flex w-full items-center justify-between gap-2"
                                >
                                    <span class="flex-1">{{
                                        rarity.label
                                    }}</span>
                                    <span
                                        v-if="rarity.count !== undefined"
                                        class="shrink-0 text-xs opacity-70"
                                    >
                                        {{ rarity.count }}
                                    </span>
                                </span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex gap-2">
                <Button @click="resetFilters" variant="outline" class="flex-1">
                    Réinitialiser
                </Button>
                <Button @click="applyFilters" class="flex-1">
                    Appliquer ({{ activeFiltersCount }})
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
