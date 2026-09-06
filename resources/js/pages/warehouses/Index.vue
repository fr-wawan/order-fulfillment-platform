<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { Plus } from "@lucide/vue";
import Heading from "@/components/Heading.vue";
import { Button } from "@/components/ui/button";
import WarehouseTable from "@/components/warehouses/WarehouseTable.vue";
import { create, index } from "@/routes/warehouses";
import type { PaginatedData, Warehouse } from "@/types";

defineProps<{ warehouses: PaginatedData<Warehouse> }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: "Warehouses", href: index() }],
    },
});
</script>

<template>
    <Head title="Warehouses" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading
                title="Warehouses"
                description="Manage storage locations and their inventory."
            />
            <Button as-child>
                <Link :href="create()"><Plus />Add warehouse</Link>
            </Button>
        </div>

        <div
            v-if="warehouses.data.length === 0"
            class="border-border rounded-xl border px-6 py-16 text-center"
        >
            <p class="font-medium">No warehouses yet</p>
            <p class="text-muted-foreground mt-1 text-sm">
                Create your first warehouse to start tracking inventory.
            </p>
            <Button class="mt-5" as-child>
                <Link :href="create()"><Plus />Add warehouse</Link>
            </Button>
        </div>

        <WarehouseTable v-else :warehouses="warehouses" />
    </div>
</template>
