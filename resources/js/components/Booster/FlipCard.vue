<script setup lang="ts">
type Card = {
    id: number;
    name: string;
    image: string;
    isNew?: boolean;
    hp?: number;
    attack?: number;
    defense?: number;
    rarity?: {
        id: number;
        name: string;
    };
    primaryType?: {
        id: number;
        name: string;
    };
};

defineProps<{
    card: Card;
    flipped: boolean;
}>();

const emit = defineEmits<{
    flip: [];
}>();
</script>

<template>
    <div class="perspective-1000 w-full">
        <div
            @click="!flipped && emit('flip')"
            class="transform-style-3d relative aspect-[3/4] w-full transition-transform duration-700 hover:scale-105"
            :class="{
                'rotate-y-180': flipped,
                'cursor-pointer': !flipped,
            }"
        >
            <!-- Dos de la carte -->
            <div
                class="absolute inset-0 overflow-hidden rounded-lg shadow-2xl backface-hidden"
            >
                <img
                    src="/assets/cards/backCard.png"
                    alt="Dos de carte"
                    class="h-full w-full object-cover"
                />
                <div
                    v-if="!flipped"
                    class="absolute inset-0 animate-pulse bg-gradient-to-tr from-yellow-500/20 to-purple-500/20"
                />
            </div>

            <!-- Face de la carte -->
            <div
                class="absolute inset-0 rotate-y-180 overflow-hidden rounded-lg shadow-2xl backface-hidden"
            >
                <div class="relative h-full w-full">
                    <img
                        :src="card.image"
                        :alt="card.name"
                        class="h-full w-full object-cover"
                    />

                    <!-- Effet de brillance -->
                    <div
                        v-if="flipped"
                        class="animate-shine absolute inset-0 bg-gradient-to-tr from-transparent via-white/30 to-transparent"
                    />

                    <!-- Badge NEW -->
                    <div
                        v-if="card.isNew && flipped"
                        class="absolute top-2 left-2 flex animate-in items-center gap-1 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-lg duration-500 fade-in slide-in-from-top"
                        :style="{ animationDelay: '200ms' }"
                    >
                        NEW
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.perspective-1000 {
    perspective: 1000px;
}

.transform-style-3d {
    transform-style: preserve-3d;
}

.backface-hidden {
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

.rotate-y-180 {
    transform: rotateY(180deg);
}

@keyframes shine {
    0% {
        transform: translateX(-100%) rotate(45deg);
    }
    100% {
        transform: translateX(200%) rotate(45deg);
    }
}

.animate-shine {
    animation: shine 1.5s ease-in-out;
}
</style>
