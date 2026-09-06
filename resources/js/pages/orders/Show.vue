<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { ArrowLeft } from "@lucide/vue";
import Heading from "@/components/Heading.vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { formatMoney } from "@/lib/formatters";
import { index } from "@/routes/orders";
import type { OrderDetail } from "@/types";

const props = defineProps<{
    order: OrderDetail;
}>();

const dateFormatter = new Intl.DateTimeFormat("en-US", {
    dateStyle: "long",
    timeStyle: "short",
});

defineOptions({
    layout: {
        breadcrumbs: [{ title: "Orders", href: index() }],
    },
});
</script>

<template>
    <Head :title="order.order_number" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <Heading
                :title="order.order_number"
                :description="`Created ${dateFormatter.format(new Date(order.created_at))}`"
            />
            <Button variant="outline" as-child>
                <Link :href="index()">
                    <ArrowLeft />
                    Back to orders
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader class="pb-2">
                    <p class="text-muted-foreground text-sm">Status</p>
                </CardHeader>
                <CardContent>
                    <Badge :variant="order.status === 'pending' ? 'default' : 'secondary'">
                        {{ order.status === "pending" ? "Pending" : "Cancelled" }}
                    </Badge>
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
                    Unit prices are snapshots captured when this order was created.
                </p>
            </CardHeader>
            <CardContent>
                <div class="border-border overflow-hidden rounded-xl border">
                    <Table>
                        <TableHeader class="bg-muted/50">
                            <TableRow>
                                <TableHead class="px-4">SKU</TableHead>
                                <TableHead class="px-4">Product</TableHead>
                                <TableHead class="px-4 text-right">Unit price</TableHead>
                                <TableHead class="px-4 text-right">Quantity</TableHead>
                                <TableHead class="px-4 text-right">Line total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in order.items" :key="item.id">
                                <TableCell class="px-4 py-4">
                                    <p class="font-medium">{{ item.sku.name }}</p>
                                    <p class="text-muted-foreground font-mono text-xs">
                                        {{ item.sku.code }}
                                    </p>
                                </TableCell>
                                <TableCell class="px-4 py-4">
                                    {{ item.sku.product.name }}
                                </TableCell>
                                <TableCell class="px-4 py-4 text-right tabular-nums">
                                    {{ formatMoney(item.unit_price) }}
                                </TableCell>
                                <TableCell class="px-4 py-4 text-right tabular-nums">
                                    {{ item.quantity }}
                                </TableCell>
                                <TableCell class="px-4 py-4 text-right font-semibold tabular-nums">
                                    {{ formatMoney(item.unit_price * item.quantity) }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
