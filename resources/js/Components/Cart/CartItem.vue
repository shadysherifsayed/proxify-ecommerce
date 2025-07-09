<script setup lang="ts">
import { CartProduct } from '@/Types/entities';

defineProps<{
  product: CartProduct;
}>();

const emit = defineEmits<{
  (e: 'remove'): void;
  (e: 'increase'): void;
  (e: 'decrease'): void;
}>();
</script>

<template>
  <v-card class="d-flex flex-row align-center pa-3 mb-3" elevation="0">
    <v-img
      height="80"
      width="80"
      :src="product.image"
      cover
      class="flex-shrink-0 rounded"
    >
      <template #error>
        <div
          class="d-flex align-center justify-center fill-height bg-grey-lighten-3"
        >
          <v-icon icon="mdi-image-broken" color="grey" size="64" />
        </div>
      </template>
    </v-img>

    <div class="d-flex flex-column flex-grow-1 ml-4">
      <div class="d-flex justify-space-between align-start">
        <div class="flex-grow-1 pr-2">
          <h3 class="text-subtitle-1 font-weight-medium mb-1 line-clamp-2">
            {{ product.title }}
          </h3>
          <div class="text-body-2 text-grey-darken-1 mb-2">
            Price: ${{ product.price }}
          </div>
        </div>

        <v-btn
          icon="mdi-delete"
          size="small"
          color="error"
          variant="text"
          data-testid="remove-btn"
          @click="emit('remove')"
        />
      </div>

      <div class="d-flex justify-space-between align-center">
        <div class="d-flex align-center">
          <v-btn
            icon="mdi-minus"
            size="small"
            variant="outlined"
            :disabled="product.pivot.quantity <= 1"
            data-testid="decrease-btn"
            @click="emit('decrease')"
          />
          <span class="mx-3 text-h6 font-weight-medium">
            {{ product.pivot.quantity }}
          </span>
          <v-btn
            icon="mdi-plus"
            size="small"
            variant="outlined"
            data-testid="increase-btn"
            @click="emit('increase')"
          />
        </div>

        <div class="text-h6 font-weight-bold text-primary">
          ${{ (product.price * product.pivot.quantity).toFixed(2) }}
        </div>
      </div>
    </div>
  </v-card>
</template>
