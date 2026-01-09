<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const auth = computed(() => page.props.auth);

// Calcul de la progression XP (exemple: xp sur 100)
const xpProgress = computed(() => {
    const xp = auth.value.user?.xp ?? 0;
    return Math.min((xp / 100) * 100, 100); // Pourcentage de 0 à 100
});

// Calcul pour le SVG circle
const radius = 20;
const circumference = 2 * Math.PI * radius;
const strokeDashoffset = computed(() => {
    return circumference - (xpProgress.value / 100) * circumference;
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2 flex-1">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="flex items-center gap-3">
            <!-- Coins -->
            <div class="flex items-center gap-2">
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
                <span class="font-semibold text-sm text-yellow-700 dark:text-yellow-400">
                    {{ auth.user?.coin ?? 0 }}
                </span>
            </div>

            <!-- Boosters -->
            <div class="flex items-center gap-2">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    class="booster-icon"
                >
                    <!-- Dégradé pour l'icône booster -->
                    <defs>
                        <linearGradient id="boosterGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#8B5CF6;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#EC4899;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#F59E0B;stop-opacity:1" />
                        </linearGradient>
                    </defs>

                    <!-- Forme du booster (rectangle arrondi avec détails) -->
                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"
                          fill="url(#boosterGradient)" stroke="currentColor"
                          stroke-width="0.5" class="text-purple-300/50"/>

                    <!-- Bande décorative en haut -->
                    <rect x="5" y="2" width="14" height="4" rx="2" ry="2"
                          fill="white" opacity="0.3"/>

                    <!-- Étoile Pokémon stylisée au centre -->
                    <circle cx="12" cy="13" r="3.5" fill="white" opacity="0.9"/>
                    <path d="M12 10.5 L12.8 12.3 L14.8 12.3 L13.3 13.5 L13.9 15.3 L12 14.1 L10.1 15.3 L10.7 13.5 L9.2 12.3 L11.2 12.3 Z"
                          fill="url(#boosterGradient)"/>

                    <!-- Lignes de brillance -->
                    <path d="M8 8 L10 10" stroke="white" stroke-width="1" opacity="0.6" stroke-linecap="round"/>
                    <path d="M16 8 L14 10" stroke="white" stroke-width="1" opacity="0.6" stroke-linecap="round"/>
                </svg>
                <span class="font-semibold text-sm bg-gradient-to-r from-purple-600 via-pink-500 to-orange-500 bg-clip-text text-transparent">
                    {{ auth.user?.nbBooster ?? 0 }}
                </span>
            </div>

            <!-- Cercle de progression du niveau -->
            <div class="relative flex items-center justify-center">
                <svg class="w-14 h-14 -rotate-90" viewBox="0 0 50 50">
                    <!-- Cercle de fond (track) -->
                    <circle
                        cx="25"
                        cy="25"
                        :r="radius"
                        stroke="currentColor"
                        stroke-width="3"
                        fill="none"
                        class="text-blue-200 dark:text-blue-900"
                    />
                    <!-- Cercle de progression -->
                    <circle
                        cx="25"
                        cy="25"
                        :r="radius"
                        stroke="currentColor"
                        stroke-width="3"
                        fill="none"
                        class="text-blue-500 dark:text-blue-400 transition-all duration-500 ease-out"
                        :stroke-dasharray="circumference"
                        :stroke-dashoffset="strokeDashoffset"
                        stroke-linecap="round"
                    />
                </svg>
                <!-- Contenu du cercle -->
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="font-bold text-base text-blue-700 dark:text-blue-300">
                        {{ auth.user?.level_id ?? 1 }}
                    </span>
                    <span class="text-[10px] text-blue-600 dark:text-blue-400 -mt-0.5">
                        {{ auth.user?.xp ?? 0 }}/100
                    </span>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
.booster-icon {
    filter: drop-shadow(0 2px 4px rgba(139, 92, 246, 0.3));
    transition: transform 0.2s ease;
}

.booster-icon:hover {
    transform: scale(1.1);
}
</style>
