<script lang="ts" setup>
import { Product } from '@/Types/entities';

defineProps<{
  product: Product;
}>();
</script>

<template>
  <v-card elevation="3" class="h-100 product-info-card">
    <v-card-text class="pa-6">
      <!-- Title -->
      <div class="mb-6">
        <v-label class="text-subtitle-1 font-weight-medium mb-3 d-block"
          >Product Title</v-label
        >
        <div>
          <h2 class="text-h5 font-weight-bold text-primary">
            {{ product.title }}
          </h2>
        </div>
      </div>

      <!-- Price -->
      <div class="mb-6">
        <v-label class="text-subtitle-1 font-weight-medium mb-3 d-block"
          >Price</v-label
        >
        <div>
          <div class="text-h4 text-success font-weight-bold">
            ${{ product.price.toFixed(2) }}
          </div>
        </div>
      </div>

      <!-- Category -->
      <div class="mb-6">
        <v-label class="text-subtitle-1 font-weight-medium mb-3 d-block"
          >Category</v-label
        >
        <div>
          <v-chip
            color="primary"
            variant="elevated"
            size="large"
            prepend-icon="mdi-tag"
          >
            {{ product.category.name }}
          </v-chip>
        </div>
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
        <div>
          <p class="text-body-1 text-grey-darken-2 line-height-relaxed">
            {{ product.description }}
          </p>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>
