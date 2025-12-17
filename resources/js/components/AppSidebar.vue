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
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Items du footer (liens externes, documentation, etc.)
const footerNavItems: NavItem[] = [];

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
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
    ];

    // Ajouter l'item Admin si l'utilisateur est admin (role_id === 1)
    if (user.value?.role_id === 1) {
        items.push({
            title: 'Admin',
            href: '/admin/challenges',
            icon: Shield,
        });
    }

    return items;
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
