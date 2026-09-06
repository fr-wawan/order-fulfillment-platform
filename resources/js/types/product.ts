export type Product = {
    id: number;
    name: string;
    description: string | null;
    status: "active" | "inactive";
};

export type Sku = {
    id: number;
    code: string;
    name: string;
    price: number;
    status: "active" | "inactive";
};
