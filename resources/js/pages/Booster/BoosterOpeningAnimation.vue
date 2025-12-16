<script setup lang="ts">
import { Sparkles } from 'lucide-vue-next';

type Set = {
    id: number;
    name: string;
    abbreviation: string;
};

const props = defineProps<{
    set: Set;
}>();

// Générer le chemin de l'image du booster
const getBoosterImage = (abbreviation: string) => {
    return `/assets/boosters/${abbreviation}.png`;
};
</script>

<template>
    <div class="flex min-h-[400px] flex-col items-center justify-center gap-8">
        <div class="relative">
            <!-- Image du booster qui s'ouvre -->
            <div
                class="h-64 w-48 animate-bounce overflow-hidden rounded-lg shadow-2xl"
            >
                <img
                    :src="getBoosterImage(set.abbreviation)"
                    :alt="`Booster ${set.name}`"
                    class="h-full w-full object-cover"
                    @error="
                        (e) => {
                            const target = e.target as HTMLImageElement;
                            target.style.display = 'none';
                            (target.parentElement as HTMLElement).classList.add(
                                'bg-gradient-to-br',
                                'from-blue-600',
                                'via-purple-600',
                                'to-pink-600',
                            );
                        }
                    "
                />
                <!-- Fallback si l'image n'existe pas -->
                <div
                    class="pointer-events-none absolute inset-0 flex items-center justify-center"
                >
                    <Sparkles class="h-24 w-24 animate-pulse text-white/50" />
                </div>
            </div>

            <!-- Particules qui s'échappent -->
            <div
                class="absolute -top-4 -left-4 h-3 w-3 animate-ping rounded-full bg-yellow-400"
            />
            <div
                class="absolute -top-2 -right-2 h-2 w-2 animate-ping rounded-full bg-blue-400"
                style="animation-delay: 0.2s"
            />
            <div
                class="absolute -bottom-3 left-1/2 h-2.5 w-2.5 animate-ping rounded-full bg-purple-400"
                style="animation-delay: 0.4s"
            />
        </div>

        <div class="space-y-4 text-center">
            <h2 class="text-4xl font-bold">Ouverture en cours...</h2>
            <p class="text-xl text-muted-foreground">
                {{ set.name }}
            </p>
        </div>

        <div class="flex gap-2">
            <div class="h-3 w-3 animate-bounce rounded-full bg-primary" />
            <div
                class="h-3 w-3 animate-bounce rounded-full bg-primary"
                style="animation-delay: 150ms"
            />
            <div
                class="h-3 w-3 animate-bounce rounded-full bg-primary"
                style="animation-delay: 300ms"
            />
        </div>
    </div>
</template>
