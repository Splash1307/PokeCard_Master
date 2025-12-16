<script setup lang="ts">
import BoosterPackCard from '@/components/Booster/BoosterPackCard.vue';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

type Set = {
    id: number;
    name: string;
    abbreviation: string;
    logo?: string;
};

type Serie = {
    id: number;
    name: string;
    abbreviation: string;
    logo?: string;
    sets: Set[];
};

const props = defineProps<{
    serie: Serie;
}>();

const emit = defineEmits<{
    back: [];
}>();

const opening = ref(false);

const openBooster = (setId: number) => {
    opening.value = true;

    router.post(
        `/boosters/${setId}/open`,
        {},
        {
            onFinish: () => {
                opening.value = false;
            },
        },
    );
};

// Générer le chemin du logo de la série
const getSeriesLogo = (abbreviation: string) => {
    return `/assets/logos/${abbreviation}.png`;
};

// Générer le chemin du symbole de la série
const getSeriesSymbol = (abbreviation: string) => {
    return `/assets/symbols/${abbreviation}.png`;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Bouton retour -->
        <Button
            @click="emit('back')"
            variant="outline"
            size="sm"
            :disabled="opening"
        >
            <ArrowLeft class="mr-2 h-4 w-4" />
            Retour aux séries
        </Button>

        <!-- En-tête de la série avec logo -->
        <div class="border-b pb-4">
            <div class="mb-2 flex items-center gap-4">
                <!-- Logo de la série -->
                <div class="flex h-16 items-center">
                    <img
                        :src="getSeriesLogo(serie.abbreviation)"
                        :alt="`Logo ${serie.name}`"
                        class="max-h-full object-contain"
                        @error="
                            (e) =>
                                ((e.target as HTMLImageElement).style.display =
                                    'none')
                        "
                    />
                </div>

                <div>
                    <h2 class="flex items-center gap-2 text-2xl font-bold">
                        <!-- Symbole de la série -->
                        <img
                            :src="getSeriesSymbol(serie.abbreviation)"
                            :alt="`Symbole ${serie.name}`"
                            class="h-6 w-6 object-contain"
                            @error="
                                (e) =>
                                    ((
                                        e.target as HTMLImageElement
                                    ).style.display = 'none')
                            "
                        />
                        {{ serie.name }}
                    </h2>
                    <p class="text-muted-foreground">
                        Choisissez un set pour ouvrir un booster
                    </p>
                </div>
            </div>
        </div>

        <!-- Liste des sets -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <BoosterPackCard
                v-for="set in serie.sets"
                :key="set.id"
                :set="set"
                :disabled="opening"
                @open="openBooster"
            />
        </div>
    </div>
</template>
