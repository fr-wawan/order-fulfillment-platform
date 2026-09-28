<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { PackageCheck } from '@lucide/vue';
import { fulfill } from '@/actions/App/Http/Controllers/OrderController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { Order } from '@/types';

defineProps<{
    order: Pick<Order, 'id' | 'order_number'>;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button>
                <PackageCheck />
                Fulfill order
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="fulfill.form(order.id)"
                v-slot="{ processing }"
                :options="{ preserveScroll: true }"
            >
                <DialogHeader>
                    <DialogTitle>Fulfill order?</DialogTitle>
                    <DialogDescription>
                        This will mark “{{ order.order_number }}” as fulfilled
                        and deduct its reserved inventory. This action cannot be
                        undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-6 gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="outline">
                            Keep order
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        <PackageCheck />
                        {{ processing ? 'Fulfilling…' : 'Fulfill order' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
