<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { CreditCard, LoaderCircle } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { store } from '@/routes/orders/payments';
import type { Order } from '@/types';

defineProps<{
    order: Pick<Order, 'id'>;
}>();
</script>

<template>
    <Form v-bind="store.form(order.id)" v-slot="{ processing }">
        <Button type="submit" :disabled="processing">
            <LoaderCircle v-if="processing" class="animate-spin" />
            <CreditCard v-else />
            {{ processing ? 'Redirecting…' : 'Pay order' }}
        </Button>
    </Form>
</template>
