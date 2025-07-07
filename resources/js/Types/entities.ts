export interface User {
  id: number;
  name: string;
  email: string;
  token?: string;
  avatar?: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface Category {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

export interface Product {
  id: number;
  title: string;
  description: string;
  image: string;
  price: number;
  reviews_count: number;
  rating: number;
  category_id: number;
  created_at: string;
  updated_at: string;
  category: Category;
}

export type CartProduct = Product & { pivot: { quantity: number } };

export interface Cart {
  id: number;
  user_id: number;
  total_price: number;
  created_at: string;
  updated_at: string;
  products: CartProduct[];
}

export type OrderProduct = Product & {
  pivot: { quantity: number; price: number };
};

export interface Order {
  id: number;
  user_id: number;
  total_price: number;
  status: string;
  created_at: string;
  updated_at: string;
  products: OrderProduct[];
}

export interface ProductFilters {
  search: string;
  categories: number[];
  min_price: number | null;
  max_price: number | null;
  min_rating: number;
}

export interface OrderFilters {
  status: string | null;
  min_price: number | null;
  max_price: number | null;
  date_from: string | null;
  date_to: string | null;
}

export interface Sort {
  field: string;
  direction: 'asc' | 'desc';
}
