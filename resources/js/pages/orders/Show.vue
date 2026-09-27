<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import CancelOrderDialog from '@/components/orders/CancelOrderDialog.vue';
import PayOrderButton from '@/components/orders/PayOrderButton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDateTime, formatMoney } from '@/lib/formatters';
import { orderStatusTone } from '@/lib/order-status';
import { index } from '@/routes/orders';
import type { OrderDetail } from '@/types';

const props = defineProps<{
    order: OrderDetail;
}>();

const paymentStatus = computed(() => {
    if (!props.order.payment) {
        return {
            label: 'Not started',
            tone: 'neutral' as const,
        };
    }

    return {
        pending: { label: 'Pending', tone: 'warning' as const },
        succeeded: { label: 'Succeeded', tone: 'success' as const },
        refund_queued: { label: 'Refund queued', tone: 'info' as const },
        refund_submitting: {
            label: 'Submitting refund',
            tone: 'warning' as const,
        },
        refund_pending: {
            label: 'Refund in progress',
            tone: 'warning' as const,
        },
        refund_failed: { label: 'Refund failed', tone: 'danger' as const },
        refunded: { label: 'Refunded', tone: 'info' as const },
    }[props.order.payment.status];
});

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Orders', href: index() }],
    },
});
</script>

<template>
    <Head :title="order.order_number" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                :title="order.order_number"
                :description="`Created ${formatDateTime(order.created_at, 'long')}`"
            />
            <div class="flex flex-wrap gap-2">
                <PayOrderButton
                    v-if="order.status === 'pending'"
                    :order="order"
                />
                <CancelOrderDialog
                    v-if="order.status === 'pending'"
                    :order="order"
                />
                <Button variant="outline" as-child>
                    <Link :href="index()">
                        <ArrowLeft />
                        Back to orders
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <p class="text-muted-foreground text-sm">Status</p>
                </CardHeader>
                <CardContent>
                    <StatusBadge
                        :status="order.status"
                        :tone="orderStatusTone[order.status]"
                    />
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <p class="text-muted-foreground text-sm">Payment</p>
                </CardHeader>
                <CardContent>
                    <StatusBadge
                        :status="order.payment?.status ?? 'not_started'"
                        :label="paymentStatus.label"
                        :tone="paymentStatus.tone"
                    />
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <p class="text-muted-foreground text-sm">Items</p>
                </CardHeader>
                <CardContent class="text-2xl font-semibold">
                    {{ order.items.length }}
                </CardContent>
            </Card>
            <Card class="border-primary/20 bg-primary/5">
                <CardHeader class="pb-2">
                    <p class="text-muted-foreground text-sm">Order total</p>
                </CardHeader>
                <CardContent class="text-2xl font-semibold tabular-nums">
                    {{ formatMoney(order.total_amount) }}
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Order items</CardTitle>
                <p class="text-muted-foreground text-sm">
                    Unit prices are snapshots captured when this order was
                    created.
                </p>
            </CardHeader>
            <CardContent>
                <div class="border-border overflow-hidden rounded-xl border">
                    <Table>
                        <TableHeader class="bg-muted/50">
                            <TableRow>
                                <TableHead class="px-4">SKU</TableHead>
                                <TableHead class="px-4">Product</TableHead>
                                <TableHead class="px-4 text-right"
                                    >Unit price</TableHead
                                >
                                <TableHead class="px-4 text-right"
                                    >Quantity</TableHead
                                >
                                <TableHead class="px-4 text-right"
                                    >Line total</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in order.items"
                                :key="item.id"
                            >
                                <TableCell class="px-4 py-4">
                                    <p class="font-medium">
                                        {{ item.sku.name }}
                                    </p>
                                    <p
                                        class="text-muted-foreground font-mono text-xs"
                                    >
                                        {{ item.sku.code }}
                                    </p>
                                </TableCell>
                                <TableCell class="px-4 py-4">
                                    {{ item.sku.product.name }}
                                </TableCell>
                                <TableCell
                                    class="px-4 py-4 text-right tabular-nums"
                                >
                                    {{ formatMoney(item.unit_price) }}
                                </TableCell>
                                <TableCell
                                    class="px-4 py-4 text-right tabular-nums"
                                >
                                    {{ item.quantity }}
                                </TableCell>
                                <TableCell
                                    class="px-4 py-4 text-right font-semibold tabular-nums"
                                >
                                    {{
                                        formatMoney(
                                            item.unit_price * item.quantity,
                                        )
                                    }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
