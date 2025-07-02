<template>
  <v-container fluid>
    <v-row>
      <v-col v-for="product in products" :key="product.id" cols="12" sm="6" md="3">
        <ProductItem :product="product" @add="cartStore.addToCart(product)" />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import ProductItem from '@/Components/Products/ProductItem.vue';
import { useCartStore } from '@/Stores/cart';

import { useProductsStore } from '@/Stores/products';
import { storeToRefs } from 'pinia';

const productsStore = useProductsStore();
const { products } = storeToRefs(productsStore);

const cartStore = useCartStore();

</script>
