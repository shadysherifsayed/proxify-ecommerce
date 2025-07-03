import { ProductListResponse } from '@/Types/responses';
import { BaseService } from './BaseService';

class ProductService extends BaseService {
  fetchProducts(): Promise<ProductListResponse> {
    return this.send('GET', 'products');
  }
}

export default new ProductService();
