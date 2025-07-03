<script setup lang="ts">
import ProductItem from '@/Components/Products/ProductItem.vue';
import SpinnerLoader from '@/Components/General/SpinnerLoader.vue';
import { Product } from '@/Types/entities';

defineProps<{
  products?: Product[];
  isLoading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'add-to-cart', product: Product): void;
  (e: 'edit-product', product: Product): void;
}>();
</script>

<template>
  <v-container fluid max-width="90%">
    <SpinnerLoader v-if="isLoading" text="Loading products..." />
    <v-row v-else>
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
          @edit="emit('edit-product', product)"
        />
      </v-col>
    </v-row>
  </v-container>
</template>
