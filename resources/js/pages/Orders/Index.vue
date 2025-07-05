<script setup lang="ts">
import SpinnerLoader from '@/Components/General/SpinnerLoader.vue';
import OrdersList from '@/Components/Orders/OrdersList.vue';
import OrdersSummary from '@/Components/Orders/OrdersSummary.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useOrdersStore } from '@/Stores/orders';
import { Order } from '@/Types/entities';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const ordersStore = useOrdersStore();
const { orders, isLoading, ordersCount, totalSpent } = storeToRefs(ordersStore);

function updateOrder(orderId: number, data: Partial<Order>) {
  ordersStore.updateOrder(orderId, data);
}

function viewOrder(orderId: number) {
  router.push(`/orders/${orderId}`);
}

onMounted(() => ordersStore.fetchOrders());
</script>

<template>
  <main-layout>
    <v-container class="py-6">
      <!-- Page Header -->
      <OrdersSummary :orders-count="ordersCount" :total-spent="totalSpent" />

      <!-- Loading State -->
      <SpinnerLoader v-if="isLoading" text="Loading your orders..." />

      <!-- Empty State -->
      <div
        v-else-if="orders.length === 0 && !isLoading"
        class="text-center py-12"
      >
        <v-icon
          icon="mdi-package-variant-closed"
          size="80"
          color="grey-lighten-1"
          class="mb-4"
        />
        <v-btn
          color="primary"
          size="large"
          prepend-icon="mdi-shopping"
          @click="router.push('/')"
        >
          Start Shopping
        </v-btn>
      </div>

      <OrdersList
        v-else
        :orders="orders"
        @view-order="viewOrder"
        @update-order="updateOrder"
      />
    </v-container>
  </main-layout>
</template>
