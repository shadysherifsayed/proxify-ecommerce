<template>
  <main-layout>
    <v-container class="py-6">
      <!-- Back Button -->
      <v-btn
        variant="text"
        prepend-icon="mdi-arrow-left"
        class="mb-4"
        @click="$router.go(-1)"
      >
        Back to Orders
      </v-btn>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-8">
        <v-progress-circular
          indeterminate
          color="primary"
          size="64"
        />
        <p class="text-body-1 text-grey-darken-1 mt-4">Loading order details...</p>
      </div>

      <!-- Error State -->
      <v-alert
        v-else-if="error"
        type="error"
        variant="outlined"
        class="mb-6"
      >
        {{ error }}
      </v-alert>

      <!-- Order Details -->
      <div v-else-if="order">
        <!-- Order Header -->
        <v-card elevation="2" class="mb-6">
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
                  {{ order.products.length }} {{ order.products.length === 1 ? 'Item' : 'Items' }}
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
        <v-card elevation="2">
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
              <v-img
                :src="product.image"
                height="80"
                width="80"
                cover
                class="rounded mr-4 flex-shrink-0"
              />
              
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

            <!-- Order Summary -->
            <div class="bg-grey-lighten-4 pa-4">
              <div class="d-flex justify-space-between align-center">
                <span class="text-h6 font-weight-medium">Total Amount</span>
                <span class="text-h5 font-weight-bold text-primary">
                  ${{ order.total_price.toFixed(2) }}
                </span>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </div>

      <!-- Not Found State -->
      <div v-else class="text-center py-12">
        <v-icon
          icon="mdi-package-variant-closed"
          size="80"
          color="grey-lighten-1"
          class="mb-4"
        />
        <h2 class="text-h5 font-weight-medium mb-2 text-grey-darken-1">
          Order not found
        </h2>
        <p class="text-body-1 text-grey-darken-2 mb-6">
          The order you're looking for doesn't exist or has been removed.
        </p>
        <v-btn color="primary" @click="$router.push('/orders')">
          Back to Orders
        </v-btn>
      </div>
    </v-container>
  </main-layout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useOrdersStore } from '@/Stores/orders';
import { Order } from '@/Types/entities';
import MainLayout from '@/Layouts/MainLayout.vue';

const route = useRoute();
const ordersStore = useOrdersStore();

const order = ref<Order | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);

const orderId = parseInt(route.params.id as string);

async function fetchOrderDetails() {
  if (!orderId || isNaN(orderId)) {
    error.value = 'Invalid order ID';
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const orderData = await ordersStore.fetchOrder(orderId);
    order.value = orderData;
  } catch (err) {
    error.value = 'Failed to load order details';
    console.error('Error fetching order details:', err);
  } finally {
    loading.value = false;
  }
}

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

onMounted(() => {
  fetchOrderDetails();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 2;
}

.border-bottom {
  border-bottom: 1px solid rgba(var(--v-border-color), 0.12);
}
</style>
