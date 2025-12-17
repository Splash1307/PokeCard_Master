import { computed, ref, type Ref } from 'vue';

export type Card = {
    id: number;
    name: string;
    image: string;
    owned: boolean;
    quantity: number;
    localId?: number;
    rarity?: {
        id: number;
        name: string;
        price?: number;
    };
    primaryType?: {
        id: number;
        name: string;
        logo?: string;
    };
    secondaryType?: {
        id: number;
        name: string;
        logo?: string;
    };
    set?: {
        id: number;
        name: string;
        abbreviation: string;
        logo?: string;
        serie?: {
            id: number;
            name: string;
            abbreviation: string;
            logo?: string;
        };
    };
};


export function useCollection(allCards: Ref<Card[]>) {
    const filter = ref<'all' | 'owned' | 'not_owned'>('all');
    const expandedSeries = ref<Set<number>>(new Set());
    const expandedSets = ref<Set<number>>(new Set());

    const ownedCount = computed(() => {
        return allCards.value.filter((card) => card.owned).length;
    });

    const organizedCards = computed(() => {
        const filteredCards = allCards.value.filter((card) => {
            if (filter.value === 'owned') return card.owned;
            if (filter.value === 'not_owned') return !card.owned;
            return true;
        });

        const seriesMap = new Map();

        filteredCards.forEach((card) => {
            if (!card.set?.serie) return;

            const serieId = card.set.serie.id;
            const setId = card.set.id;

            if (!seriesMap.has(serieId)) {
                seriesMap.set(serieId, {
                    id: serieId,
                    name: card.set.serie.name,
                    abbreviation: card.set.serie.abbreviation,
                    sets: new Map(),
                    totalCards: 0,
                    ownedCards: 0,
                });
            }

            const serie = seriesMap.get(serieId);

            if (!serie.sets.has(setId)) {
                serie.sets.set(setId, {
                    id: setId,
                    name: card.set.name,
                    abbreviation: card.set.abbreviation,
                    cards: [],
                    totalCards: 0,
                    ownedCards: 0,
                });
            }

            const set = serie.sets.get(setId);
            set.cards.push(card);
            set.totalCards++;
            serie.totalCards++;

            if (card.owned) {
                set.ownedCards++;
                serie.ownedCards++;
            }
        });

        return Array.from(seriesMap.values()).map((serie) => ({
            ...serie,
            sets: Array.from(serie.sets.values()),
        }));
    });

    const toggleSerie = (serieId: number) => {
        if (expandedSeries.value.has(serieId)) {
            expandedSeries.value.delete(serieId);
        } else {
            expandedSeries.value.add(serieId);
        }
    };

    const toggleSet = (setId: number) => {
        if (expandedSets.value.has(setId)) {
            expandedSets.value.delete(setId);
        } else {
            expandedSets.value.add(setId);
        }
    };

    const expandAll = () => {
        organizedCards.value.forEach((serie) => {
            expandedSeries.value.add(serie.id);
            serie.sets.forEach((set: { id: number }) => {
                expandedSets.value.add(set.id);
            });
        });
    };

    const collapseAll = () => {
        expandedSeries.value.clear();
        expandedSets.value.clear();
    };

    return {
        filter,
        expandedSeries,
        expandedSets,
        ownedCount,
        organizedCards,
        toggleSerie,
        toggleSet,
        expandAll,
        collapseAll,
    };
}
