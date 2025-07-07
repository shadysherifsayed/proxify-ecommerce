<script setup lang="ts">
import { Category, ProductFilters, Sort } from '@/Types/entities';
import { computed, ref } from 'vue';

defineProps<{
  categories: Category[];
}>();

// Emits
const emit = defineEmits<{
  apply: [filters: ProductFilters, sort: Sort];
  clear: [];
}>();

const filters = ref<ProductFilters>({
  search: '',
  categories: [],
  min_price: null,
  max_price: null,
  min_rating: 0,
});

const sortBy = ref<
  'newest' | 'price_low' | 'price_high' | 'rating_high' | 'rating_low'
>('newest');

const sort = ref<Sort>({
  field: 'created_at',
  direction: 'desc',
});

// Rating options
const ratingOptions = [
  { value: 0, label: 'All Ratings', icon: 'mdi-star-outline' },
  { value: 1, label: '1+ Stars', icon: 'mdi-star' },
  { value: 2, label: '2+ Stars', icon: 'mdi-star' },
  { value: 3, label: '3+ Stars', icon: 'mdi-star' },
  { value: 4, label: '4+ Stars', icon: 'mdi-star' },
];

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
    value: 'price_low',
    label: 'Price: Low to High',
    icon: 'mdi-arrow-up',
    onClick: () => {
      sortBy.value = 'price_low';
      sort.value = { field: 'price', direction: 'asc' };
    },
  },
  {
    value: 'price_high',
    label: 'Price: High to Low',
    icon: 'mdi-arrow-down',
    onClick: () => {
      sortBy.value = 'price_high';
      sort.value = { field: 'price', direction: 'desc' };
    },
  },
  {
    value: 'rating_high',
    label: 'Highest Rated',
    icon: 'mdi-star',
    onClick: () => {
      sortBy.value = 'rating_high';
      sort.value = { field: 'rating', direction: 'desc' };
    },
  },
  {
    value: 'rating_low',
    label: 'Lowest Rated',
    icon: 'mdi-star-outline',
    onClick: () => {
      sortBy.value = 'rating_low';
      sort.value = { field: 'rating', direction: 'asc' };
    },
  },
]);

const hasActiveFilters = computed(() => {
  return (
    filters.value.search ||
    filters.value.categories.length > 0 ||
    filters.value.min_price !== null ||
    filters.value.max_price !== null ||
    filters.value.min_rating > 0 ||
    sortBy.value !== 'newest'
  );
});

// Methods
function applyFilters() {
  emit('apply', filters.value, sort.value);
}

function clearFilters() {
  filters.value = {
    search: '',
    categories: [],
    min_price: null,
    max_price: null,
    min_rating: 0,
  };
  sortBy.value = 'newest';
  sort.value = { field: 'created_at', direction: 'desc' };
  emit('clear');
}

// Generate star display for rating
const getStarDisplay = (rating: number) => {
  return Array.from({ length: 5 }, (_, i) =>
    i < rating ? 'mdi-star' : 'mdi-star-outline',
  );
};
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
        Filter and sort the available products
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

        <!-- Search Filter -->
        <div class="filter-section mb-6">
          <h4 class="text-subtitle-1 mb-3 text-primary">
            <v-icon class="me-2">mdi-magnify</v-icon>
            Search Products
          </h4>
          <v-text-field
            v-model="filters.search"
            placeholder="Search in title and description..."
            variant="outlined"
            density="compact"
            prepend-inner-icon="mdi-magnify"
            clearable
            hide-details
          />
        </div>

        <!-- Category Filter -->
        <div class="filter-section mb-6">
          <h4 class="text-subtitle-1 mb-3 text-primary">
            <v-icon class="me-2">mdi-tag-multiple</v-icon>
            Categories
          </h4>
          <div class="category-grid">
            <v-checkbox
              v-for="category in categories"
              :key="category.id"
              v-model="filters.categories"
              :value="category.id"
              :label="category.name"
              density="compact"
              hide-details
              color="primary"
              class="category-checkbox"
            >
              <template #label>
                <div class="d-flex align-center justify-space-between w-100">
                  <span>{{ category.name }}</span>
                </div>
              </template>
            </v-checkbox>
          </div>
        </div>

        <!-- Price Range Filter -->
        <div class="filter-section mb-6">
          <h4 class="text-subtitle-1 mb-3 text-primary">
            <v-icon class="me-2">mdi-currency-usd</v-icon>
            Price Range
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
            />
          </div>
        </div>

        <!-- Rating Filter -->
        <div class="filter-section mb-6">
          <h4 class="text-subtitle-1 mb-3 text-primary">
            <v-icon class="me-2">mdi-star</v-icon>
            Minimum Rating
          </h4>
          <v-radio-group
            v-model="filters.min_rating"
            density="compact"
            color="primary"
            hide-details
          >
            <v-radio
              v-for="option in ratingOptions"
              :key="option.value"
              :value="option.value"
              class="rating-radio"
            >
              <template #label>
                <div class="d-flex align-center">
                  <div class="stars me-2">
                    <v-icon
                      v-for="(star, index) in getStarDisplay(option.value || 5)"
                      :key="index"
                      :icon="star"
                      size="16"
                      :color="option.value > 0 ? 'amber' : 'grey-lighten-1'"
                    />
                  </div>
                  <span class="rating-label">{{ option.label }}</span>
                </div>
              </template>
            </v-radio>
          </v-radio-group>
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
