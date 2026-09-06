export type Order = {
    id: number;
    order_number: string;
    status: "pending" | "cancelled";
    total_amount: number;
    items_count: number;
    created_at: string;
};

export type OrderSkuOption = {
    id: number;
    code: string;
    name: string;
    price: number;
    status: "active" | "inactive";
    product: {
        id: number;
        name: string;
    };
};

export type OrderItemSnapshot = {
    id: number;
    sku_id: number;
    quantity: number;
    unit_price: number;
    sku: OrderSkuOption;
};

export type OrderDetail = Omit<Order, "items_count"> & {
    items: OrderItemSnapshot[];
};
