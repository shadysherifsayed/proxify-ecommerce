import { Category } from '@/Types/entities';
import { BaseService } from './BaseService';

/**
 * CategoryService
 *
 * Service class for handling product category operations.
 * Manages category data retrieval for product organization.
 */
class CategoryService extends BaseService {
  /**
   * Fetch all available product categories
   *
   * @returns {Promise<{categories: Category[]}>} Promise resolving to array of categories
   * @throws {Error} Throws error if request fails
   * ```
   */
  fetchCategories(): Promise<{ categories: Category[] }> {
    return this.send('GET', 'categories');
  }
}

export default new CategoryService();
