import ProductService from '@/Services/ProductService';
import { Product } from '@/Types/entities';
import { ListMeta } from '@/Types/responses';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useProductsStore = defineStore('products', () => {
  const product = ref<Product | null>(null);
  const products = ref<Product[]>([]);
  const productsMeta = ref<ListMeta>({
    is_fetched: false,
    pagination: null,
  });
  const isLoading = ref(false);
  const isUpdating = ref(false);
  const hasMore = computed(
    () =>
      !productsMeta.value.pagination || !!productsMeta.value.pagination?.next,
  );

  async function fetchProducts() {
    try {
      isLoading.value = true;
      const response = await ProductService.fetchProducts({
        cursor: productsMeta.value?.pagination?.next || null, // Use next cursor for pagination
      });
      products.value = [...products.value, ...response.products.data];
      productsMeta.value = {
        is_fetched: true,
        pagination: {
          next: response.products.next_cursor,
          prev: response.products.prev_cursor,
        },
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
    } catch (error) {
      console.error('Error fetching product:', error);
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
    } catch (error) {
      console.error('Error updating product:', error);
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
    } catch (error) {
      console.error('Error uploading product image:', error);
      throw error; // Re-throw to handle in the component
    } finally {
      isUpdating.value = false;
    }
  }

  /**
   * Resets the products state and fetches fresh data
   */
  function resetProducts() {
    products.value = [];
    productsMeta.value = {
      is_fetched: false,
      pagination: null,
    };
  }

  return {
    // State
    product,
    products,
    productsMeta,
    isLoading,
    isUpdating,

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
