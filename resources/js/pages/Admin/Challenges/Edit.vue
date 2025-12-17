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

interface ChallengeData {
    id: number;
    title: string;
    description: string;
    start_date: string | null;
    end_date: string | null;
    status: string;
    reward: number;
    requirements: Array<{
        id: number;
        type: string;
        set_id: number | null;
        target_count: number;
        cards: Array<{
            card_id: number;
            required_qty: number;
            card: {
                id: number;
                name: string;
                image: string;
            };
        }>;
    }>;
}

const props = defineProps<{
    challenge: ChallengeData;
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
        title: 'Éditer',
        href: `/admin/challenges/${props.challenge.id}/edit`,
    },
];
</script>

<template>
    <Head :title="`Éditer ${challenge.title} - Admin`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- En-tête -->
            <div>
                <h1 class="text-3xl font-bold">Éditer le Challenge</h1>
                <p class="text-muted-foreground mt-1">
                    Modifiez les informations du challenge "{{ challenge.title }}"
                </p>
            </div>

            <!-- Formulaire -->
            <ChallengeForm
                :challenge="challenge"
                :sets="sets"
                :cards="cards"
                :submit-url="`/admin/challenges/${challenge.id}`"
                submit-method="put"
                submit-label="Mettre à jour le challenge"
            />
        </div>
    </AppLayout>
</template>
