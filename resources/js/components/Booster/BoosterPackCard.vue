<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const auth = computed(() => page.props.auth);

type Set = {
    id: number;
    name: string;
    abbreviation: string;
    logo?: string;
};

const props = defineProps<{
    set: Set;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    open: [setId: number];
}>();

// Générer le chemin de l'image du booster
const getBoosterImage = (abbreviation: string) => {
    return `/assets/boosters/${abbreviation}.png`;
};

// Générer le chemin du symbole du set
const getSetSymbol = (abbreviation: string) => {
    return `/assets/symbols/${abbreviation}.png`;
};

// Générer le chemin du logo du set
const getSetLogo = (abbreviation: string) => {
    return `/assets/logos/${abbreviation}.png`;
};

const handleClick = () => {
    emit('open', props.set.id);
};
</script>

<template>
    <Card
        class="overflow-hidden transition-shadow hover:shadow-lg"
        :class="{ 'opacity-60': disabled }"
    >
        <CardHeader
            class="relative bg-gradient-to-br from-primary/20 to-primary/5"
        >
            <CardTitle class="flex items-center gap-2 text-lg">
                <!-- Symbole du set -->
                <img
                    :src="getSetSymbol(set.abbreviation)"
                    :alt="`Symbole ${set.name}`"
                    class="h-5 w-5 object-contain"
                    @error="
                        (e) =>
                            ((e.target as HTMLImageElement).style.display =
                                'none')
                    "
                />
                {{ set.name }}
            </CardTitle>
        </CardHeader>

        <CardContent class="p-4">
            <!-- Logo du set en haut -->
            <div class="mb-3 flex h-12 items-center justify-center">
                <img
                    :src="getSetLogo(set.abbreviation)"
                    :alt="`Logo ${set.name}`"
                    class="max-h-full object-contain"
                    @error="
                        (e) =>
                            ((e.target as HTMLImageElement).style.display =
                                'none')
                    "
                />
            </div>

            <!-- Image du booster cliquable -->
            <div
                @click="!disabled && handleClick()"
                class="group relative mb-3 flex aspect-[3/4] cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-blue-500/10 to-purple-500/10"
                :class="{ 'cursor-not-allowed': disabled }"
            >
                <img
                    :src="getBoosterImage(set.abbreviation)"
                    :alt="`Booster ${set.name}`"
                    class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-110"
                    @error="
                        (e) =>
                            ((e.target as HTMLImageElement).src =
                                '/assets/boosters/default.png')
                    "
                />
            </div>

            <div class="text-center text-sm text-muted-foreground">
                10 cartes aléatoires
            </div>
        </CardContent>

        <CardFooter class="p-4 pt-0">
            <Button @click="handleClick" class="w-full" :disabled="disabled">
                <!-- Bouton désactivé -->
                <span v-if="disabled">Ouverture...</span>

                <span v-else-if="auth.user.nbBooster === 0">
                    Ouvrir - 50 💰
                </span>

                <span v-else class="flex items-center gap-2">
                    Ouvrir - 1
                    <svg width="24" height="24" viewBox="0 0 24 24">
                        <rect
                            x="5"
                            y="2"
                            width="14"
                            height="20"
                            rx="2"
                            ry="2"
                            fill="url(#boosterGradient)"
                            stroke="currentColor"
                            stroke-width="0.5"
                            class="text-purple-300/50"
                        />
                    </svg>
                </span>
            </Button>
        </CardFooter>
    </Card>
</template>
