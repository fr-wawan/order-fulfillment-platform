import type { Order } from '@/types';

export const orderStatusTone = {
    pending: 'warning',
    cancelled: 'danger',
    expired: 'danger',
    paid: 'success',
} as const satisfies Record<
    Order['status'],
    'success' | 'warning' | 'danger'
>;
