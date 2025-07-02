import { Product } from '@/Tpes/entities';
import { BaseService } from './BaseService';

class CartService extends BaseService {

    get(): Promise<Product[]> {
        return this.send('GET', '/carts');
    }

    addProduct(productId: string, quantity: number): Promise<Product> {
        return this.send('POST', `/carts/products/${productId}`, { quantity });
    }

    removeProduct(productId: string): Promise<void> {
        return this.send('DELETE', `/carts/products/${productId}`);
    }
}

export default new CartService();
