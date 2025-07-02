<template>
  <v-layout class="h-screen">
    <v-navigation-drawer v-model="drawer">
      <v-list density="compact" item-props :items="items" nav />

      <template #append>
        <v-list-item
          class="ma-2"
          link
          nav
          prepend-icon="mdi-cog-outline"
          title="Settings"
        />
      </template>
    </v-navigation-drawer>

    <v-app-bar border="b" class="ps-4" flat>
      <v-app-bar-nav-icon
        v-if="$vuetify.display.smAndDown"
        @click="drawer = !drawer"
      />

      <v-app-bar-title>E-Commerce</v-app-bar-title>

      <template #append>
        <v-btn class="text-none me-2" height="48" icon slim>
          <v-avatar
            color="surface-light"
            image="https://cdn.vuetifyjs.com/images/john.png"
            size="32"
          />

          <v-menu activator="parent">
            <v-list density="compact" nav>
              <v-list-item
                append-icon="mdi-cog-outline"
                link
                title="Settings"
              />

              <v-list-item
                append-icon="mdi-logout"
                link
                title="Logout"
                @click="useAuthStore().logout()"
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
          <v-badge color="red" :content="cart?.products?.length ?? 0" overlap>
            <v-icon>mdi-cart</v-icon>
          </v-badge>
        </v-btn>
      </template>
    </v-app-bar>

    <CartDrawer v-model="cartDrawer" />

    <v-main class="h-100 overflow-auto">
      <slot />
    </v-main>
  </v-layout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAuthStore } from '@/Stores/auth';
import { useCartStore } from '@/Stores/cart';
import { useProductsStore } from '@/Stores/products';
import { storeToRefs } from 'pinia';
import CartDrawer from '@/Components/Cart/CartDrawer.vue';

const productsStore = useProductsStore();

const cartStore = useCartStore();
const { cart } = storeToRefs(cartStore);
const cartDrawer = ref(false);

const drawer = ref(true);

const items = ref([
  {
    title: 'Products',
    prependIcon: 'mdi-view-dashboard-outline',
    link: true,
  },
  {
    title: 'Orders',
    prependIcon: 'mdi-account-group',
    link: true,
  },
]);

onMounted(() => {
  productsStore.fetchProducts();
  cartStore.fetchCart();
});
</script>
