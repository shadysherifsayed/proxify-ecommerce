import OrderService from '@/Services/OrderService';
import { Order, OrderFilters, Sort } from '@/Types/entities';
import { Pagination } from '@/Types/responses';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useOrdersStore = defineStore('orders', () => {
  const order = ref<Order | null>(null);
  const orders = ref<Order[]>([]);
  const ordersMeta = ref<Pagination | null>(null);
  const isLoading = ref(false);
  const appliedFilters = ref<Partial<OrderFilters>>({});
  const sortConfiguration = ref<Sort>({
    field: 'created_at',
    direction: 'desc' as 'asc' | 'desc',
  });

  async function fetchOrders() {
    try {
      isLoading.value = true;
      const response = await OrderService.fetchOrders({
        page: ordersMeta.value?.current_page || 1,
        sort: sortConfiguration.value,
        filters: appliedFilters.value,
      });
      orders.value = response.orders.data;
      ordersMeta.value = {
        to: response.orders.to,
        from: response.orders.from,
        total: response.orders.total,
        per_page: response.orders.per_page,
        last_page: response.orders.last_page,
        current_page: response.orders.current_page,
      };
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchOrder(orderId: number): Promise<void> {
    try {
      isLoading.value = true;
      const response = await OrderService.fetchOrder(orderId);
      order.value = response.order;
    } catch {
      order.value = null;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateOrder(
    orderId: number,
    data: Partial<Order>,
  ): Promise<boolean> {
    try {
      await OrderService.updateOrder(orderId, data);
      const index = orders.value.findIndex((order) => order.id === orderId);
      if (index !== -1) {
        orders.value[index] = { ...orders.value[index], ...data };
      }
      return true;
    } catch {
      return false;
    }
  }

  function resetOrders() {
    isLoading.value = false;
    ordersMeta.value = null;
    appliedFilters.value = {};
    sortConfiguration.value = {
      field: 'id',
      direction: 'asc' as 'asc' | 'desc',
    };
    orders.value = [];
  }

  function resetOrder() {
    order.value = null;
  }

  return {
    // State
    order,
    orders,
    isLoading,
    ordersMeta,
    appliedFilters,
    sortConfiguration,

    // Actions
    resetOrder,
    fetchOrder,
    updateOrder,
    fetchOrders,
    resetOrders,
  };
});
