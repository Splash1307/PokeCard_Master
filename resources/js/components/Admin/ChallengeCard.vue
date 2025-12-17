<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { Edit, Trash2, Power, List, Package, Archive } from 'lucide-vue-next';
import { ref } from 'vue';

interface Requirement {
    id: number;
    type: string;
    set_name: string | null;
    target_count: number;
    cards_count: number;
}

interface Challenge {
    id: number;
    title: string;
    description: string;
    start_date: string | null;
    end_date: string | null;
    status: string;
    reward: number;
    requirements: Requirement[];
}

const props = defineProps<{
    challenge: Challenge;
}>();

const isDeleting = ref(false);
const isTogglingStatus = ref(false);

// Tronquer la description à 100 caractères
const truncatedDescription = (text: string) => {
    return text.length > 100 ? text.substring(0, 100) + '...' : text;
};

// Déterminer la couleur du badge de statut
const statusVariant = (status: string) => {
    if (status === 'Actif') return 'default';
    if (status === 'Inactif') return 'secondary';
    return 'outline';
};

// Formater une date
const formatDate = (date: string | null) => {
    if (!date) return 'Non définie';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

// Supprimer un challenge
const deleteChallenge = () => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce challenge ?')) return;

    isDeleting.value = true;
    router.delete(`/admin/challenges/${props.challenge.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

// Basculer le statut d'un challenge
const toggleStatus = () => {
    isTogglingStatus.value = true;
    router.post(
        `/admin/challenges/${props.challenge.id}/toggle-status`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isTogglingStatus.value = false;
            },
        }
    );
};

// Éditer un challenge
const editChallenge = () => {
    router.visit(`/admin/challenges/${props.challenge.id}/edit`);
};

// Obtenir le label du type de requirement
const getRequirementTypeLabel = (type: string) => {
    switch (type) {
        case 'CARD_LIST':
            return 'Liste de cartes';
        case 'OPEN_PACKS':
            return 'Ouvrir boosters';
        case 'OWN_CARDS':
            return 'Posséder cartes';
        default:
            return type;
    }
};

// Obtenir l'icône du type de requirement
const getRequirementIcon = (type: string) => {
    switch (type) {
        case 'CARD_LIST':
            return List;
        case 'OPEN_PACKS':
            return Package;
        case 'OWN_CARDS':
            return Archive;
        default:
            return List;
    }
};
</script>

<template>
    <Card class="flex flex-col">
        <CardHeader>
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1">
                    <CardTitle class="text-xl">{{ challenge.title }}</CardTitle>
                    <CardDescription class="mt-1">
                        {{ truncatedDescription(challenge.description) }}
                    </CardDescription>
                </div>
                <Badge :variant="statusVariant(challenge.status)">
                    {{ challenge.status }}
                </Badge>
            </div>
        </CardHeader>

        <CardContent class="flex-1 space-y-4">
            <!-- Dates -->
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Début:</span>
                    <span class="font-medium">{{ formatDate(challenge.start_date) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Fin:</span>
                    <span class="font-medium">{{ formatDate(challenge.end_date) }}</span>
                </div>
            </div>

            <!-- Récompense -->
            <div class="flex items-center justify-between rounded-lg bg-muted p-3">
                <span class="text-sm text-muted-foreground">Récompense</span>
                <div class="flex items-center gap-1 font-bold text-lg">
                    <span>{{ challenge.reward }}</span>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="text-yellow-600 dark:text-yellow-500"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                        <path d="M12 18V6" />
                    </svg>
                </div>
            </div>

            <!-- Requirements -->
            <div class="space-y-2">
                <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                    Requirements ({{ challenge.requirements.length }})
                </div>

                <div v-if="challenge.requirements.length === 0" class="text-sm text-muted-foreground text-center py-2">
                    Aucun requirement
                </div>

                <div v-else class="space-y-1.5">
                    <div
                        v-for="req in challenge.requirements"
                        :key="req.id"
                        class="flex items-center gap-2 rounded-md bg-muted/50 p-2 text-sm"
                    >
                        <component :is="getRequirementIcon(req.type)" class="h-4 w-4 flex-shrink-0 text-muted-foreground" />
                        <div class="flex-1 min-w-0">
                            <div class="font-medium truncate">
                                {{ getRequirementTypeLabel(req.type) }}
                            </div>
                            <div class="text-xs text-muted-foreground truncate">
                                <template v-if="req.type === 'CARD_LIST'">
                                    {{ req.cards_count }} carte(s)
                                </template>
                                <template v-else-if="req.type === 'OPEN_PACKS'">
                                    {{ req.set_name }} - {{ req.target_count }} booster(s)
                                </template>
                                <template v-else-if="req.type === 'OWN_CARDS'">
                                    {{ req.set_name }} - {{ req.target_count }} carte(s)
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>

        <CardFooter class="flex gap-2">
            <Button variant="outline" size="sm" class="flex-1" @click="editChallenge">
                <Edit class="mr-2 h-4 w-4" />
                Éditer
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="isTogglingStatus"
                @click="toggleStatus"
            >
                <Power class="h-4 w-4" />
            </Button>
            <Button
                variant="destructive"
                size="sm"
                :disabled="isDeleting"
                @click="deleteChallenge"
            >
                <Trash2 class="h-4 w-4" />
            </Button>
        </CardFooter>
    </Card>
</template>
