<template>
  <main-layout>
    <v-container class="py-6">
      <!-- Page Header -->
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold mb-2">My Orders</h1>
          <p class="text-body-1 text-grey-darken-1">
            Track and manage your order history
          </p>
        </div>
        
        <!-- Quick Stats -->
        <div class="d-flex gap-4">
          <v-card elevation="2" class="px-4 py-3 text-center">
            <div class="text-h5 font-weight-bold text-primary">{{ ordersCount }}</div>
            <div class="text-caption text-grey-darken-1">Total Orders</div>
          </v-card>
          
          <v-card elevation="2" class="px-4 py-3 text-center">
            <div class="text-h5 font-weight-bold text-success">{{ totalSpent.toFixed(2) }}</div>
            <div class="text-caption text-grey-darken-1">Total Spent</div>
          </v-card>
        </div>
      </div>

      <!-- Filters and Search -->
      <v-card elevation="1" class="mb-6">
        <v-card-text class="py-4">
          <v-row align="center">
            <v-col cols="12" md="6">
              <v-text-field
                v-model="searchQuery"
                placeholder="Search orders by ID or product name..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                hide-details
                clearable
              />
            </v-col>
            
            <v-col cols="12" md="3">
              <v-select
                v-model="statusFilter"
                :items="statusOptions"
                placeholder="Filter by status"
                variant="outlined"
                density="compact"
                hide-details
                clearable
              />
            </v-col>
            
            <v-col cols="12" md="3">
              <v-select
                v-model="sortBy"
                :items="sortOptions"
                placeholder="Sort by"
                variant="outlined"
                density="compact"
                hide-details
              />
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-8">
        <v-progress-circular
          indeterminate
          color="primary"
          size="64"
        />
        <p class="text-body-1 text-grey-darken-1 mt-4">Loading your orders...</p>
      </div>

      <!-- Error State -->
      <v-alert
        v-else-if="error"
        type="error"
        variant="outlined"
        class="mb-6"
        closable
        @click:close="clearError"
      >
        {{ error }}
      </v-alert>

      <!-- Empty State -->
      <div v-else-if="filteredOrders.length === 0 && !loading" class="text-center py-12">
        <v-icon
          icon="mdi-package-variant-closed"
          size="80"
          color="grey-lighten-1"
          class="mb-4"
        />
        <h2 class="text-h5 font-weight-medium mb-2 text-grey-darken-1">
          {{ searchQuery || statusFilter ? 'No orders found' : 'No orders yet' }}
        </h2>
        <p class="text-body-1 text-grey-darken-2 mb-6">
          {{ 
            searchQuery || statusFilter 
              ? 'Try adjusting your search or filter criteria'
              : 'Start shopping to see your orders here' 
          }}
        </p>
        <v-btn
          v-if="!searchQuery && !statusFilter"
          color="primary"
          size="large"
          prepend-icon="mdi-shopping"
          @click="$router.push('/')"
        >
          Start Shopping
        </v-btn>
      </div>

      <!-- Orders List -->
      <div v-else class="orders-list">
        <order-item
          v-for="order in paginatedOrders"
          :key="order.id"
          :order="order"
          @view="viewOrderDetails"
          @update-status="updateOrderStatus"
        />

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="d-flex justify-center mt-6">
          <v-pagination
            v-model="currentPage"
            :length="totalPages"
            :total-visible="7"
            color="primary"
          />
        </div>
      </div>

      <!-- Refresh FAB -->
      <v-fab
        icon="mdi-refresh"
        location="bottom end"
        size="small"
        color="primary"
        @click="refreshOrders"
      />
    </v-container>
  </main-layout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useOrdersStore } from '@/Stores/orders';
import { storeToRefs } from 'pinia';
import MainLayout from '@/Layouts/MainLayout.vue';
import OrderItem from '@/Components/Orders/OrderItem.vue';

const router = useRouter();
const ordersStore = useOrdersStore();
const { orders, loading, error, ordersCount, totalSpent } = storeToRefs(ordersStore);

// Filters and search
const searchQuery = ref('');
const statusFilter = ref('');
const sortBy = ref('newest');

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;

const statusOptions = [
  { title: 'All Statuses', value: '' },
  { title: 'Pending', value: 'pending' },
  { title: 'Processing', value: 'processing' },
  { title: 'Completed', value: 'completed' },
  { title: 'Cancelled', value: 'cancelled' },
];

const sortOptions = [
  { title: 'Newest First', value: 'newest' },
  { title: 'Oldest First', value: 'oldest' },
  { title: 'Highest Amount', value: 'highest' },
  { title: 'Lowest Amount', value: 'lowest' },
];

// Computed properties
const filteredOrders = computed(() => {
  let filtered = [...orders.value];

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(order => 
      order.id.toString().includes(query) ||
      order.products.some(product => 
        product.title.toLowerCase().includes(query)
      )
    );
  }

  // Apply status filter
  if (statusFilter.value) {
    filtered = filtered.filter(order => order.status === statusFilter.value);
  }

  // Apply sorting
  switch (sortBy.value) {
    case 'oldest':
      filtered.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());
      break;
    case 'highest':
      filtered.sort((a, b) => b.total_price - a.total_price);
      break;
    case 'lowest':
      filtered.sort((a, b) => a.total_price - b.total_price);
      break;
    case 'newest':
    default:
      filtered.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
      break;
  }

  return filtered;
});

const totalPages = computed(() => {
  return Math.ceil(filteredOrders.value.length / itemsPerPage);
});

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredOrders.value.slice(start, end);
});

// Methods
async function refreshOrders() {
  await ordersStore.fetchOrders();
}

function viewOrderDetails(orderId: number) {
  router.push(`/orders/${orderId}`);
}

async function updateOrderStatus(orderId: number, status: string) {
  const success = await ordersStore.updateOrderStatus(orderId, status);
  if (success) {
    // Could show a success message here
    console.log(`Order ${orderId} status updated to ${status}`);
  }
}

function clearError() {
  ordersStore.clearError();
}

// Watch for filter changes to reset pagination
watch([searchQuery, statusFilter, sortBy], () => {
  currentPage.value = 1;
});

// Lifecycle
onMounted(() => {
  ordersStore.fetchOrders();
});
</script>

<style scoped>
.orders-list {
  max-width: 100%;
}

/* Custom scrollbar for better UX */
:deep(.v-container) {
  max-height: calc(100vh - 80px);
}
</style>
