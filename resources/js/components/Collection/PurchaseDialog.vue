<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { computed } from 'vue';

type Card = {
    id: number;
    name: string;
    image: string;
    rarity?: {
        name: string;
        price?: number;
    };
};

const props = defineProps<{
    open: boolean;
    card: Card | null;
    userCoins: number;
    purchasing: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();

const canAfford = computed(() => {
    if (!props.card?.rarity?.price) return false;
    return props.userCoins >= props.card.rarity.price;
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="card">
            <DialogHeader>
                <DialogTitle>Confirmer l'achat</DialogTitle>
                <DialogDescription>
                    Voulez-vous acheter cette carte ?
                </DialogDescription>
            </DialogHeader>

            <div class="flex flex-col items-center gap-4 py-4">
                <div class="w-48">
                    <img
                        :src="card.image"
                        :alt="card.name"
                        class="w-full rounded-lg"
                    />
                </div>

                <div class="text-center">
                    <h3 class="text-lg font-bold">{{ card.name }}</h3>
                    <p v-if="card.rarity" class="text-sm text-muted-foreground">
                        {{ card.rarity.name }}
                    </p>
                </div>

                <div class="flex items-center gap-4 text-lg">
                    <span class="font-semibold">Prix:</span>
                    <div
                        class="flex items-center gap-2 rounded-lg border border-yellow-500/20 bg-yellow-500/10 px-3 py-1.5"
                    >
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
                            <path
                                d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"
                            />
                            <path d="M12 18V6" />
                        </svg>
                        <span
                            class="font-bold text-yellow-700 dark:text-yellow-400"
                        >
                            {{ card.rarity?.price ?? 0 }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="!canAfford"
                    class="text-sm text-red-600 dark:text-red-400"
                >
                    Vous n'avez pas assez de coins pour acheter cette carte
                </div>
            </div>

            <DialogFooter>
                <Button
                    @click="emit('update:open', false)"
                    variant="outline"
                    :disabled="purchasing"
                >
                    Annuler
                </Button>
                <Button
                    @click="emit('confirm')"
                    :disabled="!canAfford || purchasing"
                >
                    <span v-if="purchasing">Achat en cours...</span>
                    <span v-else>Confirmer l'achat</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
