<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { Eye } from "@lucide/vue";
import PaginatedTable from "@/components/PaginatedTable.vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { TableCell, TableHead } from "@/components/ui/table";
import { formatMoney } from "@/lib/formatters";
import { index, show } from "@/routes/orders";
import type { Order, PaginatedData } from "@/types";

const props = defineProps<{
    orders: PaginatedData<Order>;
}>();

const dateFormatter = new Intl.DateTimeFormat("en-US", {
    dateStyle: "medium",
    timeStyle: "short",
});

function visitPage(page: number): void {
    if (page === props.orders.current_page) {
        return;
    }

    router.visit(index({ query: { page } }), {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <PaginatedTable
        :pagination="orders"
        :row-key="(order) => order.id"
        :column-count="6"
        @page-change="visitPage"
    >
        <template #header>
            <TableHead class="px-4">Order</TableHead>
            <TableHead class="px-4">Status</TableHead>
            <TableHead class="px-4">Items</TableHead>
            <TableHead class="px-4 text-right">Total</TableHead>
            <TableHead class="px-4">Created</TableHead>
            <TableHead class="w-20 px-4 text-right">Actions</TableHead>
        </template>

        <template #empty>No orders have been created yet.</template>

        <template #row="{ row: order }">
            <TableCell class="px-4 py-4 font-medium">
                {{ order.order_number }}
            </TableCell>
            <TableCell class="px-4 py-4">
                <Badge :variant="order.status === 'pending' ? 'default' : 'secondary'">
                    {{ order.status === "pending" ? "Pending" : "Cancelled" }}
                </Badge>
            </TableCell>
            <TableCell class="px-4 py-4">
                {{ order.items_count }}
            </TableCell>
            <TableCell class="px-4 py-4 text-right font-medium">
                {{ formatMoney(order.total_amount) }}
            </TableCell>
            <TableCell class="text-muted-foreground px-4 py-4 text-sm">
                {{ dateFormatter.format(new Date(order.created_at)) }}
            </TableCell>
            <TableCell class="px-4 py-4 text-right">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="show(order.id)" :aria-label="`View ${order.order_number}`">
                        <Eye />
                    </Link>
                </Button>
            </TableCell>
        </template>
    </PaginatedTable>
</template>
