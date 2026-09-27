<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import { edit } from '@/actions/App/Http/Controllers/ProductController';
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
import { index } from '@/routes/products';
import type { PaginatedData, Product } from '@/types';

const props = defineProps<{
    products: PaginatedData<Product>;
}>();

const visitPage = usePaginatedNavigation(
    () => props.products.current_page,
    (page) => index({ query: { page } }),
);
</script>

<template>
    <PaginatedTable
        :pagination="products"
        :row-key="(product) => product.id"
        :column-count="3"
        @page-change="visitPage"
    >
        <template #header>
            <TableHead class="px-4">Product</TableHead>
            <TableHead class="px-4">Status</TableHead>
            <TableHead class="w-28 px-4 text-right">Actions</TableHead>
        </template>

        <template #empty>No products have been added yet.</template>

        <template #row="{ row: product }">
            <TableCell class="max-w-xl px-4 py-4 whitespace-normal">
                <p class="font-medium">{{ product.name }}</p>
                <p
                    v-if="product.description"
                    class="text-muted-foreground mt-1 line-clamp-2 text-sm"
                >
                    {{ product.description }}
                </p>
            </TableCell>
            <TableCell class="px-4 py-4">
                <StatusBadge
                    :status="product.status"
                    :tone="product.status === 'active' ? 'success' : 'neutral'"
                />
            </TableCell>
            <TableCell class="px-4 py-4">
                <div class="flex justify-end gap-1">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" as-child>
                                <Link
                                    :href="edit(product.id)"
                                    :aria-label="`Edit ${product.name}`"
                                >
                                    <Pencil />
                                </Link>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Edit product</TooltipContent>
                    </Tooltip>
                </div>
            </TableCell>
        </template>
    </PaginatedTable>
</template>
