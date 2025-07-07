import { Product, User } from './entities';

export interface CursorPagination {
  next: string | null;
  prev: string | null;
}

export interface Pagination {
  to: number;
  from: number;
  total: number;
  per_page: number;
  last_page: number;
  current_page: number;
}

export interface SimplePagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
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

export interface PaginationResponse<T> {
  data: T[];
  to: number;
  from: number;
  total: number;
  per_page: number;
  last_page: number;
  current_page: number;
}

export interface ProductListResponse {
  products: PaginationCursorResponse<Product[]>;
}

export interface AuthenticatedResponse {
  user: User;
  token: string;
}
