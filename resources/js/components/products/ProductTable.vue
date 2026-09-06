<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { Pencil } from "@lucide/vue";
import { edit } from "@/actions/App/Http/Controllers/ProductController";
import PaginatedTable from "@/components/PaginatedTable.vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { TableCell, TableHead } from "@/components/ui/table";
import { index } from "@/routes/products";
import type { PaginatedData, Product } from "@/types";

const props = defineProps<{
    products: PaginatedData<Product>;
}>();

function visitPage(page: number): void {
    if (page === props.products.current_page) {
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
                <Badge :variant="product.status === 'active' ? 'default' : 'secondary'">
                    {{ product.status === "active" ? "Active" : "Inactive" }}
                </Badge>
            </TableCell>
            <TableCell class="px-4 py-4">
                <div class="flex justify-end gap-1">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="edit(product.id)" :aria-label="`Edit ${product.name}`">
                            <Pencil />
                        </Link>
                    </Button>
                </div>
            </TableCell>
        </template>
    </PaginatedTable>
</template>
