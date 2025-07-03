import { Product } from '@/Types/entities';
import { ProductListResponse } from '@/Types/responses';
import { BaseService } from './BaseService';

class ProductService extends BaseService {

  fetchProducts(): Promise<ProductListResponse> {
    return this.send('GET', 'products');
  }


  /**
   * Fetch a specific product by ID
   */
  async fetchProduct(productId: number): Promise<{ product: Product }> {
    return await this.send('GET', `products/${productId}`);
  }

  /**
   * Update product details
   */
  async updateProduct(productId: number, data: Partial<Product>): Promise<{ product: Product }> {
    return await this.send('PATCH', `products/${productId}`, data);
  }

  async uploadProductImage(productId: number, file: File): Promise<{ url: string }> {
    
    const formData = new FormData();
    
    formData.append('image', file);

    return await this.send('POST', `products/${productId}/image`, formData, {
      'Content-Type': 'multipart/form-data',
    });
  }
}

export default new ProductService();
