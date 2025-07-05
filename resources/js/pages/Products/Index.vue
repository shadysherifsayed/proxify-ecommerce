<script setup lang="ts">
import ProductsList from '@/Components/Products/ProductsList.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import router from '@/Router';
import { useCartStore } from '@/Stores/cart';
import { useProductsStore } from '@/Stores/products';
import { Product } from '@/Types/entities';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

const productsStore = useProductsStore();
const { products, productsMeta, isLoading, hasMore } = storeToRefs(productsStore);

const cartStore = useCartStore();

onMounted(() => productsMeta.value?.is_fetched || productsStore.fetchProducts());
</script>

<template>
  <MainLayout>
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
  </MainLayout>
</template>
