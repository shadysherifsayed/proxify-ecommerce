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
import { ref } from 'vue';

const cartDrawer = ref(false);
</script>
