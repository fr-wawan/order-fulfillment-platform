<script setup lang="ts">
import CrudIndexPage from '@/components/CrudIndexPage.vue';
import WarehouseTable from '@/components/warehouses/WarehouseTable.vue';
import { create, index } from '@/routes/warehouses';
import type { PaginatedData, Warehouse } from '@/types';

defineProps<{ warehouses: PaginatedData<Warehouse> }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Warehouses', href: index() }],
    },
});
</script>

<template>
    <CrudIndexPage
        title="Warehouses"
        description="Manage storage locations and their inventory."
        :create-href="create()"
        create-label="Add warehouse"
        empty-title="No warehouses yet"
        empty-description="Create your first warehouse to start tracking inventory."
        :is-empty="warehouses.data.length === 0"
    >
        <WarehouseTable :warehouses="warehouses" />
    </CrudIndexPage>
</template>
