import { PaginationCursorResponse } from '@/Types/responses';
import { BaseService } from './BaseService';
import { Product } from '@/Types/entities';

class ProductService extends BaseService {
    list(): Promise<PaginationCursorResponse<Product[]>> {
        return this.send('GET', 'products');
    }
}

export default new ProductService();
