<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Filter } from 'lucide-vue-next';

defineProps<{
    activeFiltersCount?: number;
}>();

const emit = defineEmits<{
    click: [];
}>();
</script>

<template>
    <Button @click="emit('click')" variant="outline" size="sm" class="relative">
        <Filter class="mr-2 h-4 w-4" />
        Filtres
        <span
            v-if="activeFiltersCount && activeFiltersCount > 0"
            class="ml-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-xs text-primary-foreground"
        >
            {{ activeFiltersCount }}
        </span>
    </Button>
</template>
