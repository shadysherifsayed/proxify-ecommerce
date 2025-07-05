import { Product, User } from './entities';

export interface ListMeta {
  is_fetched: boolean;
  pagination: Pagination | null;
}

export interface Pagination {
  next: string | null;
  prev: string | null;
}

export interface DataWrapper<T> {
  data: T;
}

export interface PaginationCursorResponse<T> {
  data: T;
  prev_cursor: string | null;
  next_cursor: string | null;
}

export interface ProductListResponse {
  products: PaginationCursorResponse<Product[]>;
}

export interface AuthenticatedResponse {
  user: User;
  token: string;
}
