<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed } from 'vue';
import { store } from '@/actions/App/Http/Controllers/OrderController';
import AlertError from '@/components/AlertError.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import OrderItemRow from '@/components/orders/OrderItemRow.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatMoney } from '@/lib/formatters';
import { index } from '@/routes/orders';
import type { OrderFormItem, OrderSkuOption } from '@/types';

const props = defineProps<{
    skuOptions: OrderSkuOption[];
}>();

let nextClientId = 1;

function createOrderItem(): OrderFormItem {
    return {
        clientId: nextClientId++,
        sku_id: '',
        quantity: 1,
    };
}

const form = useForm<{ items: OrderFormItem[] }>({
    items: [createOrderItem()],
});

const skuById = computed(
    () => new Map(props.skuOptions.map((sku) => [String(sku.id), sku])),
);

const totalAmount = computed(() =>
    form.items.reduce((total, item) => {
        const sku = skuById.value.get(item.sku_id);

        return (
            total + (sku?.price ?? 0) * Math.max(Number(item.quantity) || 0, 0)
        );
    }, 0),
);

const canAddItem = computed(() => form.items.length < props.skuOptions.length);

const selectedItemCount = computed(
    () => form.items.filter((item) => item.sku_id !== '').length,
);

const selectedSkuIds = computed(() =>
    form.items.map((item) => item.sku_id).filter((skuId) => skuId !== ''),
);

function addItem(): void {
    form.items.push(createOrderItem());
}

function removeItem(indexToRemove: number): void {
    if (form.items.length === 1) {
        return;
    }

    form.items.splice(indexToRemove, 1);
}

function submit(): void {
    form.transform((data) => ({
        items: data.items.map((item) => ({
            sku_id: item.sku_id,
            quantity: item.quantity,
        })),
    })).submit(store());
}

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
    <Head title="Create order" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <Heading
            title="Create order"
            description="Add one or more SKUs and set the quantity for each item."
        />

        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <Card>
                <CardHeader
                    class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <div>
                        <CardTitle>Order items</CardTitle>
                        <p class="text-muted-foreground mt-1 text-sm">
                            Inactive SKUs remain available and are marked below.
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full sm:w-auto"
                        :disabled="!canAddItem"
                        @click="addItem"
                    >
                        <Plus />
                        Add item
                    </Button>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <AlertError
                        v-if="form.errors.items"
                        :errors="[form.errors.items]"
                        title="Unable to create order."
                    />

                    <div
                        v-if="skuOptions.length === 0"
                        class="border-border rounded-lg border border-dashed px-6 py-10 text-center"
                    >
                        <p class="font-medium">No SKUs available</p>
                        <p class="text-muted-foreground mt-1 text-sm">
                            Add a SKU to a product before creating an order.
                        </p>
                    </div>

                    <OrderItemRow
                        v-for="(item, itemIndex) in form.items"
                        v-else
                        :key="item.clientId"
                        v-model="form.items[itemIndex]"
                        :item-index="itemIndex"
                        :sku-options="skuOptions"
                        :selected-sku-ids="selectedSkuIds"
                        :can-remove="form.items.length > 1"
                        :sku-error="form.errors[`items.${itemIndex}.sku_id`]"
                        :quantity-error="
                            form.errors[`items.${itemIndex}.quantity`]
                        "
                        @remove="removeItem(itemIndex)"
                    />
                </CardContent>
            </Card>

            <Card class="border-primary/20 bg-primary/5">
                <CardContent
                    class="flex flex-col gap-5 pt-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-6">
                        <div>
                            <p class="text-muted-foreground text-sm">
                                Selected items
                            </p>
                            <p class="text-lg font-semibold">
                                {{ selectedItemCount }}
                            </p>
                        </div>
                        <div class="border-border border-l pl-6">
                            <p class="text-muted-foreground text-sm">
                                Order total
                            </p>
                            <p class="text-2xl font-semibold">
                                {{ formatMoney(totalAmount) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex w-full justify-end gap-3 sm:w-auto">
                        <Button variant="outline" as-child>
                            <Link :href="index()">Cancel</Link>
                        </Button>
                        <Button
                            type="submit"
                            class="flex-1 sm:flex-none"
                            :disabled="
                                form.processing || skuOptions.length === 0
                            "
                        >
                            {{ form.processing ? 'Creating…' : 'Create order' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    </div>
</template>
