<script setup lang="ts">
import CrudIndexPage from '@/components/CrudIndexPage.vue';
import ProductTable from '@/components/products/ProductTable.vue';
import { create, index } from '@/routes/products';
import type { PaginatedData, Product } from '@/types';

defineProps<{
    products: PaginatedData<Product>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Products',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <CrudIndexPage
        title="Products"
        description="Manage the products in your catalog."
        :create-href="create()"
        create-label="Add product"
        empty-title="No products yet"
        empty-description="Create your first product to get started."
        :is-empty="products.data.length === 0"
    >
        <ProductTable :products="products" />
    </CrudIndexPage>
</template>
