<script setup lang="ts" generic="T">
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from "@/components/ui/pagination";
import {
    Table,
    TableBody,
    TableEmpty,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import type { PaginatedData } from "@/types";

defineProps<{
    pagination: PaginatedData<T>;
    rowKey: (row: T) => string | number;
    columnCount: number;
}>();

const emit = defineEmits<{
    pageChange: [page: number];
}>();

defineSlots<{
    header(): unknown;
    row(props: { row: T }): unknown;
    empty(): unknown;
}>();
</script>

<template>
    <div class="border-border overflow-hidden rounded-xl border">
        <Table>
            <TableHeader class="bg-muted/50">
                <TableRow>
                    <slot name="header" />
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableEmpty
                    v-if="pagination.data.length === 0"
                    :colspan="columnCount"
                >
                    <slot name="empty" />
                </TableEmpty>
                <TableRow v-for="row in pagination.data" :key="rowKey(row)">
                    <slot name="row" :row="row" />
                </TableRow>
            </TableBody>
        </Table>

        <div
            class="border-t px-4 py-3 sm:flex sm:items-center sm:justify-between"
        >
            <p class="text-muted-foreground text-sm">
                Showing {{ pagination.from }}–{{ pagination.to }} of
                {{ pagination.total }}
            </p>
            <Pagination
                v-slot="{ page }"
                :page="pagination.current_page"
                :items-per-page="pagination.per_page"
                :total="pagination.total"
                :sibling-count="1"
                show-edges
                class="mt-3 w-auto sm:mx-0 sm:mt-0"
                @update:page="emit('pageChange', $event)"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious aria-label="Go to previous page" />
                    <template
                        v-for="(item, itemIndex) in items"
                        :key="itemIndex"
                    >
                        <PaginationItem
                            v-if="item.type === 'page'"
                            :value="item.value"
                            :is-active="item.value === page"
                        >
                            {{ item.value }}
                        </PaginationItem>
                        <PaginationEllipsis v-else :index="itemIndex" />
                    </template>
                    <PaginationNext aria-label="Go to next page" />
                </PaginationContent>
            </Pagination>
        </div>
    </div>
</template>
