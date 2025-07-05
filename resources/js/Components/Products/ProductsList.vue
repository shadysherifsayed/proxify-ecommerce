<script setup lang="ts">
import EmptyState from '@/Components/General/EmptyState.vue';
import SpinnerLoader from '@/Components/General/SpinnerLoader.vue';
import ProductItem from '@/Components/Products/ProductItem.vue';
import { Product } from '@/Types/entities';

defineProps<{
  products: Product[];
  isLoading: boolean;
  hasMore: boolean;
}>();

const emit = defineEmits<{
  (e: 'add-to-cart', product: Product): void;
  (e: 'view-product', product: Product): void;
  (e: 'load-more'): void;
}>();
</script>

<template>
  <v-container fluid max-width="90%">
    <template v-if="products.length">
      <v-row class="mb-4">
        <v-col cols="12">
          <h1 class="text-h5">Products</h1>
        </v-col>
      </v-row>
      <v-row v-if="products?.length">
        <v-col
          v-for="product in products"
          :key="product.id"
          cols="12"
          sm="6"
          md="3"
        >
          <ProductItem
            :product="product"
            @add="emit('add-to-cart', product)"
            @view="emit('view-product', product)"
          />
        </v-col>
      </v-row>
      <div class="text-center" v-if="hasMore">
        <v-btn
          color="primary"
          class="mt-4"
          :disabled="isLoading"
          @click="emit('load-more')"
        >
          Load More
        </v-btn>
      </div>
    </template>
    <SpinnerLoader v-else-if="isLoading" text="Loading products..." />
    <EmptyState
      v-else
      title="No products found"
      description="Run the artisan command `php artisan sync:products` to populate the database with products."
      icon="mdi-package-variant-closed"
    />
  </v-container>
</template>
