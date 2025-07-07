<script setup lang="ts">
import CartDrawer from '@/Components/Cart/CartDrawer.vue';
import AppBar from '@/Components/Layout/AppBar.vue';
import { useCartStore } from '@/Stores/cart';
import { useCategoriesStore } from '@/Stores/categories';
import { onMounted, ref } from 'vue';

const cartDrawer = ref(false);

const categoriesStore = useCategoriesStore();
const cartStore = useCartStore();

onMounted(() => {
  categoriesStore.fetchCategories();
  cartStore.fetchCart();
});
</script>

<template>
  <v-layout class="h-screen">
    <AppBar v-model:cart-drawer="cartDrawer" />

    <CartDrawer v-model="cartDrawer" />

    <!-- Add slot conditional for left drawer -->
    <slot name="left-drawer"> </slot>

    <v-main class="h-100 overflow-auto">
      <slot />
    </v-main>
  </v-layout>
</template>
