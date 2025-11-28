<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CardItem from '@/components/CardItem.vue';
import { Button } from '@/components/ui/button';
import { Head, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

// Types
type Card = {
    id: number;
    name: string;
    localId?: number;
    image: string;
    owned: boolean;
    quantity: number;
    rarity?: {
        id: number;
        name: string;
        price: number;
    };
    type?: {
        name: string;
    };
    set?: {
        name: string;
        abbreviation: string;
        serie?: {
            name: string;
            abbreviation: string;
        };
    };
};

// Props
const props = defineProps<{
    allCards: Card[];
}>();

const page = usePage();
const userCoins = computed(() => page.props.auth.user?.coin ?? 0);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Boutique',
        href: '/shop',
    },
];

// État pour le dialog de confirmation d'achat
const showPurchaseDialog = ref(false);
const selectedCard = ref<Card | null>(null);
const purchasing = ref(false);

// Ouvrir le dialog d'achat
const openPurchaseDialog = (card: Card) => {
    selectedCard.value = card;
    showPurchaseDialog.value = true;
};

// Confirmer l'achat
const confirmPurchase = () => {
    if (!selectedCard.value) return;

    purchasing.value = true;

    router.post(
        `/shop/purchase/${selectedCard.value.id}`,
        {},
        {
            onSuccess: () => {
                showPurchaseDialog.value = false;
                selectedCard.value = null;
            },
            onFinish: () => {
                purchasing.value = false;
            },
        }
    );
};

// Vérifier si l'utilisateur peut acheter
const canAfford = computed(() => {
    if (!selectedCard.value?.rarity?.price) return false;
    return userCoins.value >= selectedCard.value.rarity.price;
});
</script>

<template>
    <Head title="Boutique" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- En-tête de la page -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Boutique de cartes</h1>
                    <p class="text-muted-foreground mt-1">
                        Achetez des cartes avec vos coins. Prix basés sur la rareté.
                    </p>
                </div>

                <div class="flex items-center gap-2 rounded-lg bg-yellow-500/10 px-4 py-2 border border-yellow-500/20">
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
                        class="text-yellow-600 dark:text-yellow-500"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                        <path d="M12 18V6" />
                    </svg>
                    <span class="font-bold text-lg text-yellow-700 dark:text-yellow-400">
                        {{ userCoins }}
                    </span>
                </div>
            </div>

            <!-- Grille des cartes -->
            <div v-if="allCards.length > 0" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                <div
                    v-for="card in allCards"
                    :key="card.id"
                    class="relative"
                >
                    <CardItem
                        :card="card"
                        :quantity="card.quantity"
                        :owned="card.owned"
                        :show-trade-button="false"
                    />

                    <!-- Bouton d'achat en overlay -->
                    <div class="absolute bottom-4 left-4 right-4">
                        <Button
                            @click="openPurchaseDialog(card)"
                            class="w-full"
                            size="sm"
                            :variant="card.owned ? 'outline' : 'default'"
                        >
                            <span v-if="card.rarity?.price">
                                Acheter - {{ card.rarity.price }} 💰
                            </span>
                            <span v-else>Prix non défini</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Message si aucune carte -->
            <div
                v-else
                class="flex flex-col items-center justify-center min-h-[400px] border rounded-lg border-dashed"
            >
                <p class="text-lg font-semibold">Aucune carte disponible</p>
            </div>
        </div>

        <!-- Dialog de confirmation d'achat -->
        <Dialog v-model:open="showPurchaseDialog">
            <DialogContent v-if="selectedCard">
                <DialogHeader>
                    <DialogTitle>Confirmer l'achat</DialogTitle>
                    <DialogDescription>
                        Voulez-vous acheter cette carte ?
                    </DialogDescription>
                </DialogHeader>

                <div class="flex flex-col items-center gap-4 py-4">
                    <div class="w-48">
                        <img
                            :src="selectedCard.image"
                            :alt="selectedCard.name"
                            class="w-full rounded-lg"
                        />
                    </div>

                    <div class="text-center">
                        <h3 class="text-lg font-bold">{{ selectedCard.name }}</h3>
                        <p v-if="selectedCard.rarity" class="text-sm text-muted-foreground">
                            {{ selectedCard.rarity.name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4 text-lg">
                        <span class="font-semibold">Prix:</span>
                        <div class="flex items-center gap-2 rounded-lg bg-yellow-500/10 px-3 py-1.5 border border-yellow-500/20">
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
                            <span class="font-bold text-yellow-700 dark:text-yellow-400">
                                {{ selectedCard.rarity?.price ?? 0 }}
                            </span>
                        </div>
                    </div>

                    <div v-if="!canAfford" class="text-sm text-red-600 dark:text-red-400">
                         Vous n'avez pas assez de coins pour acheter cette carte
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        @click="showPurchaseDialog = false"
                        variant="outline"
                        :disabled="purchasing"
                    >
                        Annuler
                    </Button>
                    <Button
                        @click="confirmPurchase"
                        :disabled="!canAfford || purchasing"
                    >
                        <span v-if="purchasing">Achat en cours...</span>
                        <span v-else>Confirmer l'achat</span>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
