<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Building2, CreditCard, LayoutGrid, Shield, Users } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { account, dashboard } from '@/routes';
import branches from '@/routes/branches';
import members from '@/routes/members';
import nfcCards from '@/routes/nfc-cards';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Members',
        href: members.index(),
        icon: Users,
    },
    {
        title: 'Branches',
        href: branches.index(),
        icon: Building2,
    },
    {
        title: 'NFC Cards',
        href: nfcCards.index(),
        icon: CreditCard,
    },
    {
        title: 'My Account',
        href: account(),
        icon: Shield,
    },
];
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
