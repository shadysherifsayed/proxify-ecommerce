<template>
  <v-card elevation="2" class="mb-4" :class="statusColor">
    <v-card-title class="d-flex justify-space-between align-center pb-2">
      <div>
        <h3 class="text-h6 font-weight-bold">
          Order #{{ order.id }}
        </h3>
        <div class="text-body-2 text-grey-darken-1">
          {{ formatDate(order.created_at) }}
        </div>
      </div>
      
      <v-chip
        :color="getStatusColor(order.status)"
        :variant="order.status === 'completed' ? 'elevated' : 'outlined'"
        size="small"
        class="font-weight-medium"
      >
        {{ formatStatus(order.status) }}
      </v-chip>
    </v-card-title>

    <v-divider class="mb-3"></v-divider>

    <v-card-text class="pt-0">
      <!-- Order Summary -->
      <div class="d-flex justify-space-between align-center mb-3">
        <div class="d-flex align-center">
          <v-icon icon="mdi-package-variant" size="small" class="mr-2"></v-icon>
          <span class="text-body-2">
            {{ order.products.length }} {{ order.products.length === 1 ? 'Item' : 'Items' }}
          </span>
        </div>
        
        <div class="text-h6 font-weight-bold text-primary">
          ${{ order.total_price?.toFixed(2) ?? 0 }}
        </div>
      </div>

      <!-- Products List -->
      <div class="products-list">
        <div
          v-for="(product, index) in order.products.slice(0, showAllProducts ? order.products.length : 3)"
          :key="product.id"
          class="d-flex align-center py-2"
          :class="{ 'border-top': index > 0 }"
        >
          <!-- <v-img
            :src="product.image"
            height="40"
            width="40"
            cover
            class="rounded mr-3 flex-shrink-0"
          /> -->
          
          <div class="flex-grow-1 min-width-0">
            <div class="text-body-2 font-weight-medium text-truncate">
              {{ product.title }}
            </div>
            <div class="text-caption text-grey-darken-1">
              Qty: {{ product.pivot.quantity }} × ${{ product.pivot.price.toFixed(2) }}
            </div>
          </div>
          
          <div class="text-body-2 font-weight-medium text-right ml-2">
            ${{ (product.pivot.quantity * product.pivot.price).toFixed(2) }}
          </div>
        </div>

        <!-- Show more/less button -->
        <v-btn
          v-if="order.products.length > 3"
          variant="text"
          size="small"
          color="primary"
          class="mt-2"
          @click="showAllProducts = !showAllProducts"
        >
          {{ showAllProducts ? 'Show Less' : `Show ${order.products.length - 3} More Items` }}
          <v-icon :icon="showAllProducts ? 'mdi-chevron-up' : 'mdi-chevron-down'" class="ml-1" />
        </v-btn>
      </div>
    </v-card-text>

    <v-divider></v-divider>

    <v-card-actions class="d-flex justify-space-between align-center">
      <v-btn
        variant="outlined"
        size="small"
        prepend-icon="mdi-eye"
        @click="emit('view', order.id)"
      >
        View Details
      </v-btn>

      <div class="d-flex gap-2">
        <v-btn
          v-if="order.status === 'pending'"
          variant="text"
          size="small"
          color="warning"
          prepend-icon="mdi-clock"
          @click="emit('updateStatus', order.id, 'processing')"
        >
          Mark Processing
        </v-btn>
        
        <v-btn
          v-if="order.status === 'processing'"
          variant="text"
          size="small"
          color="success"
          prepend-icon="mdi-check"
          @click="emit('updateStatus', order.id, 'completed')"
        >
          Mark Complete
        </v-btn>

        <v-btn
          v-if="['pending', 'processing'].includes(order.status)"
          variant="text"
          size="small"
          color="error"
          prepend-icon="mdi-cancel"
          @click="emit('updateStatus', order.id, 'cancelled')"
        >
          Cancel
        </v-btn>
      </div>
    </v-card-actions>
  </v-card>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Order } from '@/Types/entities';

const props = defineProps<{
  order: Order;
}>();

const emit = defineEmits<{
  (e: 'view', orderId: number): void;
  (e: 'updateStatus', orderId: number, status: string): void;
}>();

const showAllProducts = ref(false);

const statusColor = computed(() => {
  const colors = {
    pending: 'border-l-warning',
    processing: 'border-l-info',
    completed: 'border-l-success',
    cancelled: 'border-l-error',
  };
  return colors[props.order.status as keyof typeof colors] || 'border-l-grey';
});

function getStatusColor(status: string): string {
  const colors = {
    pending: 'warning',
    processing: 'info',
    completed: 'success',
    cancelled: 'error',
  };
  return colors[status as keyof typeof colors] || 'grey';
}

function formatStatus(status: string): string {
  return status.charAt(0).toUpperCase() + status.slice(1);
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>

<style scoped>
.min-width-0 {
  min-width: 0;
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.border-top {
  border-top: 1px solid rgba(var(--v-border-color), 0.12);
}

.border-l-warning {
  border-left: 4px solid rgb(var(--v-theme-warning));
}

.border-l-info {
  border-left: 4px solid rgb(var(--v-theme-info));
}

.border-l-success {
  border-left: 4px solid rgb(var(--v-theme-success));
}

.border-l-error {
  border-left: 4px solid rgb(var(--v-theme-error));
}

.border-l-grey {
  border-left: 4px solid rgb(var(--v-theme-surface-variant));
}
</style>
