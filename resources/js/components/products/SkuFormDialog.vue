<script setup lang="ts">
import { Form } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { store, update } from "@/actions/App/Http/Controllers/SkuController";
import InputError from "@/components/InputError.vue";
import MoneyInput from "@/components/MoneyInput.vue";
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
import type { Sku } from "@/types";

const props = defineProps<{
    productId: number;
    sku?: Sku;
}>();

const open = ref(false);
const isEditing = computed(() => props.sku !== undefined);
const formAction = computed(() =>
    props.sku
        ? update.form({ product: props.productId, sku: props.sku.id })
        : store.form(props.productId),
);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <slot />
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="formAction"
                v-slot="{ errors, processing }"
                reset-on-success
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? "Edit SKU" : "Add SKU" }}</DialogTitle>
                    <DialogDescription>
                        {{
                            isEditing
                                ? "Update this stock keeping unit."
                                : "Add a stock keeping unit to this product."
                        }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="sku-code">Code</Label>
                        <Input
                            id="sku-code"
                            name="code"
                            :default-value="sku?.code ?? ''"
                            placeholder="SKU-001"
                            required
                        />
                        <InputError :message="errors.code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="sku-name">Name</Label>
                        <Input
                            id="sku-name"
                            name="name"
                            :default-value="sku?.name ?? ''"
                            placeholder="Default variant"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="sku-price">Price</Label>
                        <MoneyInput
                            id="sku-price"
                            name="price"
                            :default-value="sku?.price ?? 0"
                            placeholder="0"
                            required
                        />
                        <InputError :message="errors.price" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="sku-status">Status</Label>
                        <Select name="status" :default-value="sku?.status ?? 'active'" required>
                            <SelectTrigger id="sku-status" class="w-full">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.status" />
                    </div>
                </div>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button type="button" variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? "Saving…" : "Save SKU" }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
