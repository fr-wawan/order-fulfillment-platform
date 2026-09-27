<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Ban, Eye } from '@lucide/vue';
import CancelOrderDialog from '@/components/orders/CancelOrderDialog.vue';
import PaginatedTable from '@/components/PaginatedTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { TableCell, TableHead } from '@/components/ui/table';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { usePaginatedNavigation } from '@/composables/usePaginatedNavigation';
import { formatDateTime, formatMoney } from '@/lib/formatters';
import { orderStatusTone } from '@/lib/order-status';
import { index, show } from '@/routes/orders';
import type { Order, PaginatedData } from '@/types';

const props = defineProps<{
    orders: PaginatedData<Order>;
}>();

const visitPage = usePaginatedNavigation(
    () => props.orders.current_page,
    (page) => index({ query: { page } }),
);
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
            <TableHead class="w-28 px-4 text-right">Actions</TableHead>
        </template>

        <template #empty>No orders have been created yet.</template>

        <template #row="{ row: order }">
            <TableCell class="px-4 py-4 font-medium">
                {{ order.order_number }}
            </TableCell>
            <TableCell class="px-4 py-4">
                <StatusBadge
                    :status="order.status"
                    :tone="orderStatusTone[order.status]"
                />
            </TableCell>
            <TableCell class="px-4 py-4">
                {{ order.items_count }}
            </TableCell>
            <TableCell class="px-4 py-4 text-right font-medium">
                {{ formatMoney(order.total_amount) }}
            </TableCell>
            <TableCell class="text-muted-foreground px-4 py-4 text-sm">
                {{ formatDateTime(order.created_at) }}
            </TableCell>
            <TableCell class="px-4 py-4 text-right">
                <div class="flex justify-end gap-1">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" as-child>
                                <Link
                                    :href="show(order.id)"
                                    :aria-label="`View ${order.order_number}`"
                                >
                                    <Eye />
                                </Link>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>View order</TooltipContent>
                    </Tooltip>
                    <CancelOrderDialog
                        v-if="order.status === 'pending'"
                        :order="order"
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            :aria-label="`Cancel ${order.order_number}`"
                        >
                            <Ban />
                        </Button>
                    </CancelOrderDialog>
                </div>
            </TableCell>
        </template>
    </PaginatedTable>
</template>
