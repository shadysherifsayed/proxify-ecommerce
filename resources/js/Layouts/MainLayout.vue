<template>
  <v-layout class="h-screen">
    <AppBar v-model:cart-drawer="cartDrawer" />

    <CartDrawer v-model="cartDrawer" />

    <v-main class="h-100 overflow-auto">
      <slot />
    </v-main>
  </v-layout>
</template>

<script setup lang="ts">
import CartDrawer from '@/Components/Cart/CartDrawer.vue';
import AppBar from '@/Components/Layout/AppBar.vue';
import { useCartStore } from '@/Stores/cart';
import { onMounted, ref } from 'vue';

const cartStore = useCartStore();
const cartDrawer = ref(false);

onMounted(() => cartStore.fetchCart());
</script>
