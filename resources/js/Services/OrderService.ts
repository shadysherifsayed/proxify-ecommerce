import { Order } from '@/Types/entities';
import { BaseService } from './BaseService';

class OrderService extends BaseService {
  /**
   * Fetch all orders for the current user
   */
  async fetchOrders(): Promise<{ data: Order[] }> {
    return await this.send('GET', 'orders');
  }

  /**
   * Fetch a specific order by ID
   */
  async fetchOrder(orderId: number): Promise<{ data: Order }> {
    return await this.send('GET', `orders/${orderId}`);
  }

  /**
   * Create a new order from cart
   */
  async createOrder(): Promise<{ data: Order }> {
    return await this.send('POST', 'orders');
  }

  /**
   * Update order status
   */
  async updateOrderStatus(orderId: number, status: string): Promise<{ data: Order }> {
    return await this.send('PATCH', `orders/${orderId}`, { status });
  }
}

export default new OrderService();
