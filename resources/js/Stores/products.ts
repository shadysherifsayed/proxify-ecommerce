import ProductService from '@/Services/ProductService';
import { Product } from '@/Types/entities';
import { Pagination } from '@/Types/responses';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useProductsStore = defineStore('products', () => {
  const product = ref<Product | null>(null);
  const products = ref<Product[]>([]);
  const productsMeta = ref<Pagination | null>(null);
  const isLoading = ref(false);
  const isUpdating = ref(false);
  const hasMore = computed(() => productsMeta.value?.next !== null);

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
      const response = await ProductService.uploadProductImage(productId, imageFile);
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
