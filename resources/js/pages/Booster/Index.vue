<script setup lang="ts">
import SeriesList from '@/components/Booster/SeriesList.vue';
import SetSelector from '@/components/Booster/SetSelector.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
    series: Serie[];
}>();

const page = usePage();
const userCoins = computed(() => page.props.auth.user?.coin ?? 0);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Boosters',
        href: '/boosters',
    },
];

const selectedSerie = ref<Serie | null>(null);
const showSetSelector = ref(false);

const handleSerieClick = (serie: Serie) => {
    selectedSerie.value = serie;
    showSetSelector.value = true;
};

const handleBack = () => {
    showSetSelector.value = false;
    selectedSerie.value = null;
};
</script>

<template>
    <Head title="Boosters" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- En-tête de la page -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="flex items-center gap-3 text-3xl font-bold">
                        Boosters Pokémon
                    </h1>
                    <p class="mt-1 text-muted-foreground">
                        Ouvrez des boosters pour agrandir votre collection !
                    </p>
                </div>
            </div>

            <!-- Alerte si pas assez de coins -->
            <div
                v-if="userCoins < 50"
                class="rounded-lg border border-red-500/20 bg-red-500/10 p-4"
            >
                <div class="flex items-center gap-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="text-red-600 dark:text-red-400"
                    >
                        <path
                            d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"
                        />
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                    </svg>
                    <p
                        class="text-sm font-medium text-red-700 dark:text-red-300"
                    >
                        Vous n'avez pas assez de coins pour ouvrir un booster
                    </p>
                </div>
            </div>

            <!-- Liste des séries ou sélecteur de set -->
            <SeriesList
                v-if="!showSetSelector"
                :series="series"
                @select-serie="handleSerieClick"
            />

            <SetSelector v-else :serie="selectedSerie!" @back="handleBack" />
        </div>
    </AppLayout>
</template>
