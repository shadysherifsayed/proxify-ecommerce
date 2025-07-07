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
  <v-app-bar border="b" class="ps-4 pr-16" flat>
    <!-- Logo/Brand -->
    <div class="d-flex align-center">
      <v-icon icon="mdi-shopping" size="32" color="primary" class="me-2" />
      <span
        class="text-h5 font-weight-bold gradient-text"
        style="
          background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
          background-clip: text;
          -webkit-text-fill-color: transparent;
        "
      >
        ShadeL
      </span>
    </div>

    <!-- Navigation -->
    <div class="d-flex align-center ms-8">
      <v-btn
        class="text-none mx-2 nav-btn"
        variant="text"
        size="large"
        prepend-icon="mdi-storefront-outline"
        :to="{ name: 'products.index' }"
        :color="isDarkMode ? 'white' : 'grey-darken-2'"
      >
        Shop
      </v-btn>
      <v-btn
        class="text-none mx-2 nav-btn"
        variant="text"
        size="large"
        prepend-icon="mdi-receipt-text-outline"
        :to="{ name: 'orders.index' }"
        :color="isDarkMode ? 'white' : 'grey-darken-2'"
      >
        Orders
      </v-btn>
    </div>

    <v-spacer />
    <template #append>
      <v-btn
        height="48"
        :color="isDarkMode ? 'yellow-lighten-2' : 'blue-darken-2'"
        :icon="!isDarkMode ? 'mdi-moon-waning-crescent' : 'mdi-weather-sunny'"
        @click="isDarkMode = !isDarkMode"
      />

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
        class="text-none me-2"
        height="48"
        icon="mdi-logout"
        rounded
        color="primary"
        @click="authStore.logout"
      />
    </template>
  </v-app-bar>
</template>
