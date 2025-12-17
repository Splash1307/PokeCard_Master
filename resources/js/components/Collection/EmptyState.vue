<script setup lang="ts">
defineProps<{
    message?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed"
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
            class="mb-4 text-muted-foreground"
        >
            <rect width="18" height="18" x="3" y="3" rx="2" />
            <path d="M3 9h18" />
            <path d="M9 21V9" />
        </svg>
        <p class="text-lg font-semibold">
            {{ message || 'Aucune carte trouvée' }}
        </p>
        <p class="mt-1 text-sm text-muted-foreground">
            {{ description || 'Essayez un autre filtre' }}
        </p>
    </div>
</template>
