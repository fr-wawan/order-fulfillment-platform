<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { store, update } from "@/actions/App/Http/Controllers/InventoryController";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import type { Inventory, InventorySku } from "@/types";

const props = defineProps<{
    warehouseId: number;
    inventory?: Inventory;
    skuOptions: InventorySku[];
    assignedSkuIds: number[];
}>();

const open = ref(false);
const isEditing = computed(() => props.inventory !== undefined);
const formAction = computed(() =>
    props.inventory
        ? update.form({ warehouse: props.warehouseId, inventory: props.inventory.id })
        : store.form(props.warehouseId),
);

function isSkuUnavailable(skuId: number): boolean {
    return skuId !== props.inventory?.sku_id && props.assignedSkuIds.includes(skuId);
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child><slot /></DialogTrigger>
        <DialogContent>
            <Form
                v-bind="formAction"
                v-slot="{ errors, processing }"
                reset-on-success
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>
                        {{ isEditing ? "Edit inventory" : "Add inventory" }}
                    </DialogTitle>
                    <DialogDescription>
                        Assign one unique SKU and its on-hand quantity to this warehouse.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-6">
                    <div v-if="!isEditing" class="grid gap-2">
                        <Label for="inventory-sku">SKU</Label>
                        <Select
                            name="sku_id"
                            :default-value="inventory?.sku_id.toString()"
                            required
                        >
                            <SelectTrigger id="inventory-sku" class="w-full">
                                <SelectValue placeholder="Select SKU" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="sku in skuOptions"
                                    :key="sku.id"
                                    :value="sku.id.toString()"
                                    :disabled="isSkuUnavailable(sku.id)"
                                >
                                    {{ sku.code }} — {{ sku.product.name }} / {{ sku.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.sku_id" />
                    </div>

                    <div v-else class="grid gap-2">
                        <Label>SKU</Label>
                        <div class="bg-muted rounded-md border px-3 py-2 text-sm">
                            <span class="font-mono">{{ inventory?.sku.code }}</span>
                            <span class="text-muted-foreground">
                                — {{ inventory?.sku.product.name }} / {{ inventory?.sku.name }}
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="inventory-quantity">Quantity</Label>
                        <Input
                            id="inventory-quantity"
                            name="quantity"
                            type="number"
                            min="0"
                            step="1"
                            :default-value="inventory?.quantity ?? 0"
                            required
                        />
                        <InputError :message="errors.quantity" />
                    </div>
                </div>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button type="button" variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing || skuOptions.length === 0">
                        {{ processing ? "Saving…" : "Save inventory" }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
