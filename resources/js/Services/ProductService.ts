import { Product } from '@/Types/entities';
import { ProductListResponse } from '@/Types/responses';
import { BaseService } from './BaseService';

/**
 * ProductService
 *
 * Service class for handling product-related API operations.
 * Extends BaseService to inherit common HTTP functionality.
 */
class ProductService extends BaseService {
  /**
   * Fetch a paginated list of products
   *
   * @param {object} params - Query parameters for filtering and pagination
   * @param {string} [params.cursor] - Cursor for pagination
   * @returns {Promise<ProductListResponse>} Promise resolving to paginated product list
   */
  fetchProducts(params: any = {}): Promise<ProductListResponse> {
    return this.send('GET', 'products', params);
  }

  /**
   * Fetch a specific product by ID
   *
   * @param {number} productId - The unique identifier of the product
   * @returns {Promise<{product: Product}>} Promise resolving to product details
   * @throws {Error} Throws error if product not found or request fails
   */
  async fetchProduct(productId: number): Promise<{ product: Product }> {
    return await this.send('GET', `products/${productId}`);
  }

  /**
   * Update product details
   *
   * @param {number} productId - The unique identifier of the product to update
   * @param {Partial<Product>} data - Partial product data to update
   * @param {string} [data.title] - Product title
   * @param {string} [data.description] - Product description
   * @param {number} [data.price] - Product price
   * @param {number} [data.category_id] - Category ID
   * @returns {Promise<{product: Product}>} Promise resolving to updated product
   * @throws {Error} Throws error if validation fails or product not found
   */
  async updateProduct(
    productId: number,
    data: Partial<Product>,
  ): Promise<{ product: Product }> {
    return await this.send('PATCH', `products/${productId}`, data);
  }

  /**
   * Upload or update a product image
   *
   * @param {number} productId - The unique identifier of the product
   * @param {File} file - The image file to upload
   * @returns {Promise<{product: Product}>} Promise resolving to product with updated image
   * @throws {Error} Throws error if file is invalid or upload fails
   */
  async uploadProductImage(
    productId: number,
    file: File,
  ): Promise<{ product: Product }> {
    const formData = new FormData();

    formData.append('image', file);

    formData.append('_method', 'PUT');

    return await this.send('POST', `products/${productId}`, formData, {
      'Content-Type': 'multipart/form-data',
    });
  }
}

export default new ProductService();
