<script lang="ts" setup>
import { Order } from '@/Types/entities';
import OrderItem from '@/Components/Orders/OrderItem.vue';

defineProps<{
  orders: Order[];
}>();

const emit = defineEmits<{
  (e: 'viewOrder', orderId: number): void;
  (e: 'updateOrder', orderId: number, data: Partial<Order>): void;
}>();
</script>

<template>
  <!-- Orders List -->
  <div class="orders-list">
    <order-item
      v-for="order in orders"
      :key="order.id"
      :order="order"
      @view="emit('viewOrder', order.id)"
      @update="data => emit('updateOrder', order.id, data)"
    />
  </div>
</template>
