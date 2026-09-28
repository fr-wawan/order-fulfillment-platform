export type Order = {
    id: number;
    order_number: string;
    status: 'pending' | 'cancelled' | 'expired' | 'paid' | 'fulfilled';
    total_amount: number;
    items_count: number;
    created_at: string;
};

export type OrderSku = {
    id: number;
    code: string;
    name: string;
    price: number;
    status: 'active' | 'inactive';
    product: {
        id: number;
        name: string;
    };
};

export type OrderSkuOption = OrderSku & {
    available_quantity: number;
};

export type OrderFormItem = {
    clientId: number;
    sku_id: string;
    quantity: number;
};

export type OrderItemSnapshot = {
    id: number;
    sku_id: number;
    quantity: number;
    unit_price: number;
    sku: OrderSku;
};

export type OrderDetail = Omit<Order, 'items_count'> & {
    items: OrderItemSnapshot[];
    payment: {
        status:
            | 'pending'
            | 'succeeded'
            | 'refund_queued'
            | 'refund_submitting'
            | 'refund_pending'
            | 'refund_failed'
            | 'refunded';
    } | null;
};
