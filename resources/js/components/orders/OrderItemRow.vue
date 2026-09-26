<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatMoney } from '@/lib/formatters';
import type { OrderFormItem, OrderSkuOption } from '@/types';

const props = defineProps<{
    itemIndex: number;
    skuOptions: OrderSkuOption[];
    selectedSkuIds: string[];
    canRemove: boolean;
    skuError?: string;
    quantityError?: string;
}>();

const emit = defineEmits<{
    remove: [];
}>();

const item = defineModel<OrderFormItem>({ required: true });

const selectedSku = computed(() =>
    props.skuOptions.find((sku) => String(sku.id) === item.value.sku_id),
);

const lineTotal = computed(
    () =>
        (selectedSku.value?.price ?? 0) *
        Math.max(Number(item.value.quantity) || 0, 0),
);

function isSkuUnavailable(skuId: number): boolean {
    const value = String(skuId);

    return value !== item.value.sku_id && props.selectedSkuIds.includes(value);
}
</script>

<template>
    <div
        class="border-border bg-muted/20 grid gap-4 rounded-lg border p-4 md:grid-cols-[auto_minmax(0,1fr)_9rem_10rem_auto] md:items-start"
    >
        <div
            class="bg-primary text-primary-foreground flex size-7 items-center justify-center rounded-full text-xs font-semibold md:mt-6"
            :aria-label="`Item ${itemIndex + 1}`"
        >
            {{ itemIndex + 1 }}
        </div>

        <div class="grid gap-2">
            <Label :for="`item-${item.clientId}-sku`">SKU</Label>
            <Select v-model="item.sku_id">
                <SelectTrigger :id="`item-${item.clientId}-sku`" class="w-full">
                    <SelectValue placeholder="Select a SKU" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="sku in skuOptions"
                        :key="sku.id"
                        :value="String(sku.id)"
                        :disabled="isSkuUnavailable(sku.id)"
                    >
                        <span>{{ sku.code }} — {{ sku.name }}</span>
                        <span v-if="sku.status === 'inactive'">
                            (Inactive)</span
                        >
                    </SelectItem>
                </SelectContent>
            </Select>
            <div
                v-if="selectedSku"
                class="flex flex-wrap items-center gap-2 text-sm"
            >
                <span class="text-muted-foreground">
                    {{ selectedSku.product.name }} ·
                    {{ formatMoney(selectedSku.price) }}
                </span>
                <StatusBadge
                    :status="selectedSku.status"
                    :tone="
                        selectedSku.status === 'active' ? 'success' : 'neutral'
                    "
                />
            </div>
            <InputError :message="skuError" />
        </div>

        <div class="grid gap-2">
            <Label :for="`item-${item.clientId}-quantity`">Quantity</Label>
            <Input
                :id="`item-${item.clientId}-quantity`"
                v-model="item.quantity"
                type="number"
                min="1"
                step="1"
                :disabled="item.sku_id === ''"
                :tabindex="item.sku_id === '' ? -1 : 0"
                required
            />
            <InputError :message="quantityError" />
        </div>

        <div class="grid content-start gap-2">
            <Label>Line total</Label>
            <div class="flex h-9 items-center font-semibold tabular-nums">
                {{ formatMoney(lineTotal) }}
            </div>
        </div>

        <Button
            type="button"
            variant="ghost"
            size="icon"
            class="md:mt-6"
            :disabled="!canRemove"
            :title="
                canRemove ? 'Remove item' : 'An order needs at least one item'
            "
            :aria-label="`Remove item ${itemIndex + 1}`"
            @click="emit('remove')"
        >
            <Trash2 />
        </Button>
    </div>
</template>
