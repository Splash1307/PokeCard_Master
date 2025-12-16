<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import CardAppearAnimation from '@/components/Booster/CardAppearAnimation.vue';
import FlipCard from '@/components/Booster/FlipCard.vue';

type Card = {
    id: number;
    name: string;
    localId?: number;
    image: string;
    isNew?: boolean;
    hp?: number;
    attack?: number;
    defense?: number;
    rarity?: {
        id: number;
        name: string;
    };
    primaryType?: {
        id: number;
        name: string;
    };
    set: {
        id: number;
        name: string;
        abbreviation: string;
    };
};

type Set = {
    id: number;
    name: string;
    abbreviation: string;
};

const props = defineProps<{
    cards: Card[];
    set: Set;
}>();

const emit = defineEmits<{
    complete: [];
}>();

const visibleCards = ref<number[]>([]);
const showLastCard = ref(false);
const lastCardFlipped = ref(false);

// Les 9 premières cartes
const firstNineCards = computed(() => props.cards.slice(0, 9));
// La 10ème carte
const lastCard = computed(() => props.cards[9]);

// Première ligne (5 cartes)
const firstRowCards = computed(() => firstNineCards.value.slice(0, 5));
// Deuxième ligne (4 cartes)
const secondRowCards = computed(() => firstNineCards.value.slice(5, 9));

onMounted(() => {
    // Afficher les cartes une par une
    firstNineCards.value.forEach((_, index) => {
        setTimeout(() => {
            visibleCards.value.push(index);
        }, index * 200); // Délai de 200ms entre chaque carte
    });

    // Afficher la dernière carte (dos) après toutes les autres
    setTimeout(
        () => {
            showLastCard.value = true;
        },
        firstNineCards.value.length * 200 + 300,
    );
});

const handleLastCardFlip = () => {
    lastCardFlipped.value = true;

    // Attendre 2 secondes après le flip pour terminer
    setTimeout(() => {
        emit('complete');
    }, 2000);
};
</script>

<template>
    <div
        class="flex min-h-[600px] flex-col items-center justify-center gap-6 px-4"
    >
        <!-- Titre -->
        <div class="space-y-2 text-center">
            <h2 class="text-3xl font-bold">
                Ouverture du booster {{ set.name }}
            </h2>
        </div>

        <!-- Première ligne : 5 cartes -->
        <div class="grid w-full max-w-5xl grid-cols-5 gap-3">
            <CardAppearAnimation
                v-for="(card, index) in firstRowCards"
                :key="card.id + '-' + index"
                :card="card"
                :visible="visibleCards.includes(index)"
                :delay="index * 200"
            />
        </div>

        <!-- Deuxième ligne : 4 cartes + la 10ème carte -->
        <div class="grid w-full max-w-5xl grid-cols-5 gap-3">
            <CardAppearAnimation
                v-for="(card, index) in secondRowCards"
                :key="card.id + '-' + (index + 5)"
                :card="card"
                :visible="visibleCards.includes(index + 5)"
                :delay="(index + 5) * 200"
            />

            <!-- La 10ème carte (à retourner) -->
            <div class="flex items-center justify-center">
                <FlipCard
                    v-if="showLastCard"
                    :card="lastCard"
                    :flipped="lastCardFlipped"
                    @flip="handleLastCardFlip"
                />
            </div>
        </div>

        <!-- Instruction pour retourner la carte -->
        <div
            v-if="showLastCard && !lastCardFlipped"
            class="animate-pulse text-center"
        >
            <p class="text-lg font-semibold text-primary">
                Cliquez sur la dernière carte pour la révéler !
            </p>
        </div>
    </div>
</template>
