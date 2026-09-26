<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatMoney, formatNumber } from '@/lib/formatters';
import type { OrderSkuOption } from '@/types';

const props = defineProps<{
    id: string;
    skuOptions: OrderSkuOption[];
    selectedSkuIds: string[];
    error?: string;
}>();

const selectedSkuId = defineModel<string>({ required: true });

const selectedSku = computed(() =>
    props.skuOptions.find((sku) => String(sku.id) === selectedSkuId.value),
);

const selectedSkuLabel = computed(() =>
    selectedSku.value
        ? `${selectedSku.value.code} — ${selectedSku.value.name}`
        : 'Select a SKU',
);

function isUnavailable(sku: OrderSkuOption): boolean {
    const skuId = String(sku.id);

    return (
        sku.available_quantity < 1 ||
        (skuId !== selectedSkuId.value && props.selectedSkuIds.includes(skuId))
    );
}
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id">SKU</Label>
        <Select v-model="selectedSkuId">
            <SelectTrigger :id="id" class="w-full">
                <SelectValue placeholder="Select a SKU">
                    <span class="truncate">{{ selectedSkuLabel }}</span>
                </SelectValue>
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="sku in skuOptions"
                    :key="sku.id"
                    :value="String(sku.id)"
                    :disabled="isUnavailable(sku)"
                    class="py-2.5"
                >
                    <span class="flex min-w-0 flex-col items-start gap-0.5">
                        <span class="max-w-full truncate font-medium">
                            {{ sku.code }} — {{ sku.name }}
                        </span>
                        <span
                            class="text-muted-foreground max-w-full truncate text-xs font-normal"
                        >
                            {{ sku.product.name }} · Stock:
                            {{ formatNumber(sku.available_quantity) }}
                            <template v-if="sku.status === 'inactive'">
                                · Inactive
                            </template>
                        </span>
                    </span>
                </SelectItem>
            </SelectContent>
        </Select>
        <div
            v-if="selectedSku"
            class="flex flex-wrap items-center gap-2 text-sm"
        >
            <span class="text-muted-foreground">
                {{ selectedSku.product.name }} ·
                {{ formatMoney(selectedSku.price) }} ·
                {{ formatNumber(selectedSku.available_quantity) }} available
            </span>
            <StatusBadge
                :status="selectedSku.status"
                :tone="selectedSku.status === 'active' ? 'success' : 'neutral'"
            />
        </div>
        <InputError :message="error" />
    </div>
</template>
