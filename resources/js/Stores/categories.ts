import CategoryService from '@/Services/CategoryService';
import { Category } from '@/Types/entities';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useCategoriesStore = defineStore('categories', () => {
  const categories = ref<Category[]>([]);
  const isLoading = ref(false);

  async function fetchCategories() {
    isLoading.value = true;

    try {
      if (categories.value.length > 0) {
        return; // If categories are already fetched, no need to fetch again
      }
      const response = await CategoryService.fetchCategories();
      categories.value = response.categories;
    } finally {
      isLoading.value = false;
    }
  }

  return {
    // State
    categories,
    isLoading,

    // Actions
    fetchCategories,
  };
});
