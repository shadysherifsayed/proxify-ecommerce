import OrderService from '@/Services/OrderService';
import { Order } from '@/Types/entities';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref<Order[]>([]);
  const isLoading = ref(false);

  const ordersCount = computed(() => orders.value.length);

  const ordersByStatus = computed(() => {
    return orders.value.reduce(
      (acc, order) => {
        if (!acc[order.status]) {
          acc[order.status] = [];
        }
        acc[order.status].push(order);
        return acc;
      },
      {} as Record<string, Order[]>,
    );
  });

  const totalSpent = computed(() => {
    return orders.value.reduce((total, order) => total + order.total_price, 0);
  });

  async function fetchOrders() {
    isLoading.value = true;

    try {
      const response = await OrderService.fetchOrders();
      orders.value = response.orders;
    } catch (err) {
      console.error('Error fetching orders:', err);
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchOrder(orderId: number): Promise<Order | null> {
    try {
      const response = await OrderService.fetchOrder(orderId);
      return response.order;
    } catch (err) {
      console.error('Error fetching order:', err);
      return null;
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

  return {
    // State
    orders,
    isLoading,

    // Getters
    ordersCount,
    ordersByStatus,
    totalSpent,

    // Actions
    fetchOrders,
    fetchOrder,
    updateOrder,
  };
});
