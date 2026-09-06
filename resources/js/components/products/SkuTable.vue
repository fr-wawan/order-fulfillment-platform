<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { Pencil, Plus } from "@lucide/vue";
import PaginatedTable from "@/components/PaginatedTable.vue";
import SkuFormDialog from "@/components/products/SkuFormDialog.vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { formatMoney } from "@/lib/formatters";
import { edit } from "@/routes/products";
import type { PaginatedData, Sku } from "@/types";

const props = defineProps<{
    productId?: number;
    skus: PaginatedData<Sku> | null;
    disabled?: boolean;
}>();

function visitPage(page: number): void {
    if (!props.productId || page === props.skus?.current_page) {
        return;
    }

    router.visit(edit(props.productId, { query: { page } }), {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <Card :aria-disabled="disabled">
        <CardHeader class="grid-cols-[1fr_auto]">
            <div class="grid gap-1.5">
                <CardTitle>SKUs</CardTitle>
                <p class="text-muted-foreground text-sm">
                    {{
                        disabled
                            ? "Save the product before adding SKUs."
                            : "Manage the stock keeping units for this product."
                    }}
                </p>
            </div>
            <SkuFormDialog v-if="productId && !disabled" :product-id="productId">
                <Button><Plus />Add SKU</Button>
            </SkuFormDialog>
            <Button v-else disabled><Plus />Add SKU</Button>
        </CardHeader>
        <CardContent :class="disabled && 'pointer-events-none opacity-60'">
            <div v-if="disabled" class="border-border overflow-hidden rounded-xl border">
                <Table>
                    <TableHeader class="bg-muted/50">
                        <TableRow>
                            <TableHead>Code</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Price</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty :colspan="5"> Save the product to manage SKUs. </TableEmpty>
                    </TableBody>
                </Table>
            </div>

            <PaginatedTable
                v-else-if="skus && productId"
                :pagination="skus"
                :row-key="(sku) => sku.id"
                :column-count="5"
                @page-change="visitPage"
            >
                <template #header>
                    <TableHead class="px-4">Code</TableHead>
                    <TableHead class="px-4">Name</TableHead>
                    <TableHead class="px-4">Price</TableHead>
                    <TableHead class="px-4">Status</TableHead>
                    <TableHead class="w-28 px-4 text-right">Actions</TableHead>
                </template>

                <template #empty>No SKUs have been added yet.</template>

                <template #row="{ row: sku }">
                    <TableCell class="px-4 font-mono">{{ sku.code }}</TableCell>
                    <TableCell class="px-4">{{ sku.name }}</TableCell>
                    <TableCell class="px-4 tabular-nums">
                        {{ formatMoney(sku.price) }}
                    </TableCell>
                    <TableCell class="px-4">
                        <Badge :variant="sku.status === 'active' ? 'default' : 'secondary'">
                            {{ sku.status === "active" ? "Active" : "Inactive" }}
                        </Badge>
                    </TableCell>
                    <TableCell class="px-4">
                        <div class="flex justify-end gap-1">
                            <SkuFormDialog :product-id="productId" :sku="sku">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    :aria-label="`Edit ${sku.code}`"
                                >
                                    <Pencil />
                                </Button>
                            </SkuFormDialog>
                        </div>
                    </TableCell>
                </template>
            </PaginatedTable>
        </CardContent>
    </Card>
</template>
