<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ChallengeCard from '@/components/Admin/ChallengeCard.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Plus } from 'lucide-vue-next';

// Interface pour un challenge
interface Challenge {
    id: number;
    title: string;
    description: string;
    start_date: string | null;
    end_date: string | null;
    status: string;
    was_active: boolean;
    reward: number;
    requirements_count: number;
    participants_count: number;
    completed_count: number;
}

defineProps<{
    challenges: Challenge[];
}>();

// Définir les breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin/challenges',
    },
    {
        title: 'Challenges',
        href: '/admin/challenges',
    },
];
</script>

<template>
    <Head title="Gestion des Challenges - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- En-tête de la page -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Gestion des Challenges</h1>
                    <p class="text-muted-foreground mt-1">
                        Créez et gérez les challenges disponibles pour les joueurs
                    </p>
                </div>

                <Link href="/admin/challenges/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Nouveau Challenge
                    </Button>
                </Link>
            </div>

            <!-- Liste des challenges -->
            <div v-if="challenges.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <ChallengeCard
                    v-for="challenge in challenges"
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
                <p class="text-lg font-semibold">Aucun challenge créé</p>
                <p class="text-sm text-muted-foreground mt-1">
                    Créez votre premier challenge pour commencer !
                </p>
            </div>
        </div>
    </AppLayout>
</template>
