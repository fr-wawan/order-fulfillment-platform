<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Plus, Trash2 } from "@lucide/vue";
import { computed } from "vue";
import { store } from "@/actions/App/Http/Controllers/OrderController";
import Heading from "@/components/Heading.vue";
import InputError from "@/components/InputError.vue";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { formatMoney } from "@/lib/formatters";
import { index } from "@/routes/orders";
import type { OrderSkuOption } from "@/types";

const props = defineProps<{
    skuOptions: OrderSkuOption[];
}>();

type OrderLine = {
    sku_id: string;
    quantity: number;
};

const form = useForm<{ items: OrderLine[] }>({
    items: [{ sku_id: "", quantity: 1 }],
});

const skuById = computed(() => new Map(props.skuOptions.map((sku) => [String(sku.id), sku])));

const totalAmount = computed(() =>
    form.items.reduce((total, item) => {
        const sku = skuById.value.get(item.sku_id);

        return total + (sku?.price ?? 0) * Math.max(Number(item.quantity) || 0, 0);
    }, 0),
);

const canAddItem = computed(() => form.items.length < props.skuOptions.length);

const selectedItemCount = computed(() => form.items.filter((item) => item.sku_id !== "").length);

function addItem(): void {
    form.items.push({ sku_id: "", quantity: 1 });
}

function removeItem(indexToRemove: number): void {
    if (form.items.length === 1) {
        return;
    }

    form.items.splice(indexToRemove, 1);
}

function isSkuSelectedByAnotherItem(skuId: number, currentItemIndex: number): boolean {
    return form.items.some(
        (item, itemIndex) => itemIndex !== currentItemIndex && item.sku_id === String(skuId),
    );
}

function getLineTotal(item: OrderLine): number {
    const sku = skuById.value.get(item.sku_id);

    return (sku?.price ?? 0) * Math.max(Number(item.quantity) || 0, 0);
}

function submit(): void {
    form.submit(store());
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Orders",
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
                    <div
                        v-if="skuOptions.length === 0"
                        class="border-border rounded-lg border border-dashed px-6 py-10 text-center"
                    >
                        <p class="font-medium">No SKUs available</p>
                        <p class="text-muted-foreground mt-1 text-sm">
                            Add a SKU to a product before creating an order.
                        </p>
                    </div>

                    <div
                        v-for="(item, itemIndex) in form.items"
                        v-else
                        :key="itemIndex"
                        class="border-border bg-muted/20 grid gap-4 rounded-lg border p-4 md:grid-cols-[auto_minmax(0,1fr)_9rem_10rem_auto] md:items-start"
                    >
                        <div
                            class="bg-primary text-primary-foreground flex size-7 items-center justify-center rounded-full text-xs font-semibold md:mt-6"
                            :aria-label="`Item ${itemIndex + 1}`"
                        >
                            {{ itemIndex + 1 }}
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`item-${itemIndex}-sku`">SKU</Label>
                            <Select v-model="item.sku_id">
                                <SelectTrigger :id="`item-${itemIndex}-sku`" class="w-full">
                                    <SelectValue placeholder="Select a SKU" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="sku in skuOptions"
                                        :key="sku.id"
                                        :value="String(sku.id)"
                                        :disabled="isSkuSelectedByAnotherItem(sku.id, itemIndex)"
                                    >
                                        <span>{{ sku.code }} — {{ sku.name }}</span>
                                        <span v-if="sku.status === 'inactive'"> (Inactive)</span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div
                                v-if="skuById.get(item.sku_id)"
                                class="flex flex-wrap items-center gap-2 text-sm"
                            >
                                <span class="text-muted-foreground">
                                    {{ skuById.get(item.sku_id)?.product.name }} ·
                                    {{ formatMoney(skuById.get(item.sku_id)?.price ?? 0) }}
                                </span>
                                <Badge
                                    :variant="
                                        skuById.get(item.sku_id)?.status === 'active'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        skuById.get(item.sku_id)?.status === "active"
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </Badge>
                            </div>
                            <InputError :message="form.errors[`items.${itemIndex}.sku_id`]" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`item-${itemIndex}-quantity`">Quantity</Label>
                            <Input
                                :id="`item-${itemIndex}-quantity`"
                                v-model="item.quantity"
                                type="number"
                                min="1"
                                step="1"
                                :disabled="item.sku_id === ''"
                                :tabindex="item.sku_id === '' ? -1 : 0"
                                required
                            />
                            <InputError :message="form.errors[`items.${itemIndex}.quantity`]" />
                        </div>

                        <div class="grid content-start gap-2">
                            <Label>Line total</Label>
                            <div class="flex h-9 items-center font-semibold tabular-nums">
                                {{ formatMoney(getLineTotal(item)) }}
                            </div>
                        </div>

                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="md:mt-6"
                            :disabled="form.items.length === 1"
                            :title="
                                form.items.length === 1
                                    ? 'An order needs at least one item'
                                    : 'Remove item'
                            "
                            :aria-label="`Remove item ${itemIndex + 1}`"
                            @click="removeItem(itemIndex)"
                        >
                            <Trash2 />
                        </Button>
                    </div>

                    <InputError :message="form.errors.items" />
                </CardContent>
            </Card>

            <Card class="border-primary/20 bg-primary/5">
                <CardContent
                    class="flex flex-col gap-5 pt-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-6">
                        <div>
                            <p class="text-muted-foreground text-sm">Selected items</p>
                            <p class="text-lg font-semibold">{{ selectedItemCount }}</p>
                        </div>
                        <div class="border-border border-l pl-6">
                            <p class="text-muted-foreground text-sm">Order total</p>
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
                            :disabled="form.processing || skuOptions.length === 0"
                        >
                            {{ form.processing ? "Creating…" : "Create order" }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    </div>
</template>
