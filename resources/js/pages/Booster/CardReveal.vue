<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

type CardType = {
    id: number;
    name: string;
    localId?: number;
    image: string;
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

defineProps<{
    card: CardType;
    currentIndex: number;
    totalCards: number;
}>();

const emit = defineEmits<{
    next: [];
    previous: [];
}>();
</script>

<template>
    <div class="flex items-center justify-center gap-4">
        <Button
            @click="emit('previous')"
            :disabled="currentIndex === 0"
            variant="outline"
            size="icon"
            class="h-12 w-12"
        >
            <ChevronLeft class="h-6 w-6" />
        </Button>

        <Card
            class="w-full max-w-md animate-in overflow-hidden duration-300 fade-in zoom-in"
        >
            <CardContent class="p-6">
                <div class="space-y-4">
                    <!-- Image de la carte -->
                    <div
                        class="relative aspect-[3/4] overflow-hidden rounded-lg bg-muted"
                    >
                        <img
                            :src="card.image"
                            :alt="card.name"
                            class="h-full w-full object-cover"
                        />
                    </div>

                    <!-- Informations de la carte -->
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-2xl font-bold">{{ card.name }}</h3>
                            <Badge v-if="card.rarity" variant="secondary">
                                {{ card.rarity.name }}
                            </Badge>
                        </div>

                        <!-- Statistiques -->
                        <div
                            v-if="card.hp || card.attack || card.defense"
                            class="grid grid-cols-3 gap-4"
                        >
                            <div
                                v-if="card.hp"
                                class="rounded-lg bg-muted p-2 text-center"
                            >
                                <div class="text-sm text-muted-foreground">
                                    HP
                                </div>
                                <div class="text-xl font-bold">
                                    {{ card.hp }}
                                </div>
                            </div>
                            <div
                                v-if="card.attack"
                                class="rounded-lg bg-muted p-2 text-center"
                            >
                                <div class="text-sm text-muted-foreground">
                                    ATK
                                </div>
                                <div class="text-xl font-bold">
                                    {{ card.attack }}
                                </div>
                            </div>
                            <div
                                v-if="card.defense"
                                class="rounded-lg bg-muted p-2 text-center"
                            >
                                <div class="text-sm text-muted-foreground">
                                    DEF
                                </div>
                                <div class="text-xl font-bold">
                                    {{ card.defense }}
                                </div>
                            </div>
                        </div>

                        <!-- Type et Set -->
                        <div class="flex flex-wrap gap-2">
                            <Badge v-if="card.primaryType" variant="outline">
                                {{ card.primaryType.name }}
                            </Badge>
                            <Badge variant="outline">
                                {{ card.set.name }}
                            </Badge>
                        </div>

                        <!-- Compteur -->
                        <div class="text-center text-sm text-muted-foreground">
                            Carte {{ currentIndex + 1 }} / {{ totalCards }}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Button
            @click="emit('next')"
            :disabled="currentIndex === totalCards - 1"
            variant="outline"
            size="icon"
            class="h-12 w-12"
        >
            <ChevronRight class="h-6 w-6" />
        </Button>
    </div>
</template>
