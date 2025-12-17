<script setup lang="ts">
import FilterButton from '@/components/FilterCard/FilterButton.vue';
import { Button } from '@/components/ui/button';

defineProps<{
    filter: 'all' | 'owned' | 'not_owned';
    allCardsCount: number;
    ownedCount: number;
    activeFiltersCount?: number;
}>();

const emit = defineEmits<{
    'update:filter': [value: 'all' | 'owned' | 'not_owned'];
    expandAll: [];
    collapseAll: [];
    openAdvancedFilters: [];
}>();
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Button
            @click="emit('update:filter', 'all')"
            :variant="filter === 'all' ? 'default' : 'outline'"
            size="sm"
        >
            Toutes ({{ allCardsCount }})
        </Button>
        <Button
            @click="emit('update:filter', 'owned')"
            :variant="filter === 'owned' ? 'default' : 'outline'"
            size="sm"
        >
            Possédées ({{ ownedCount }})
        </Button>
        <Button
            @click="emit('update:filter', 'not_owned')"
            :variant="filter === 'not_owned' ? 'default' : 'outline'"
            size="sm"
        >
            Non possédées ({{ allCardsCount - ownedCount }})
        </Button>

        <!-- Bouton de filtres avancés -->
        <FilterButton
            :active-filters-count="activeFiltersCount"
            @click="emit('openAdvancedFilters')"
        />

        <div class="ml-auto flex gap-2">
            <Button @click="emit('expandAll')" variant="outline" size="sm">
                Déplier tout
            </Button>
            <Button @click="emit('collapseAll')" variant="outline" size="sm">
                Replier tout
            </Button>
        </div>
    </div>
</template>
