<script setup lang="ts">
import SpinnerLoader from '@/Components/General/SpinnerLoader.vue';
import OrdersFilters from '@/Components/Orders/OrdersFilters.vue';
import OrdersList from '@/Components/Orders/OrdersList.vue';
import OrdersSummary from '@/Components/Orders/OrdersSummary.vue';
import EmptyState from '@/Components/General/EmptyState.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useOrdersStore } from '@/Stores/orders';
import { Order, OrderFilters, Sort } from '@/Types/entities';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const ordersStore = useOrdersStore();
const { orders, isLoading, ordersMeta, appliedFilters, sortConfiguration } =
  storeToRefs(ordersStore);

function updateOrder(orderId: number, data: Partial<Order>) {
  ordersStore.updateOrder(orderId, data);
}

function viewOrder(orderId: number) {
  router.push(`/orders/${orderId}`);
}

function applyFilters(filters: OrderFilters, sort: Sort) {
  ordersStore.resetOrders();
  const filtersToApply: Partial<OrderFilters> = {};
  if (filters.status) {
    filtersToApply.status = filters.status;
  }
  if (filters.min_price) {
    filtersToApply.min_price = filters.min_price;
  }
  if (filters.max_price) {
    filtersToApply.max_price = filters.max_price;
  }
  if (filters.date_from) {
    filtersToApply.date_from = filters.date_from;
  }
  if (filters.date_to) {
    filtersToApply.date_to = filters.date_to;
  }
  appliedFilters.value = filtersToApply;
  sortConfiguration.value = sort;
  ordersStore.fetchOrders();
}

function clearFilters() {
  ordersStore.resetOrders();
  ordersStore.fetchOrders();
}

function changePage(page: number) {
  if (!ordersMeta.value || page === ordersMeta.value.current_page) {
    return;
  }
  ordersMeta.value = {
    ...ordersMeta.value,
    current_page: page,
  };
  ordersStore.fetchOrders();
}

onMounted(() => ordersStore.fetchOrders());
</script>

<template>
  <MainLayout>
    <template #left-drawer>
      <OrdersFilters
        :min-price="1"
        :max-price="1000000"
        @apply="applyFilters"
        @clear="clearFilters"
      />
    </template>

    <v-container class="py-6">
      <!-- Page Header -->
      <OrdersSummary />

      <!-- Loading State -->
      <SpinnerLoader v-if="isLoading" text="Loading your orders..." />

      <!-- Empty State -->
      <EmptyState
        v-else-if="orders.length === 0 && !isLoading"
        title="No Orders Found"
        description="You haven't placed any orders yet."
        icon="mdi-shopping"
        back-text="Start Shopping"
        :back-action="() => router.push('/')"
      />

      <template v-else>
        <OrdersList
          :orders="orders"
          @view-order="viewOrder"
          @update-order="updateOrder"
        />
        <v-pagination
          v-if="ordersMeta"
          :modelValue="ordersMeta.current_page"
          :length="ordersMeta.last_page"
          class="my-4"
          :total-visible="5"
          @update:modelValue="changePage"
        />
      </template>
    </v-container>
  </MainLayout>
</template>
