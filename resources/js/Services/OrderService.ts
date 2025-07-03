import { Order } from '@/Types/entities';
import { BaseService } from './BaseService';

class OrderService extends BaseService {
  /**
   * Fetch all orders for the current user
   */
  async fetchOrders(): Promise<{ orders: Order[] }> {
    return await this.send('GET', 'orders');
  }

  /**
   * Fetch a specific order by ID
   */
  async fetchOrder(orderId: number): Promise<{ order: Order }> {
    return await this.send('GET', `orders/${orderId}`);
  }

  /**
   * Update order status
   */
  async updateOrder(orderId: number, data: Partial<Order>): Promise<void> {
    return await this.send('PATCH', `orders/${orderId}`, data);
  }
}

export default new OrderService();
