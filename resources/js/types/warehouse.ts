export type Warehouse = {
    id: number;
    code: string;
    name: string;
    status: "active" | "inactive";
};

export type InventorySku = {
    id: number;
    code: string;
    name: string;
    status: "active" | "inactive";
    product: {
        id: number;
        name: string;
    };
};

export type Inventory = {
    id: number;
    sku_id: number;
    quantity: number;
    sku: InventorySku;
};
