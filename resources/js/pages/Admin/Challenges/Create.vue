<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ChallengeForm from '@/components/Admin/ChallengeForm.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

interface Card {
    id: number;
    name: string;
    localId: string;
    image: string;
    set_name: string | null;
    rarity: string | null;
}

interface Set {
    id: number;
    name: string;
    abbreviation: string;
    serie: {
        id: number;
        name: string;
    };
}

defineProps<{
    sets: Set[];
    cards: Card[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin/challenges',
    },
    {
        title: 'Challenges',
        href: '/admin/challenges',
    },
    {
        title: 'Nouveau',
        href: '/admin/challenges/create',
    },
];
</script>

<template>
    <Head title="Créer un Challenge - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- En-tête -->
            <div>
                <h1 class="text-3xl font-bold">Créer un nouveau Challenge</h1>
                <p class="text-muted-foreground mt-1">
                    Remplissez les informations du challenge et définissez les requirements
                </p>
            </div>

            <!-- Formulaire -->
            <ChallengeForm
                :sets="sets"
                :cards="cards"
                submit-url="/admin/challenges"
                submit-method="post"
                submit-label="Créer le challenge"
            />
        </div>
    </AppLayout>
</template>
