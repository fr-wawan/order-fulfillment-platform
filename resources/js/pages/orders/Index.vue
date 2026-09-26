<script setup lang="ts">
import CrudIndexPage from '@/components/CrudIndexPage.vue';
import OrderTable from '@/components/orders/OrderTable.vue';
import { create, index } from '@/routes/orders';
import type { Order, PaginatedData } from '@/types';

defineProps<{
    orders: PaginatedData<Order>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Orders',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <CrudIndexPage
        title="Orders"
        description="Review orders and create new ones."
        :create-href="create()"
        create-label="Create order"
        empty-title="No orders yet"
        empty-description="Create your first order to get started."
        :is-empty="orders.data.length === 0"
    >
        <OrderTable :orders="orders" />
    </CrudIndexPage>
</template>
