<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import OrderSkuSelect from '@/components/orders/OrderSkuSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
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

        <OrderSkuSelect
            :id="`item-${item.clientId}-sku`"
            v-model="item.sku_id"
            :sku-options="skuOptions"
            :selected-sku-ids="selectedSkuIds"
            :error="skuError"
        />

        <div class="grid gap-2">
            <Label :for="`item-${item.clientId}-quantity`">Quantity</Label>
            <Input
                :id="`item-${item.clientId}-quantity`"
                v-model="item.quantity"
                type="number"
                min="1"
                :max="selectedSku?.available_quantity"
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

        <Tooltip>
            <TooltipTrigger as-child>
                <span class="md:mt-6">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        :disabled="!canRemove"
                        :aria-label="`Remove item ${itemIndex + 1}`"
                        @click="emit('remove')"
                    >
                        <Trash2 />
                    </Button>
                </span>
            </TooltipTrigger>
            <TooltipContent>
                {{
                    canRemove
                        ? 'Remove item'
                        : 'An order needs at least one item'
                }}
            </TooltipContent>
        </Tooltip>
    </div>
</template>
