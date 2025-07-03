<script lang="ts" setup>
import { useAuthStore } from '@/Stores/auth';
import { useCartStore } from '@/Stores/cart';
import { storeToRefs } from 'pinia';

const authStore = useAuthStore();

const cartStore = useCartStore();
const { cartCount } = storeToRefs(cartStore);

const cartDrawer = defineModel<boolean>('cartDrawer', { default: false });
</script>

<template>
  <v-app-bar border="b" class="px-16" flat>
    <v-list-item
      class="text-none"
      prepend-icon="mdi-view-dashboard-outline"
      title="Shop"
      link
      to="/"
    />
    <v-list-item
      class="text-none"
      prepend-icon="mdi-package-variant"
      title="Orders"
      link
      to="/orders"
    />
    <v-spacer />
    <template #append>
      <v-btn class="text-none me-2" height="48" icon slim>
        <v-avatar
          color="surface-light"
          image="https://cdn.vuetifyjs.com/images/john.png"
          size="32"
        />

        <v-menu activator="parent">
          <v-list density="compact" nav>
            <v-list-item append-icon="mdi-cog-outline" link title="Settings" />

            <v-list-item
              append-icon="mdi-logout"
              link
              title="Logout"
              @click="authStore.logout"
            />
          </v-list>
        </v-menu>
      </v-btn>
      <v-btn
        class="text-none me-2"
        height="48"
        icon
        rounded
        color="primary"
        @click="cartDrawer = true"
      >
        <v-badge color="primary" variant="tonal" :content="cartCount" overlap>
          <v-icon>mdi-cart</v-icon>
        </v-badge>
      </v-btn>
    </template>
  </v-app-bar>
</template>
