import { Order } from '@/Types/entities';
import { PaginationResponse } from '@/Types/responses';
import { BaseService } from './BaseService';

/**
 * OrderService
 *
 * Service class for handling order-related operations.
 * Manages order retrieval, status updates, and order history.
 */
class OrderService extends BaseService {
  /**
   * Fetch all orders for the current authenticated user
   *
   * @returns {Promise<{orders: Order[]}>} Promise resolving to array of user's orders
   * @throws {Error} Throws error if user is not authenticated
   * ```
   */
  async fetchOrders(
    params: any,
  ): Promise<{ orders: PaginationResponse<Order> }> {
    return await this.send('GET', 'orders', params);
  }

  /**
   * Fetch a specific order by its ID
   *
   * @param {number} orderId - The unique identifier of the order
   * @returns {Promise<{order: Order}>} Promise resolving to order details with products
   * @throws {Error} Throws error if order not found or user unauthorized
   */
  async fetchOrder(orderId: number): Promise<{ order: Order }> {
    return await this.send('GET', `orders/${orderId}`);
  }

  /**
   * Update an order's details (typically status changes)
   *
   * @param {number} orderId - The unique identifier of the order
   * @param {Partial<Order>} data - Partial order data to update
   * @param {string} [data.status] - New order status (pending, processing, completed, cancelled)
   * @returns {Promise<void>} Promise that resolves when order is updated
   * @throws {Error} Throws error if order not found or update fails
   */
  async updateOrder(orderId: number, data: Partial<Order>): Promise<void> {
    return await this.send('PATCH', `orders/${orderId}`, data);
  }
}

export default new OrderService();
