export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: string;
    name: string;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: string;
    title: string;
    description: string;
    image: string;
    price: number;
    reviews_count: number;
    rating: number;
    created_at: string;
    updated_at: string;
    category: Category;
}

export interface Cart {
    id: string;
    user_id: number;
    total_price: number;
    created_at: string;
    updated_at: string;
    products: Product[];
}

export interface Order {
    id: string;
    user_id: number;
    total_price: number;
    status: string;
    created_at: string;
    updated_at: string;
    products: Product[];
}
