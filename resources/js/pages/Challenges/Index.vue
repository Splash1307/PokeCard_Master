<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import UserChallengeCard from '@/components/Challenges/UserChallengeCard.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';

interface Requirement {
    id: number;
    type: string;
    set_id: number | null;
    set_name: string | null;
    target_count: number;
    progress_count: number;
    completed: boolean;
    cards: Array<{
        card_id: number;
        card_name: string;
        card_image: string;
        card_set_name: string | null;
        required_qty: number;
    }>;
}

interface Challenge {
    id: number;
    title: string;
    description: string;
    start_date: string | null;
    end_date: string | null;
    reward: number;
    status: string;
    completed_at: string | null;
    claimed_at: string | null;
    can_claim: boolean;
    requirements: Requirement[];
}

const props = defineProps<{
    challenges: Challenge[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Challenges',
        href: '/challenges',
    },
];

const filter = ref<'all' | 'in_progress' | 'completed'>('all');

const filteredChallenges = computed(() => {
    if (filter.value === 'all') {
        return props.challenges;
    } else if (filter.value === 'in_progress') {
        return props.challenges.filter(c => c.status === 'En cours');
    } else {
        return props.challenges.filter(c => c.status === 'Complété' || c.status === 'Récompense récupérée');
    }
});

const counters = computed(() => ({
    all: props.challenges.length,
    in_progress: props.challenges.filter(c => c.status === 'En cours').length,
    completed: props.challenges.filter(c => c.status === 'Complété' || c.status === 'Récompense récupérée').length,
}));
</script>

<template>
    <Head title="Challenges" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- En-tête -->
            <div>
                <h1 class="text-3xl font-bold">Challenges</h1>
                <p class="text-muted-foreground mt-1">
                    Complétez les challenges pour gagner des récompenses
                </p>
            </div>

            <!-- Filtres -->
            <div class="flex gap-2">
                <Button
                    @click="filter = 'all'"
                    :variant="filter === 'all' ? 'default' : 'outline'"
                    size="sm"
                >
                    Tous ({{ counters.all }})
                </Button>
                <Button
                    @click="filter = 'in_progress'"
                    :variant="filter === 'in_progress' ? 'default' : 'outline'"
                    size="sm"
                >
                    En cours ({{ counters.in_progress }})
                </Button>
                <Button
                    @click="filter = 'completed'"
                    :variant="filter === 'completed' ? 'default' : 'outline'"
                    size="sm"
                >
                    Terminés ({{ counters.completed }})
                </Button>
            </div>

            <!-- Liste des challenges -->
            <div v-if="filteredChallenges.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <UserChallengeCard
                    v-for="challenge in filteredChallenges"
                    :key="challenge.id"
                    :challenge="challenge"
                />
            </div>

            <!-- Message si aucun challenge -->
            <div
                v-else
                class="flex flex-col items-center justify-center min-h-[400px] border rounded-lg border-dashed"
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
                    class="text-muted-foreground mb-4"
                >
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <path d="M16 10a4 4 0 0 1-8 0" />
                </svg>
                <p class="text-lg font-semibold">Aucun challenge disponible</p>
                <p class="text-sm text-muted-foreground mt-1">
                    Revenez plus tard pour découvrir de nouveaux challenges !
                </p>
            </div>
        </div>
    </AppLayout>
</template>
