<script setup lang="ts">
import { Form, Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { store, update } from "@/actions/App/Http/Controllers/WarehouseController";
import Heading from "@/components/Heading.vue";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import InventoryTable from "@/components/warehouses/InventoryTable.vue";
import { index } from "@/routes/warehouses";
import type { Inventory, InventorySku, PaginatedData, Warehouse } from "@/types";

const { warehouse } = defineProps<{
    warehouse: Warehouse | null;
    inventories: PaginatedData<Inventory> | null;
    skuOptions: InventorySku[];
    assignedSkuIds: number[];
}>();

const isEditing = computed(() => warehouse !== null);
const title = computed(() => (isEditing.value ? "Edit warehouse" : "Create warehouse"));
const description = computed(() =>
    isEditing.value
        ? "Update the warehouse details below."
        : "Add a storage location for your inventory.",
);
const formAction = computed(() => (warehouse ? update.form(warehouse.id) : store.form()));

defineOptions({
    layout: {
        breadcrumbs: [{ title: "Warehouses", href: index() }],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading :title="title" :description="description" />

        <Card class="w-full max-w-3xl">
            <CardContent class="pt-6">
                <Form
                    v-bind="formAction"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-6"
                >
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="code">Code</Label>
                            <Input
                                id="code"
                                name="code"
                                :default-value="warehouse?.code ?? ''"
                                placeholder="WH-001"
                                required
                                autofocus
                            />
                            <InputError :message="errors.code" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                name="name"
                                :default-value="warehouse?.name ?? ''"
                                placeholder="Main warehouse"
                                required
                            />
                            <InputError :message="errors.name" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <Select
                            name="status"
                            :default-value="warehouse?.status ?? 'active'"
                            required
                        >
                            <SelectTrigger id="status" class="w-full">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button variant="outline" as-child>
                            <Link :href="index()">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? "Saving…" : "Save warehouse" }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>

        <InventoryTable
            :warehouse-id="warehouse?.id"
            :inventories="inventories"
            :sku-options="skuOptions"
            :assigned-sku-ids="assignedSkuIds"
            :disabled="!isEditing"
        />
    </div>
</template>
