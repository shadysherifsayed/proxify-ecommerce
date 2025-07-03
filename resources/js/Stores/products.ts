import ProductService from '@/Services/ProductService';
import { Product } from '@/Types/entities';
import { Pagination } from '@/Types/responses';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useProductsStore = defineStore('products', () => {
  const products = ref<Product[]>([]);

  const productsMeta = ref<Pagination>({
    next: null,
    prev: null,
  });

  const product = ref<Product | null>(null);
  const isLoading = ref(false);

  const hasMore = computed(() => productsMeta.value.next !== null);

  /**
   * Fetches the initial list of products from the ProductService.
   * Resets the products array and loads the first page.
   */
  async function fetchProducts() {
    try {
      isLoading.value = true;
      const response = await ProductService.fetchProducts();
      products.value = response.products.data;
      productsMeta.value = {
        next: response.products.next_cursor,
        prev: response.products.prev_cursor,
      };
    } catch (error) {
      console.error('Error fetching products:', error);
      products.value = [];
      productsMeta.value = {
        next: null,
        prev: null,
      };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Resets the products state and fetches fresh data
   */
  function resetProducts() {
    products.value = [];
    productsMeta.value = {
      next: null,
      prev: null,
    };
  }

  return {
    // State
    product,
    products,
    productsMeta,
    isLoading,
    // Getters
    hasMore,

    // Actions
    fetchProducts,
    resetProducts,
  };
});
