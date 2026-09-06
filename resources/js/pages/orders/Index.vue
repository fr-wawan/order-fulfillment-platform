<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { Plus } from "@lucide/vue";
import Heading from "@/components/Heading.vue";
import OrderTable from "@/components/orders/OrderTable.vue";
import { Button } from "@/components/ui/button";
import { create, index } from "@/routes/orders";
import type { Order, PaginatedData } from "@/types";

defineProps<{
    orders: PaginatedData<Order>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Orders",
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Orders" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading title="Orders" description="Review orders and create new ones." />
            <Button as-child>
                <Link :href="create()">
                    <Plus />
                    Create order
                </Link>
            </Button>
        </div>

        <div
            v-if="orders.data.length === 0"
            class="border-border rounded-xl border px-6 py-16 text-center"
        >
            <p class="font-medium">No orders yet</p>
            <p class="text-muted-foreground mt-1 text-sm">
                Create your first order to get started.
            </p>
            <Button class="mt-5" as-child>
                <Link :href="create()">
                    <Plus />
                    Create order
                </Link>
            </Button>
        </div>

        <OrderTable v-else :orders="orders" />
    </div>
</template>
