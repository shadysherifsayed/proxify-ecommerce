import ProductService from '@/Services/ProductService';
import { Product, ProductFilters, Sort } from '@/Types/entities';
import { CursorPagination } from '@/Types/responses';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useProductsStore = defineStore('products', () => {
  const product = ref<Product | null>(null);
  const products = ref<Product[]>([]);
  const appliedFilters = ref<Partial<ProductFilters>>({});
  const sortConfiguration = ref<Sort>({
    field: 'id',
    direction: 'asc' as 'asc' | 'desc',
  });
  const productsMeta = ref<CursorPagination | null>(null);
  const isLoading = ref(false);
  const isUpdating = ref(false);
  const hasMore = computed(
    () => !productsMeta.value || !!productsMeta.value?.next,
  );

  async function fetchProducts() {
    try {
      isLoading.value = true;
      const response = await ProductService.fetchProducts({
        cursor: productsMeta.value?.next || null, // Use next cursor for pagination
        filters: appliedFilters.value,
        sort: sortConfiguration.value,
      });
      products.value = [...products.value, ...response.products.data];
      productsMeta.value = {
        next: response.products.next_cursor,
        prev: response.products.prev_cursor,
      };
    } catch {
      products.value = [];
      resetProducts();
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchProduct(productId: number) {
    try {
      isLoading.value = true;
      const response = await ProductService.fetchProduct(productId);
      product.value = response.product;
    } catch {
      product.value = null;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateProduct(productId: number, data: Partial<Product>) {
    try {
      isUpdating.value = true;
      const response = await ProductService.updateProduct(productId, data);
      product.value = response.product;
      return response.product; // Return the updated product
    } catch (error) {
      throw error; // Re-throw to handle in the component
    } finally {
      isUpdating.value = false;
    }
  }

  async function updateProductImage(productId: number, imageFile: File) {
    try {
      isUpdating.value = true;
      const response = await ProductService.uploadProductImage(
        productId,
        imageFile,
      );
      product.value = response.product;
    } finally {
      isUpdating.value = false;
    }
  }

  /**
   * Resets the products state and fetches fresh data
   */
  function resetProducts() {
    products.value = [];
    productsMeta.value = null;
    appliedFilters.value = {};
    sortConfiguration.value = {
      field: 'id',
      direction: 'asc',
    };
  }

  return {
    // State
    product,
    products,
    productsMeta,
    isLoading,
    isUpdating,
    appliedFilters,
    sortConfiguration,

    // Getters
    hasMore,

    // Actions
    fetchProducts,
    resetProducts,
    fetchProduct,
    updateProduct,
    updateProductImage,
  };
});
