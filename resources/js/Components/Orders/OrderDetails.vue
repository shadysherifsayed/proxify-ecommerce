<script lang="ts" setup>
import { Order } from '@/Types/entities';

defineProps<{
  order: Order;
}>();

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
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>

<template>
  <!-- Order Header -->
  <v-card elevation="0" class="mb-6">
    <v-card-title class="d-flex justify-space-between align-center">
      <div>
        <h1 class="text-h4 font-weight-bold">Order #{{ order.id }}</h1>
        <p class="text-body-1 text-grey-darken-1 mb-0">
          Placed on {{ formatDate(order.created_at) }}
        </p>
      </div>

      <v-chip
        :color="getStatusColor(order.status)"
        size="large"
        variant="elevated"
        class="font-weight-medium"
      >
        {{ formatStatus(order.status) }}
      </v-chip>
    </v-card-title>

    <v-divider class="my-4"></v-divider>

    <v-card-text>
      <v-row>
        <v-col cols="12" md="4">
          <div class="text-subtitle-2 text-grey-darken-1 mb-2">Order Total</div>
          <div class="text-h5 font-weight-bold text-primary">
            ${{ order.total_price.toFixed(2) }}
          </div>
        </v-col>

        <v-col cols="12" md="4">
          <div class="text-subtitle-2 text-grey-darken-1 mb-2">Items Count</div>
          <div class="text-h6 font-weight-medium">
            {{ order.products.length }}
            {{ order.products.length === 1 ? 'Item' : 'Items' }}
          </div>
        </v-col>

        <v-col cols="12" md="4">
          <div class="text-subtitle-2 text-grey-darken-1 mb-2">Customer</div>
          <div class="text-h6 font-weight-medium">
            User #{{ order.user_id }}
          </div>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>

  <!-- Order Items -->
  <v-card elevation="0">
    <v-card-title>
      <v-icon icon="mdi-package-variant" class="mr-2"></v-icon>
      Order Items
    </v-card-title>

    <v-divider></v-divider>

    <v-card-text class="pa-0">
      <div
        v-for="(product, index) in order.products"
        :key="product.id"
        class="d-flex align-center pa-4"
        :class="{ 'border-bottom': index < order.products.length - 1 }"
      >
        <div class="product-image-container mr-4 flex-shrink-0">
          <v-img
            :src="product.image"
            height="120"
            width="120"
            cover
            class="product-image elevation-2"
            :alt="product.title"
          >
            <template #placeholder>
              <div class="d-flex align-center justify-center fill-height">
                <v-progress-circular color="grey-lighten-4" indeterminate />
              </div>
            </template>
            <template #error>
              <div
                class="d-flex align-center justify-center fill-height bg-grey-lighten-3"
              >
                <v-icon icon="mdi-image-broken" color="grey" size="32" />
              </div>
            </template>
          </v-img>
        </div>

        <div class="flex-grow-1">
          <h3 class="text-h6 font-weight-medium mb-2">
            {{ product.title }}
          </h3>
          <p class="text-body-2 text-grey-darken-1 mb-2 line-clamp-2">
            {{ product.description }}
          </p>
          <div class="d-flex align-center">
            <v-chip size="small" variant="outlined" class="mr-2">
              Qty: {{ product.pivot.quantity }}
            </v-chip>
            <v-chip size="small" variant="outlined">
              Price: ${{ product.pivot.price.toFixed(2) }}
            </v-chip>
          </div>
        </div>

        <div class="text-right ml-4">
          <div class="text-h6 font-weight-bold text-primary">
            ${{ (product.pivot.quantity * product.pivot.price).toFixed(2) }}
          </div>
        </div>
      </div>

      <v-divider />
      <!-- Order Summary -->
      <div class="pa-4">
        <div class="d-flex justify-space-between align-center">
          <span class="text-h6 font-weight-medium">Total Amount</span>
          <span class="text-h5 font-weight-bold text-primary">
            ${{ order.total_price.toFixed(2) }}
          </span>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>
