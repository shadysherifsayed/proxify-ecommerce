import OrderService from '@/Services/OrderService';
import { Order } from '@/Types/entities';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useOrdersStore = defineStore('orders', () => {
  const order = ref<Order | null>(null);
  const orders = ref<Order[]>([]);
  const isLoading = ref(false);

  const ordersCount = computed(() => orders.value.length);

  const totalSpent = computed(() => {
    return orders.value.reduce((total, order) => total + order.total_price, 0);
  });

  async function fetchOrders() {
    try {
      isLoading.value = true;
      const response = await OrderService.fetchOrders();
      orders.value = response.orders;
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

  function resetOrder() {
    order.value = null;
  }

  return {
    // State
    order,
    orders,
    isLoading,

    // Getters
    ordersCount,
    totalSpent,

    // Actions
    resetOrder,
    fetchOrder,
    updateOrder,
    fetchOrders,
  };
});
