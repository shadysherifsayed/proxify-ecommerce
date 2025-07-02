import { DataWrapper } from '@/Types/responses';
import { Cart, Order, Product } from '@/Types/entities';
import { BaseService } from './BaseService';

class CartService extends BaseService {

    fetchCart(): Promise<DataWrapper<Cart>> {
        return this.send('GET', '/carts');
    }

    addToCart(productId: number, quantity: number): Promise<Product> {
        return this.send('POST', `/carts/products/${productId}`, { quantity });
    }

    removeFromCart(productId: number): Promise<void> {
        return this.send('DELETE', `/carts/products/${productId}`);
    }

    clearCart(): Promise<void> {
        return this.send('DELETE', '/carts');
    }

    checkoutCart(): Promise<Order> {
        return this.send('POST', '/carts/checkout');
    }

}

export default new CartService();
