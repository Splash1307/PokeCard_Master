import type {
    FilterConfig,
    FilterState,
} from '@/components/FilterCard/FilterModal.vue';
import type { Card } from '@/composables/useCollection';
import { computed, ref, type Ref } from 'vue';

export function useAdvancedFilters(allCards: Ref<Card[]>) {
    const filterState = ref<FilterState>({
        searchQuery: '',
        selectedSeries: new Set(),
        selectedSets: new Set(),
        selectedTypes: new Set(),
        selectedRarities: new Set(),
    });

    // Génère la configuration des filtres basée sur les cartes disponibles
    const filterConfig = computed((): FilterConfig => {
        const seriesMap = new Map<number, { name: string; count: number }>();
        const setsMap = new Map<
            number,
            { name: string; abbreviation: string; count: number }
        >();
        const typesMap = new Map<string, { name: string; logo?: string; count: number }>();
        const raritiesMap = new Map<string, { name: string; count: number }>();

        allCards.value.forEach((card) => {
            // Séries
            if (card.set?.serie) {
                const serieId = card.set.serie.id;
                if (!seriesMap.has(serieId)) {
                    seriesMap.set(serieId, {
                        name: card.set.serie.name,
                        count: 0,
                    });
                }
                seriesMap.get(serieId)!.count++;
            }

            // Sets
            if (card.set) {
                const setId = card.set.id;
                if (!setsMap.has(setId)) {
                    setsMap.set(setId, {
                        name: card.set.name,
                        abbreviation: card.set.abbreviation,
                        count: 0,
                    });
                }
                setsMap.get(setId)!.count++;
            }

            // Types primaires (avec logo)
            if (card.primaryType) {
                const typeName = card.primaryType.name;
                if (!typesMap.has(typeName)) {
                    typesMap.set(typeName, {
                        name: card.primaryType.name,
                        logo: card.primaryType.logo,
                        count: 0,
                    });
                }
                typesMap.get(typeName)!.count++;
            }

            // Raretés
            if (card.rarity) {
                const rarityName = card.rarity.name;
                if (!raritiesMap.has(rarityName)) {
                    raritiesMap.set(rarityName, {
                        name: card.rarity.name,
                        count: 0,
                    });
                }
                raritiesMap.get(rarityName)!.count++;
            }
        });

        return {
            series: Array.from(seriesMap.entries())
                .map(([id, data]) => ({
                    label: data.name,
                    value: id,
                    count: data.count,
                }))
                .sort((a, b) => a.label.localeCompare(b.label)),

            sets: Array.from(setsMap.entries())
                .map(([id, data]) => ({
                    label: data.name,
                    value: id,
                    count: data.count,
                }))
                .sort((a, b) => a.label.localeCompare(b.label)),

            types: Array.from(typesMap.entries())
                .map(([name, data]) => ({
                    label: data.name,
                    value: name,
                    count: data.count,
                    logo: data.logo,
                }))
                .sort((a, b) => a.label.localeCompare(b.label)),

            rarities: Array.from(raritiesMap.entries())
                .map(([name, data]) => ({
                    label: data.name,
                    value: name,
                    count: data.count,
                }))
                .sort((a, b) => a.label.localeCompare(b.label)),
        };
    });

    // Filtre les cartes selon l'état actuel
    const filteredCards = computed(() => {
        return allCards.value.filter((card) => {
            // Recherche par nom ou localId
            if (filterState.value.searchQuery) {
                const query = filterState.value.searchQuery.toLowerCase();
                const matchName = card.name.toLowerCase().includes(query);
                const matchLocalId = card.localId?.toString().includes(query);

                if (!matchName && !matchLocalId) {
                    return false;
                }
            }

            // Filtre par série
            if (filterState.value.selectedSeries.size > 0) {
                if (
                    !card.set?.serie ||
                    !filterState.value.selectedSeries.has(card.set.serie.id)
                ) {
                    return false;
                }
            }

            // Filtre par set
            if (filterState.value.selectedSets.size > 0) {
                if (
                    !card.set ||
                    !filterState.value.selectedSets.has(card.set.id)
                ) {
                    return false;
                }
            }

            // Filtre par type
            if (filterState.value.selectedTypes.size > 0) {
                if (
                    !card.primaryType ||
                    !filterState.value.selectedTypes.has(card.primaryType.name)
                ) {
                    return false;
                }
            }

            // Filtre par rareté
            if (filterState.value.selectedRarities.size > 0) {
                if (
                    !card.rarity ||
                    !filterState.value.selectedRarities.has(card.rarity.name)
                ) {
                    return false;
                }
            }

            return true;
        });
    });

    // Applique les filtres
    const applyFilters = (newState: FilterState) => {
        filterState.value = {
            searchQuery: newState.searchQuery,
            selectedSeries: new Set(newState.selectedSeries),
            selectedSets: new Set(newState.selectedSets),
            selectedTypes: new Set(newState.selectedTypes),
            selectedRarities: new Set(newState.selectedRarities),
        };
    };

    // Réinitialise les filtres
    const resetFilters = () => {
        filterState.value = {
            searchQuery: '',
            selectedSeries: new Set(),
            selectedSets: new Set(),
            selectedTypes: new Set(),
            selectedRarities: new Set(),
        };
    };

    // Compte les filtres actifs
    const activeFiltersCount = computed(() => {
        let count = 0;
        if (filterState.value.searchQuery) count++;
        count += filterState.value.selectedSeries.size;
        count += filterState.value.selectedSets.size;
        count += filterState.value.selectedTypes.size;
        count += filterState.value.selectedRarities.size;
        return count;
    });

    return {
        filterState,
        filterConfig,
        filteredCards,
        applyFilters,
        resetFilters,
        activeFiltersCount,
    };
}
