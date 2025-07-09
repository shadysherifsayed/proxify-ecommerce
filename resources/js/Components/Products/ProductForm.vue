<script lang="ts" setup>
import { Category, Product } from '@/Types/entities';
import { watch } from 'vue';

const props = defineProps<{
  product: Product;
  validationErrors: Record<string, string[]>;
  categories: Category[];
}>();

const modelValue = defineModel<{
  title: string;
  description: string;
  price: number;
  category_id: number;
}>('modelValue', {
  default: () => ({
    title: '',
    description: '',
    price: 0,
    category_id: 0,
  }),
});

// Initialize form data when product prop changes
watch(
  () => props.product,
  (newProduct) => {
    if (newProduct) {
      modelValue.value = {
        title: newProduct.title,
        description: newProduct.description,
        price: newProduct.price,
        category_id: newProduct.category.id,
      };
    }
  },
  { immediate: true },
);
</script>

<template>
  <v-card elevation="0" class="h-100 product-info-card">
    <v-card-text class="pa-6">
      <!-- Title -->
      <div class="mb-6">
        <v-label class="text-subtitle-1 font-weight-medium mb-3 d-block"
          >Product Title</v-label
        >
        <v-text-field
          v-model="modelValue.title"
          variant="outlined"
          density="comfortable"
          :error-messages="validationErrors.title"
          placeholder="Enter product title"
        />
      </div>

      <!-- Price -->
      <div class="mb-6">
        <v-label class="text-subtitle-1 font-weight-medium mb-3 d-block"
          >Price</v-label
        >
        <v-text-field
          v-model.number="modelValue.price"
          variant="outlined"
          density="comfortable"
          type="number"
          step="0.01"
          min="0"
          prepend-inner-icon="mdi-currency-usd"
          :error-messages="validationErrors.price"
          placeholder="0.00"
        />
      </div>

      <!-- Category -->
      <div class="mb-6">
        <v-label class="text-subtitle-1 font-weight-medium mb-3 d-block"
          >Category</v-label
        >
        <v-select
          v-model="modelValue.category_id"
          :items="categories"
          item-title="name"
          item-value="id"
          variant="outlined"
          density="comfortable"
          prepend-inner-icon="mdi-tag"
          :error-messages="validationErrors.category_id"
          placeholder="Select a category"
        />
      </div>

      <!-- Rating -->
      <div class="mb-6">
        <v-label class="text-subtitle-1 font-weight-medium mb-3 d-block"
          >Customer Rating</v-label
        >
        <div class="d-flex align-center">
          <v-rating
            :model-value="product.rating"
            readonly
            density="comfortable"
            color="amber"
            half-increments
            size="large"
          />
          <div class="ml-4">
            <div class="text-h6 font-weight-bold">{{ product.rating }}/5</div>
            <div class="text-body-2 text-grey-darken-1">
              ({{ product.reviews_count }} reviews)
            </div>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="mb-4">
        <v-label class="text-subtitle-1 font-weight-medium mb-3 d-block"
          >Description</v-label
        >
        <v-textarea
          v-model="modelValue.description"
          variant="outlined"
          rows="5"
          :error-messages="validationErrors.description"
          placeholder="Enter product description"
        />
      </div>
    </v-card-text>
  </v-card>
</template>
