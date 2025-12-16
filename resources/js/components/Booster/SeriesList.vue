<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

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

defineProps<{
    series: Serie[];
}>();

const emit = defineEmits<{
    selectSerie: [serie: Serie];
}>();

// Générer le chemin du logo de la série (si vous avez des logos de séries)
const getSeriesLogo = (abbreviation: string) => {
    return `/assets/logos/${abbreviation}.png`;
};

// Générer le chemin du symbole de la série
const getSeriesSymbol = (abbreviation: string) => {
    return `/assets/symbols/${abbreviation}.png`;
};
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <Card
            v-for="serie in series"
            :key="serie.id"
            class="cursor-pointer transition-all hover:scale-105 hover:shadow-lg"
            @click="emit('selectSerie', serie)"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <!-- Symbole de la série -->
                    <img
                        :src="getSeriesSymbol(serie.abbreviation)"
                        :alt="`Symbole ${serie.name}`"
                        class="h-5 w-5 object-contain"
                        @error="
                            (e) =>
                                ((e.target as HTMLImageElement).style.display =
                                    'none')
                        "
                    />
                    {{ serie.name }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-3">
                    <!-- Logo de la série -->
                    <div
                        class="flex h-32 w-full items-center justify-center rounded-lg bg-muted/30 p-4"
                    >
                        <img
                            :src="getSeriesLogo(serie.abbreviation)"
                            :alt="`Logo ${serie.name}`"
                            class="max-h-full max-w-full object-contain"
                            @error="
                                (e) =>
                                    ((
                                        e.target as HTMLImageElement
                                    ).style.display = 'none')
                            "
                        />
                    </div>
                    <div class="text-sm font-medium">
                        {{ serie.sets.length }} set{{
                            serie.sets.length > 1 ? 's' : ''
                        }}
                        disponible{{ serie.sets.length > 1 ? 's' : '' }}
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>

    <div
        v-if="series.length === 0"
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
        <p class="text-lg font-semibold">Aucune série disponible</p>
        <p class="mt-1 text-sm text-muted-foreground">
            Revenez plus tard pour découvrir nos boosters
        </p>
    </div>
</template>
