<script lang="ts" setup>
import { useAuthStore } from '@/Stores/auth';
import { useCartStore } from '@/Stores/cart';
import { useSystemStore } from '@/Stores/system';
import { storeToRefs } from 'pinia';

const authStore = useAuthStore();

const cartStore = useCartStore();
const { cartCount } = storeToRefs(cartStore);

const systemStore = useSystemStore();
const { isDarkMode } = storeToRefs(systemStore);

const cartDrawer = defineModel<boolean>('cartDrawer', { default: false });
</script>

<template>
  <v-app-bar border="b" class="px-16" flat>
    <v-list-item
      class="text-none"
      prepend-icon="mdi-view-dashboard-outline"
      title="Shop"
      link
      :to="{ name: 'products.index' }"
    />
    <v-list-item
      class="text-none"
      prepend-icon="mdi-package-variant"
      title="Orders"
      link
      :to="{ name: 'orders.index' }"
    />
    <v-spacer />
    <template #append>
      <v-btn
        class="text-none me-2"
        height="48"
        icon
        rounded
        :color="cartCount > 0 ? 'primary' : 'grey-darken-2'"
        @click="cartDrawer = true"
      >
        <v-badge
          :color="cartCount > 0 ? 'primary' : 'grey-lighten-2'"
          variant="tonal"
          :content="cartCount"
          overlap
        >
          <v-icon>mdi-cart</v-icon>
        </v-badge>
      </v-btn>

      <v-btn
        height="48"
        color="grey-darken-2"
        :icon="!isDarkMode ? 'mdi-moon-waning-crescent' : 'mdi-weather-sunny'"
        @click="isDarkMode = !isDarkMode"
      />

      <v-btn
        class="text-none me-2"
        height="48"
        icon="mdi-logout"
        rounded
        color="grey-darken-2"
        @click="authStore.logout"
      />
    </template>
  </v-app-bar>
</template>
