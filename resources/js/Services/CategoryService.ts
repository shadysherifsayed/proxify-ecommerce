import { Category } from '@/Types/entities';
import { BaseService } from './BaseService';

class CategoryService extends BaseService {
  /**
   * Fetch all categories
   */
  fetchCategories(): Promise<{ categories: Category[] }> {
    return this.send('GET', 'categories');
  }
}

export default new CategoryService();
