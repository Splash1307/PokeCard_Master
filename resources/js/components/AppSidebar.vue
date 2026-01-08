<script setup lang="ts">
import NavFooter from '@/components/Navbar/NavFooter.vue';
import NavMain from '@/components/Navbar/NavMain.vue';
import NavUser from '@/components/Navbar/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Library,
    ArrowLeftRight,
    ShoppingBag,
    PackageOpen,
    Shield,
    Trophy,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Items du footer (liens externes, documentation, etc.)
const footerNavItems: NavItem[] = [];

const mainNavItems = computed<NavItem[]>(() => {
    // Si l'utilisateur est admin (role_id === 1), afficher uniquement le menu Admin
    if (user.value?.role_id === 1) {
        return [
            {
                title: 'Admin',
                href: '/admin/challenges',
                icon: Shield,
            },
        ];
    }

    // Sinon, afficher le menu utilisateur normal
    return [
        {
            title: 'Accueil',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Ma Collection',
            href: '/collection',
            icon: Library,
        },
        {
            title: 'Échanges',
            href: '/trades',
            icon: ArrowLeftRight,
        },
        {
            title: 'Booster',
            href: '/boosters',
            icon: PackageOpen,
        },
        {
            title: 'Challenges',
            href: '/challenges',
            icon: Trophy,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
