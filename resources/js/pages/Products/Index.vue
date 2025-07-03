<template>
  <MainLayout>
    <v-container fluid>
      <ProductsList :filtered-products="products" :loading="productsLoading" />
    </v-container>
  </MainLayout>
</template>

<script setup lang="ts">
import ProductsList from '@/Components/Products/ProductsList.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useProductsStore } from '@/Stores/products';
import { storeToRefs } from 'pinia';
import { computed, onMounted } from 'vue';

const productsStore = useProductsStore();
const { products } = storeToRefs(productsStore);

const productsLoading = computed(() => products.value.length === 0);

onMounted(() => {
  productsStore.fetchProducts();
});
</script>
