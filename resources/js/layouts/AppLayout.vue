<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { TooltipProvider } from '@/components/ui/tooltip';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <TooltipProvider :delay-duration="0">
        <AppLayout :breadcrumbs="breadcrumbs">
            <slot />
        </AppLayout>
    </TooltipProvider>
</template>
