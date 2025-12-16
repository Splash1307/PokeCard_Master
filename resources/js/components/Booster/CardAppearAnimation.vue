<script setup lang="ts">
type Card = {
    id: number;
    name: string;
    image: string;
    isNew?: boolean;
    rarity?: {
        id: number;
        name: string;
    };
};

defineProps<{
    card: Card;
    visible: boolean;
    delay: number;
}>();
</script>

<template>
    <div
        class="aspect-[3/4] rounded-lg overflow-hidden shadow-2xl transition-all duration-500"
        :class="{
            'opacity-0 scale-50 translate-y-10': !visible,
            'opacity-100 scale-100 translate-y-0': visible,
        }"
    >
        <div class="relative w-full h-full">
            <img
                :src="card.image"
                :alt="card.name"
                class="w-full h-full object-cover"
            />

            <!-- Badge NEW -->
            <div
                v-if="card.isNew && visible"
                class="absolute top-2 left-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-lg animate-in fade-in slide-in-from-top duration-500 flex items-center gap-1"
                :style="{ animationDelay: '400ms' }"
            >
                NEW
            </div>
        </div>
    </div>
</template>
