import { Product } from '@/Tpes/entities';
import { BaseService } from './BaseService';

class ProductService extends BaseService {
    list(): Promise<Product[]> {
        return this.send('GET', 'products');
    }
}

export default new ProductService();
