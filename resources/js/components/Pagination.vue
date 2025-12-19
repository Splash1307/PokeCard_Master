<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap gap-1 justify-center mt-6">
        <template v-for="(link, index) in links" :key="index">
            <div
                v-if="link.url === null"
                class="px-4 py-2 text-sm text-gray-400 border border-gray-300 rounded cursor-not-allowed"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="px-4 py-2 text-sm border rounded transition-colors"
                :class="{
                    'bg-primary text-primary-foreground border-primary': link.active,
                    'bg-background text-foreground border-border hover:bg-muted': !link.active,
                }"
                preserve-scroll
                v-html="link.label"
            />
        </template>
    </div>
</template>
