<script setup lang="ts">
import { Form, Link, router } from "@inertiajs/vue3";
import { Pencil, Trash2 } from "@lucide/vue";
import { destroy, edit } from "@/actions/App/Http/Controllers/WarehouseController";
import PaginatedTable from "@/components/PaginatedTable.vue";
import { Badge } from "@/components/ui/badge";
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
import { TableCell, TableHead } from "@/components/ui/table";
import { index } from "@/routes/warehouses";
import type { PaginatedData, Warehouse } from "@/types";

const props = defineProps<{ warehouses: PaginatedData<Warehouse> }>();

function visitPage(page: number): void {
    if (page === props.warehouses.current_page) return;

    router.visit(index({ query: { page } }), {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <PaginatedTable
        :pagination="warehouses"
        :row-key="(warehouse) => warehouse.id"
        :column-count="4"
        @page-change="visitPage"
    >
        <template #header>
            <TableHead class="px-4">Code</TableHead>
            <TableHead class="px-4">Warehouse</TableHead>
            <TableHead class="px-4">Status</TableHead>
            <TableHead class="w-28 px-4 text-right">Actions</TableHead>
        </template>
        <template #empty>No warehouses have been added yet.</template>
        <template #row="{ row: warehouse }">
            <TableCell class="px-4 font-mono">{{ warehouse.code }}</TableCell>
            <TableCell class="px-4 font-medium">{{ warehouse.name }}</TableCell>
            <TableCell class="px-4">
                <Badge :variant="warehouse.status === 'active' ? 'default' : 'secondary'">
                    {{ warehouse.status === "active" ? "Active" : "Inactive" }}
                </Badge>
            </TableCell>
            <TableCell class="px-4">
                <div class="flex justify-end gap-1">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="edit(warehouse.id)" :aria-label="`Edit ${warehouse.name}`">
                            <Pencil />
                        </Link>
                    </Button>
                    <Dialog>
                        <DialogTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                :aria-label="`Delete ${warehouse.name}`"
                            >
                                <Trash2 />
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <Form
                                v-bind="destroy.form(warehouse.id)"
                                v-slot="{ processing }"
                                :options="{ preserveScroll: true }"
                            >
                                <DialogHeader>
                                    <DialogTitle>Delete warehouse?</DialogTitle>
                                    <DialogDescription>
                                        This will permanently delete “{{ warehouse.name }}” and its
                                        inventory records. This action cannot be undone.
                                    </DialogDescription>
                                </DialogHeader>
                                <DialogFooter class="mt-6 gap-2">
                                    <DialogClose as-child>
                                        <Button type="button" variant="outline">Cancel</Button>
                                    </DialogClose>
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        :disabled="processing"
                                    >
                                        {{ processing ? "Deleting…" : "Delete warehouse" }}
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>
            </TableCell>
        </template>
    </PaginatedTable>
</template>
