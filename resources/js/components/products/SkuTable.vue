<script setup lang="ts">
import { Form, router } from "@inertiajs/vue3";
import { Pencil, Plus, Trash2 } from "@lucide/vue";
import { destroy } from "@/actions/App/Http/Controllers/SkuController";
import PaginatedTable from "@/components/PaginatedTable.vue";
import SkuFormDialog from "@/components/products/SkuFormDialog.vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { edit } from "@/routes/products";
import type { PaginatedData, Sku } from "@/types";

const props = defineProps<{
    productId?: number;
    skus: PaginatedData<Sku> | null;
    disabled?: boolean;
}>();

function formatPrice(price: number): string {
    return new Intl.NumberFormat().format(price);
}

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
                        {{ formatPrice(sku.price) }}
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

                            <Dialog>
                                <DialogTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        :aria-label="`Delete ${sku.code}`"
                                    >
                                        <Trash2 />
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <Form
                                        v-bind="
                                            destroy.form({
                                                product: productId,
                                                sku: sku.id,
                                            })
                                        "
                                        v-slot="{ processing }"
                                        :options="{ preserveScroll: true }"
                                    >
                                        <DialogHeader>
                                            <DialogTitle>Delete SKU?</DialogTitle>
                                            <DialogDescription>
                                                This will permanently delete “{{ sku.code }}”. This
                                                action cannot be undone.
                                            </DialogDescription>
                                        </DialogHeader>
                                        <DialogFooter class="mt-6 gap-2">
                                            <DialogClose as-child>
                                                <Button type="button" variant="outline">
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                :disabled="processing"
                                            >
                                                {{ processing ? "Deleting…" : "Delete SKU" }}
                                            </Button>
                                        </DialogFooter>
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </TableCell>
                </template>
            </PaginatedTable>
        </CardContent>
    </Card>
</template>
