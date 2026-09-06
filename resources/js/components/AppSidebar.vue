<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { Boxes, LayoutGrid, ShoppingCart, Warehouse } from "@lucide/vue";
import AppLogo from "@/components/AppLogo.vue";
import NavMain from "@/components/NavMain.vue";
import NavUser from "@/components/NavUser.vue";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";
import { dashboard } from "@/routes";
import { index as ordersIndex } from "@/routes/orders";
import { index as productsIndex } from "@/routes/products";
import { index as warehousesIndex } from "@/routes/warehouses";
import type { NavItem } from "@/types";

const mainNavItems: NavItem[] = [
    {
        title: "Dashboard",
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: "Products",
        href: productsIndex(),
        icon: Boxes,
    },
    {
        title: "Warehouses",
        href: warehousesIndex(),
        icon: Warehouse,
    },
    {
        title: "Orders",
        href: ordersIndex(),
        icon: ShoppingCart,
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
