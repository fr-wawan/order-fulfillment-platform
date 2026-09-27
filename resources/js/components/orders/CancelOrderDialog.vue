<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Ban } from '@lucide/vue';
import { cancel } from '@/actions/App/Http/Controllers/OrderController';
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
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { Order } from '@/types';

defineProps<{
    order: Pick<Order, 'id' | 'order_number'>;
}>();
</script>

<template>
    <Dialog>
        <TooltipProvider :delay-duration="0">
            <Tooltip>
                <TooltipTrigger as-child>
                    <DialogTrigger as-child>
                        <slot>
                            <Button variant="destructive">
                                <Ban />
                                Cancel order
                            </Button>
                        </slot>
                    </DialogTrigger>
                </TooltipTrigger>
                <TooltipContent>Cancel order</TooltipContent>
            </Tooltip>
        </TooltipProvider>
        <DialogContent>
            <Form
                v-bind="cancel.form(order.id)"
                v-slot="{ processing }"
                :options="{ preserveScroll: true }"
            >
                <DialogHeader>
                    <DialogTitle>Cancel order?</DialogTitle>
                    <DialogDescription>
                        This will cancel “{{ order.order_number }}” and release
                        its reserved inventory. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-6 gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="outline">
                            Keep order
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        {{ processing ? 'Cancelling…' : 'Cancel order' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
