<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { Plus } from "@lucide/vue";
import Heading from "@/components/Heading.vue";
import ProductTable from "@/components/products/ProductTable.vue";
import { Button } from "@/components/ui/button";
import { create, index } from "@/routes/products";
import type { PaginatedData, Product } from "@/types";

defineProps<{
    products: PaginatedData<Product>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Products",
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Products" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                title="Products"
                description="Manage the products in your catalog."
            />
            <Button as-child>
                <Link :href="create()">
                    <Plus />
                    Add product
                </Link>
            </Button>
        </div>

        <div
            v-if="products.data.length === 0"
            class="border-border rounded-xl border px-6 py-16 text-center"
        >
            <p class="font-medium">No products yet</p>
            <p class="text-muted-foreground mt-1 text-sm">
                Create your first product to get started.
            </p>
            <Button class="mt-5" as-child>
                <Link :href="create()">
                    <Plus />
                    Add product
                </Link>
            </Button>
        </div>

        <ProductTable v-else :products="products" />
    </div>
</template>
