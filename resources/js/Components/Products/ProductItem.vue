<script setup lang="ts">
import { Product } from '@/Types/entities';

defineProps<{
  product: Product;
}>();

const emit = defineEmits<{
  (e: 'add'): void;
  (e: 'view'): void;
}>();
</script>

<template>
  <v-card class="d-flex flex-column" height="100%">
    <div class="bg-white" height="300px">
      <v-img height="300" :src="product.image" contain>
        <template #error>
          <div
            class="d-flex align-center justify-center fill-height bg-grey-lighten-3"
          >
            <v-icon icon="mdi-image-broken" color="grey" size="64" />
          </div>
        </template>
      </v-img>
    </div>

    <v-chip rounded="sm" class="text-uppercase">
      <v-icon icon="mdi-tag" size="small" class="me-1"></v-icon>
      {{ product.category.name }}
    </v-chip>

    <v-card-title
      class="text-pre-wrap mb-4 line-clamp-2"
      style="min-height: 80px; max-height: 80px"
    >
      {{ product.title }}
    </v-card-title>

    <v-card-subtitle
      class="text-subtitle-2 text-grey text-pre-wrap line-clamp-3"
      style="min-height: 80px; max-height: 80px"
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

    <v-card-actions class="mt-auto flex-column">
      <v-btn
        prepend-icon="mdi-cart"
        text="Add to Cart"
        block
        variant="outlined"
        @click="emit('add')"
      />

      <v-btn
        prepend-icon="mdi-eye"
        text="View Details"
        block
        @click="emit('view')"
        variant="text"
        color="warning"
      />
    </v-card-actions>
  </v-card>
</template>
