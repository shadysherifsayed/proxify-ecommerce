<template>
  <MainLayout>
    <v-container class="py-6" max-width="1200">
      <v-btn
        variant="text"
        prepend-icon="mdi-arrow-left"
        class="mb-4"
        @click="$router.push('/orders')"
      >
        Back to Orders
      </v-btn>

      <SpinnerLoader v-if="isLoading" text="Loading order details..." />

      <OrderDetails v-else-if="order && !error" :order="order" />

      <EmptyState
        v-else-if="!isLoading && !order"
        title="Order Not Found"
        description="The order you are looking for does not exist or has been removed."
        icon="mdi-package-variant-closed"
        :back-action="() => router.push('/orders')"
        back-text="Back to Orders"
      />
    </v-container>
  </MainLayout>
</template>

<script setup lang="ts">
import EmptyState from '@/Components/General/EmptyState.vue';
import SpinnerLoader from '@/Components/General/SpinnerLoader.vue';
import OrderDetails from '@/Components/Orders/OrderDetails.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import router from '@/Router';
import { useOrdersStore } from '@/Stores/orders';
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const orderId = parseInt(route.params.id as string);

const ordersStore = useOrdersStore();
const { order, isLoading } = storeToRefs(ordersStore);

const error = ref<string | null>(null);

async function fetchOrderDetails() {
  if (!orderId || isNaN(orderId)) {
    error.value = 'Invalid order ID';
    return;
  }
  await ordersStore.fetchOrder(orderId);
  if (!order.value) {
    error.value = 'Order not found';
    return;
  }
}

onMounted(fetchOrderDetails);

onUnmounted(ordersStore.resetOrder);
</script>
