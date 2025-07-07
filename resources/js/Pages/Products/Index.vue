<script setup lang="ts">
import ProductsFilters from '@/Components/Products/ProductsFilters.vue';
import ProductsList from '@/Components/Products/ProductsList.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import router from '@/Router';
import { useCartStore } from '@/Stores/cart';
import { useCategoriesStore } from '@/Stores/categories';
import { useProductsStore } from '@/Stores/products';
import { Product, ProductFilters, Sort } from '@/Types/entities';
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted } from 'vue';

const productsStore = useProductsStore();
const { products, isLoading, hasMore, appliedFilters, sortConfiguration } =
  storeToRefs(productsStore);

const cartStore = useCartStore();

const categoriesStore = useCategoriesStore();
const { categories } = storeToRefs(categoriesStore);

function applyFilters(filters: ProductFilters, sort: Sort) {
  productsStore.resetProducts();
  const filtersToApply: Record<string, any> = {};
  if (filters.categories && filters.categories.length) {
    filtersToApply.categories = filters.categories;
  }
  if (filters.min_price) {
    filtersToApply.min_price = filters.min_price;
  }
  if (filters.max_price) {
    filtersToApply.max_price = filters.max_price;
  }
  if (filters.min_rating) {
    filtersToApply.min_rating = filters.min_rating;
  }
  if (filters.search) {
    filtersToApply.search = filters.search;
  }
  appliedFilters.value = filtersToApply;
  sortConfiguration.value = sort;
  productsStore.fetchProducts();
}

function clearFilters() {
  productsStore.resetProducts();
  productsStore.fetchProducts();
}

onMounted(productsStore.fetchProducts);

onUnmounted(() => productsStore.resetProducts());
</script>

<template>
  <MainLayout>
    <template #left-drawer>
      <ProductsFilters
        :model-value="true"
        :categories="categories"
        :min-price="1"
        :max-price="10000"
        @apply="applyFilters"
        @clear="clearFilters"
      />
    </template>
    <v-container class="py-6">
      <div class="mb-8">
        <h1 class="text-h4 font-weight-bold mb-2">Available Products</h1>
        <p class="text-body-1 text-grey-darken-1">
          Browse and shop the latest products
        </p>
      </div>
      <ProductsList
        class="mt-8"
        :products="products"
        :is-loading="isLoading"
        :has-more="hasMore"
        @load-more="productsStore.fetchProducts"
        @view-product="
          (product: Product) =>
            router.push({ name: 'products.show', params: { id: product.id } })
        "
        @add-to-cart="(product: Product) => cartStore.addToCart(product)"
      />
    </v-container>
  </MainLayout>
</template>
