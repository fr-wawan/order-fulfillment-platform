<script setup lang="ts">
import { Pencil, Plus } from '@lucide/vue';
import PaginatedTable from '@/components/PaginatedTable.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import InventoryFormDialog from '@/components/warehouses/InventoryFormDialog.vue';
import { usePaginatedNavigation } from '@/composables/usePaginatedNavigation';
import { formatNumber } from '@/lib/formatters';
import { edit } from '@/routes/warehouses';
import type { Inventory, InventorySku, PaginatedData } from '@/types';

const props = defineProps<{
    warehouseId?: number;
    inventories: PaginatedData<Inventory> | null;
    skuOptions: InventorySku[];
    assignedSkuIds: number[];
    disabled?: boolean;
}>();

const navigateToPage = usePaginatedNavigation(
    () => props.inventories?.current_page ?? 1,
    (page) => edit(props.warehouseId!, { query: { page } }),
);

function visitPage(page: number): void {
    if (props.warehouseId) {
        navigateToPage(page);
    }
}
</script>

<template>
    <Card :aria-disabled="disabled">
        <CardHeader class="grid-cols-[1fr_auto]">
            <div class="grid gap-1.5">
                <CardTitle>Inventory</CardTitle>
                <p class="text-muted-foreground text-sm">
                    {{
                        disabled
                            ? 'Save the warehouse before adding inventory.'
                            : 'Manage SKU quantities stored in this warehouse.'
                    }}
                </p>
            </div>
            <InventoryFormDialog
                v-if="warehouseId && !disabled"
                :warehouse-id="warehouseId"
                :sku-options="skuOptions"
                :assigned-sku-ids="assignedSkuIds"
            >
                <Button :disabled="skuOptions.length === assignedSkuIds.length">
                    <Plus />Add inventory
                </Button>
            </InventoryFormDialog>
            <Button v-else disabled><Plus />Add inventory</Button>
        </CardHeader>
        <CardContent :class="disabled && 'pointer-events-none opacity-60'">
            <div
                v-if="disabled"
                class="border-border overflow-hidden rounded-xl border"
            >
                <Table>
                    <TableHeader class="bg-muted/50">
                        <TableRow>
                            <TableHead>SKU</TableHead>
                            <TableHead>Product</TableHead>
                            <TableHead>Quantity</TableHead>
                            <TableHead>Reserved</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty :colspan="5">
                            Save the warehouse to manage inventory.
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>

            <PaginatedTable
                v-else-if="inventories && warehouseId"
                :pagination="inventories"
                :row-key="(inventory) => inventory.id"
                :column-count="5"
                @page-change="visitPage"
            >
                <template #header>
                    <TableHead class="px-4">SKU</TableHead>
                    <TableHead class="px-4">Product</TableHead>
                    <TableHead class="px-4 text-right">Quantity</TableHead>
                    <TableHead class="px-4 text-right">Reserved</TableHead>
                    <TableHead class="w-20 px-4 text-right">Actions</TableHead>
                </template>
                <template #empty>No inventory has been added yet.</template>
                <template #row="{ row: inventory }">
                    <TableCell class="px-4">
                        <p class="font-mono">{{ inventory.sku.code }}</p>
                        <p class="text-muted-foreground text-sm">
                            {{ inventory.sku.name }}
                        </p>
                    </TableCell>
                    <TableCell class="px-4">{{
                        inventory.sku.product.name
                    }}</TableCell>
                    <TableCell class="px-4 text-right font-medium tabular-nums">
                        {{ formatNumber(inventory.quantity) }}
                    </TableCell>
                    <TableCell class="px-4 text-right font-medium tabular-nums">
                        {{ formatNumber(inventory.reserved_quantity) }}
                    </TableCell>
                    <TableCell class="px-4">
                        <InventoryFormDialog
                            :warehouse-id="warehouseId"
                            :inventory="inventory"
                            :sku-options="skuOptions"
                            :assigned-sku-ids="assignedSkuIds"
                            trigger-tooltip="Edit inventory"
                        >
                            <Button
                                variant="ghost"
                                size="icon"
                                :aria-label="`Edit ${inventory.sku.code} inventory`"
                            >
                                <Pencil />
                            </Button>
                        </InventoryFormDialog>
                    </TableCell>
                </template>
            </PaginatedTable>
        </CardContent>
    </Card>
</template>
