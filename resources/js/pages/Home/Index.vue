<template>
  <MainLayout>
    <v-container fluid>
      <ProductsList 
        :filtered-products="filteredProducts.length > 0 ? filteredProducts : undefined"
        :loading="loading" 
        :enable-infinite-scroll="!hasActiveFilters"
        @clear-filters="clearFilters"
      />
    </v-container>
  </MainLayout>
</template>

<script setup lang="ts">
import ProductsList from '@/Components/Products/ProductsList.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useProductsStore } from '@/Stores/products';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { Product } from '@/Types/entities';

const productsStore = useProductsStore();
const { products, loading } = storeToRefs(productsStore);

// Filtering state
const searchQuery = ref('');
const selectedCategory = ref<string>('');

const hasActiveFilters = computed(() => {
  return searchQuery.value.trim() !== '' || selectedCategory.value !== '';
});

const filteredProducts = computed(() => {
  if (!hasActiveFilters.value) {
    return [];
  }

  let filtered = products.value;

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(product => 
      product.title.toLowerCase().includes(query) ||
      product.description.toLowerCase().includes(query)
    );
  }

  // Filter by category
  if (selectedCategory.value) {
    filtered = filtered.filter(product => 
      product.category.name.toLowerCase() === selectedCategory.value.toLowerCase()
    );
  }

  return filtered;
});

function clearFilters() {
  searchQuery.value = '';
  selectedCategory.value = '';
}

onMounted(() => {
  // Initial fetch if no products are loaded
  if (products.value.length === 0) {
    productsStore.fetchProducts();
  }
});
</script>

<style scoped>
.hero-section {
  position: relative;
}

.hero-card {
  position: relative;
  overflow: hidden;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1;
}

.stats-section {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.categories-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.category-card {
  transition: all 0.3s ease;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}

.category-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.newsletter-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.products-section {
  min-height: 50vh;
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .hero-card {
    height: 400px !important;
  }

  .display-1 {
    font-size: 2.5rem !important;
  }
}

@media (max-width: 600px) {
  .hero-card {
    height: 300px !important;
  }

  .display-1 {
    font-size: 2rem !important;
  }

  .text-h6 {
    font-size: 1.1rem !important;
  }
}
</style>
