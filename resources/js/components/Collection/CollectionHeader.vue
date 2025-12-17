<script setup lang="ts">

defineProps<{
    ownedCount: number;
    totalCards: number;
    userCoins: number;
}>();
</script>

<template>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Ma Collection</h1>
            <p class="mt-1 text-muted-foreground">
                Vous possédez {{ ownedCount }} carte{{ ownedCount > 1 ? 's' : '' }}
                sur {{ totalCards }}
            </p>
        </div>
    </div>
</template>
