<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';

defineProps<{
    title: string;
    description: string;
    createHref: NonNullable<InertiaLinkProps['href']>;
    createLabel: string;
    emptyTitle: string;
    emptyDescription: string;
    isEmpty: boolean;
}>();
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Head :title="title" />

        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading :title="title" :description="description" />
            <Button as-child>
                <Link :href="createHref">
                    <Plus />
                    {{ createLabel }}
                </Link>
            </Button>
        </div>

        <div
            v-if="isEmpty"
            class="border-border rounded-xl border px-6 py-16 text-center"
        >
            <p class="font-medium">{{ emptyTitle }}</p>
            <p class="text-muted-foreground mt-1 text-sm">
                {{ emptyDescription }}
            </p>
            <Button class="mt-5" as-child>
                <Link :href="createHref">
                    <Plus />
                    {{ createLabel }}
                </Link>
            </Button>
        </div>

        <slot v-else />
    </div>
</template>
