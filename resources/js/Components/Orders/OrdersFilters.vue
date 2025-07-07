<script setup lang="ts">
import { OrderFilters, Sort } from '@/Types/entities';
import { formatStatus, getStatusColor, getStatusIcon } from '@/Utils/orders';
import { computed, ref } from 'vue';

// Emits
const emit = defineEmits<{
  apply: [filters: OrderFilters, sort: Sort];
  clear: [];
}>();

const filters = ref<OrderFilters>({
  status: '',
  min_price: null,
  max_price: null,
  date_from: null,
  date_to: null,
});

const sortBy = ref<'newest' | 'oldest' | 'price_low' | 'price_high'>('newest');

const sort = ref<Sort>({
  field: 'created_at',
  direction: 'desc',
});

// Sort options
const sortOptions = ref([
  {
    value: 'newest',
    label: 'Newest First',
    icon: 'mdi-clock-outline',
    onClick: () => {
      sortBy.value = 'newest';
      sort.value = { field: 'created_at', direction: 'desc' };
    },
  },
  {
    value: 'oldest',
    label: 'Oldest First',
    icon: 'mdi-clock-outline',
    onClick: () => {
      sortBy.value = 'oldest';
      sort.value = { field: 'created_at', direction: 'asc' };
    },
  },
  {
    value: 'price_low',
    label: 'Price: Low to High',
    icon: 'mdi-arrow-up',
    onClick: () => {
      sortBy.value = 'price_low';
      sort.value = { field: 'total_price', direction: 'asc' };
    },
  },
  {
    value: 'price_high',
    label: 'Price: High to Low',
    icon: 'mdi-arrow-down',
    onClick: () => {
      sortBy.value = 'price_high';
      sort.value = { field: 'total_price', direction: 'desc' };
    },
  },
]);

const hasActiveFilters = computed(() => {
  return (
    !!filters.value.min_price ||
    !!filters.value.max_price ||
    !!filters.value.date_from ||
    !!filters.value.date_to ||
    !!filters.value.status ||
    sortBy.value !== 'newest'
  );
});

// Methods
function applyFilters() {
  emit('apply', filters.value, sort.value);
}

function clearFilters() {
  filters.value = {
    status: '',
    min_price: null,
    max_price: null,
    date_from: null,
    date_to: null,
  };
  sortBy.value = 'newest';
  sort.value = { field: 'id', direction: 'desc' };
  emit('clear');
}
</script>

<template>
  <v-navigation-drawer persistent location="left" width="500">
    <v-card
      flat
      :rounded="0"
      :border="0"
      class="d-flex flex-column h-100 overflow-hidden"
    >
      <v-card-title class="text-h6"> Tune your Results </v-card-title>

      <v-card-subtitle class="text-subtitle-1 text-grey">
        Filter and sort your orders
      </v-card-subtitle>

      <v-divider class="my-2"></v-divider>

      <!-- Filters Content -->
      <v-card-text class="flex-grow-1 overflow-auto py-0">
        <!-- Sort Filter -->
        <div class="filter-section mb-6">
          <h4 class="text-subtitle-1 mb-3 text-primary">
            <v-icon class="me-2">mdi-sort</v-icon>
            Sort By
          </h4>
          <v-select
            :model-value="sortBy"
            :items="sortOptions"
            item-title="label"
            item-value="value"
            variant="outlined"
            density="compact"
            hide-details
            color="primary"
          >
            <template #prepend-item>
              <div class="pa-2">
                <v-list-subheader>Choose sort option</v-list-subheader>
              </div>
            </template>
            <template #item="{ props: itemProps, item }">
              <v-list-item v-bind="itemProps" @click="item.raw.onClick">
                <template #prepend>
                  <v-icon :icon="item.raw.icon" color="primary" class="me-3" />
                </template>
              </v-list-item>
            </template>
            <template #selection="{ item }">
              <div class="d-flex align-center">
                <v-icon
                  :icon="item.raw.icon"
                  color="primary"
                  class="me-2"
                  size="20"
                />
                <span>{{ item.raw.label }}</span>
              </div>
            </template>
          </v-select>
        </div>

        <!-- Status Filter -->
        <div class="filter-section mb-6">
          <h4 class="text-subtitle-1 mb-3 text-primary">
            <v-icon class="me-2">mdi-check-circle</v-icon>
            Status
          </h4>
          <div class="d-flex ga-4">
            <v-chip
              v-for="status in [
                'pending',
                'processing',
                'completed',
                'cancelled',
              ]"
              :key="status"
              :text="formatStatus(status)"
              density="compact"
              hide-details
              :color="getStatusColor(status)"
              :prepend-icon="getStatusIcon(status)"
              :variant="filters.status === status ? 'elevated' : 'outlined'"
              @click="
                () =>
                  (filters.status = filters.status === status ? null : status)
              "
            />
          </div>
        </div>

        <!-- Date Range Filter -->
        <div class="filter-section mb-6">
          <h4 class="text-subtitle-1 mb-3 text-primary">
            <v-icon class="me-2">mdi-calendar-range</v-icon>
            Date Range
          </h4>
          <div class="d-flex ga-4">
            <v-date-input
              v-model="filters.date_from"
              hide-details
              :max="filters.date_to"
              placeholder="From Date"
              prepend-icon=""
              clearable
            />
            <v-date-input
              v-model="filters.date_to"
              hide-details
              :min="filters.date_from"
              :max="new Date()"
              placeholder="To Date"
              prepend-icon=""
              clearable
            />
          </div>
        </div>

        <!-- Price Range Filter -->
        <div class="filter-section mb-6">
          <h4 class="text-subtitle-1 mb-3 text-primary">
            <v-icon class="me-2">mdi-currency-usd</v-icon>
            Total Price Range
          </h4>
          <div class="d-flex ga-4">
            <v-number-input
              v-model.number="filters.min_price"
              type="number"
              variant="outlined"
              density="compact"
              hide-details
              prefix="$"
              :min="1"
              :max="filters.max_price || 1000000"
              placeholder="Min Price"
              clearable
            />
            <v-number-input
              v-model.number="filters.max_price"
              type="number"
              variant="outlined"
              density="compact"
              hide-details
              prefix="$"
              :min="filters.min_price || 1"
              placeholder="Max Price"
              clearable
            />
          </div>
        </div>
      </v-card-text>

      <!-- Footer Actions -->
      <v-divider />
      <v-card-actions class="pa-4">
        <v-btn
          variant="outlined"
          color="grey"
          @click="clearFilters"
          :disabled="!hasActiveFilters"
        >
          <v-icon class="me-2">mdi-filter-off</v-icon>
          Clear All
        </v-btn>
        <v-spacer />
        <v-btn color="primary" @click="applyFilters">
          <v-icon class="me-2">mdi-check</v-icon>
          Apply
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-navigation-drawer>
</template>
