<template>
  <v-card
    :disabled="loading"
    :loading="loading"
    class="d-flex flex-column"
  >
    <template v-slot:loader="{ isActive }">
      <v-progress-linear
        :active="isActive"
        color="deep-purple"
        height="4"
        indeterminate
      />
    </template>

    <v-img height="300" :src="product.image" cover />

    <v-card-title
      class="text-pre-wrap mb-4"
      style="
        min-height: 80px;
        max-height: 80px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        line-clamp: 3;
      "
    >
      {{ product.title }}
    </v-card-title>

    <v-card-subtitle
      class="text-subtitle-2 text-grey text-pre-wrap"
      style="
        min-height: 80px;
        max-height: 80px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        line-clamp: 3;
      "
    >
      {{ product.description }}
    </v-card-subtitle>

    <v-card-text class="flex-grow-1">
      <div class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <v-rating
            :model-value="product.rating"
            color="amber"
            density="compact"
            size="small"
            half-increments
            readonly
          />

          <div class="text-grey ms-4">
            {{ product.rating }} ({{ product.reviews_count ?? 0 }})
          </div>
        </div>
        <div class="text-subtitle-1">{{ product.price }} $</div>
      </div>
    </v-card-text>

    <v-divider class="mx-4 mb-1"></v-divider>

    <v-card-actions class="mt-auto">
      <v-btn text="Add to Cart" block @click="emit('add')"></v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup lang="ts">
import { Product } from '@/Types/entities';

defineProps<{
  loading?: boolean;
  product: Product;
}>();

const emit = defineEmits<{
  (e: 'add'): void;
}>();
</script>
