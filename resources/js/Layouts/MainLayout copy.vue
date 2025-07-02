<template>
  <v-layout class="rounded rounded-md border" style="height: 100vh; overflow: hidden;">
    <v-app-bar title="Application bar"></v-app-bar>

    <v-navigation-drawer>
      <v-list nav>
        <v-list-item title="Navigation drawer" link></v-list-item>
      </v-list>
    </v-navigation-drawer>
    <v-main class="overflow-y-auto py-0 main-container" style="height: calc(100vh - 64px);">
      <slot />
    </v-main>
  </v-layout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';

import { useProductsStore } from '@/Stores/products';

const productsStore = useProductsStore();

onMounted(() => {
  productsStore.fetchProducts();
});
</script>
