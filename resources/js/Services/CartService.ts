import { Cart } from '@/Types/entities';
import { BaseService } from './BaseService';

/**
 * CartService
 *
 * Service class for handling shopping cart operations.
 * Manages cart items, quantities, and checkout functionality.
 */
class CartService extends BaseService {
  /**
   * Fetch the current user's shopping cart
   *
   * @returns {Promise<{cart: Cart}>} Promise resolving to cart with products and totals
   * @throws {Error} Throws error if user is not authenticated
   * ```
   */
  fetchCart(): Promise<{ cart: Cart }> {
    return this.send('GET', '/carts');
  }

  /**
   * Add a product to the cart or update its quantity
   *
   * @param {number} productId - The unique identifier of the product
   * @param {number} quantity - The quantity to set for this product
   * @returns {Promise<void>} Promise that resolves when product is added
   * @throws {Error} Throws error if product not found or quantity invalid
   */
  addToCart(productId: number, quantity: number): Promise<void> {
    return this.send('POST', `/carts/products/${productId}`, { quantity });
  }

  /**
   * Remove a product completely from the cart
   *
   * @param {number} productId - The unique identifier of the product to remove
   * @returns {Promise<void>} Promise that resolves when product is removed
   * @throws {Error} Throws error if product not found in cart
   */
  removeFromCart(productId: number): Promise<void> {
    return this.send('DELETE', `/carts/products/${productId}`);
  }

  /**
   * Clear all items from the cart
   *
   * @returns {Promise<void>} Promise that resolves when cart is cleared
   */
  clearCart(): Promise<void> {
    return this.send('DELETE', '/carts');
  }

  /**
   * Process cart checkout and create an order
   *
   * @returns {Promise<void>} Promise that resolves when checkout is complete
   * @throws {Error} Throws error if cart is empty or payment fails
   */
  checkoutCart(): Promise<void> {
    return this.send('POST', '/carts/checkout');
  }
}

export default new CartService();
