import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { Order } from '@/Types/entities';
import OrderService from '@/Services/OrderService';

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref<Order[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const ordersCount = computed(() => orders.value.length);

  const ordersByStatus = computed(() => {
    return orders.value.reduce((acc, order) => {
      if (!acc[order.status]) {
        acc[order.status] = [];
      }
      acc[order.status].push(order);
      return acc;
    }, {} as Record<string, Order[]>);
  });

  const totalSpent = computed(() => {
    return orders.value.reduce((total, order) => total + order.total_price, 0);
  });

  async function fetchOrders() {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await OrderService.fetchOrders();
      orders.value = response.data;
    } catch (err) {
      error.value = 'Failed to fetch orders';
      console.error('Error fetching orders:', err);
    } finally {
      loading.value = false;
    }
  }

  async function fetchOrder(orderId: number): Promise<Order | null> {
    try {
      const response = await OrderService.fetchOrder(orderId);
      return response.data;
    } catch (err) {
      error.value = 'Failed to fetch order';
      console.error('Error fetching order:', err);
      return null;
    }
  }

  async function createOrder(): Promise<Order | null> {
    try {
      const response = await OrderService.createOrder();
      const newOrder = response.data;
      orders.value.unshift(newOrder); // Add to beginning of array
      return newOrder;
    } catch (err) {
      error.value = 'Failed to create order';
      console.error('Error creating order:', err);
      return null;
    }
  }

  async function updateOrderStatus(orderId: number, status: string): Promise<boolean> {
    try {
      const response = await OrderService.updateOrderStatus(orderId, status);
      const updatedOrder = response.data;
      
      const index = orders.value.findIndex(order => order.id === orderId);
      if (index !== -1) {
        orders.value[index] = updatedOrder;
      }
      
      return true;
    } catch (err) {
      error.value = 'Failed to update order status';
      console.error('Error updating order status:', err);
      return false;
    }
  }

  function clearError() {
    error.value = null;
  }

  return {
    // State
    orders,
    loading,
    error,
    
    // Getters
    ordersCount,
    ordersByStatus,
    totalSpent,
    
    // Actions
    fetchOrders,
    fetchOrder,
    createOrder,
    updateOrderStatus,
    clearError,
  };
});
