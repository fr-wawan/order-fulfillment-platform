<script setup lang="ts">
import { Form, Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    store,
    update,
} from "@/actions/App/Http/Controllers/ProductController";
import Heading from "@/components/Heading.vue";
import InputError from "@/components/InputError.vue";
import SkuTable from "@/components/products/SkuTable.vue";
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
import { index } from "@/routes/products";
import type { PaginatedData, Product, Sku } from "@/types";

const { product } = defineProps<{
    product: Product | null;
    skus: PaginatedData<Sku> | null;
}>();

const isEditing = computed(() => product !== null);
const title = computed(() =>
    isEditing.value ? "Edit product" : "Create product",
);
const description = computed(() =>
    isEditing.value
        ? "Update the product details below."
        : "Add a new product to your catalog.",
);
const formAction = computed(() =>
    product ? update.form(product.id) : store.form(),
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Products",
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-6">
        <Heading :title="title" :description="description" />

        <Card>
            <CardContent class="pt-6">
                <Form
                    v-bind="formAction"
                    class="flex flex-col gap-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="product?.name ?? ''"
                            placeholder="Product name"
                            required
                            autofocus
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            :value="product?.description ?? ''"
                            rows="5"
                            placeholder="Optional product description"
                            class="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <Select
                            name="status"
                            :default-value="product?.status ?? 'active'"
                            required
                        >
                            <SelectTrigger id="status" class="w-full">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive"
                                    >Inactive</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button variant="outline" as-child>
                            <Link :href="index()">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? "Saving…" : "Save product" }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>

        <SkuTable
            :product-id="product?.id"
            :skus="skus"
            :disabled="!isEditing"
        />
    </div>
</template>
