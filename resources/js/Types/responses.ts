import { Product } from "./entities";


export interface Pagination {
    next: string | null;
    prev: string | null;
}

export interface DataWrapper<T> {
  data: T
}

export interface PaginationCursorResponse<T> {
    data: T;
    next_cursor: string | null;
    prev_cursor: string | null;
}

export interface ProductListResponse {
    data: Product[]
    next_cursor: string | null;
    prev_cursor: string | null;
}