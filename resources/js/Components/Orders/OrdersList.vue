<script lang="ts" setup>
import OrderItem from '@/Components/Orders/OrderItem.vue';
import { Order } from '@/Types/entities';

defineProps<{
  orders: Order[];
}>();

const emit = defineEmits<{
  (e: 'viewOrder', orderId: number): void;
  (e: 'updateOrder', orderId: number, data: Partial<Order>): void;
}>();
</script>

<template>
  <div class="orders-list">
    <order-item
      v-for="order in orders"
      :key="order.id"
      :order="order"
      @view="emit('viewOrder', order.id)"
      @update="(data) => emit('updateOrder', order.id, data)"
    />
  </div>
</template>
