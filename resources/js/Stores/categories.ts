import { defineStore } from 'pinia';
import { ref } from 'vue';
import { Category } from '@/Types/entities';
import { BaseService } from '@/Services/BaseService';

class CategoryService extends BaseService {
  async fetchCategories(): Promise<{ data: Category[] }> {
    return await this.send('GET', '/api/v1/categories');
  }
}

const categoryService = new CategoryService();

export const useCategoriesStore = defineStore('categories', () => {
  const categories = ref<Category[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchCategories() {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await categoryService.fetchCategories();
      categories.value = response.data;
    } catch (err) {
      error.value = 'Failed to fetch categories';
      console.error('Error fetching categories:', err);
    } finally {
      loading.value = false;
    }
  }

  function clearError() {
    error.value = null;
  }

  return {
    // State
    categories,
    loading,
    error,
    
    // Actions
    fetchCategories,
    clearError,
  };
});
