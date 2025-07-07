<script lang="ts" setup>
import EmptyState from '@/Components/General/EmptyState.vue';
import { useCartStore } from '@/Stores/cart';
import { storeToRefs } from 'pinia';
import CartItem from './CartItem.vue';

const modelValue = defineModel<boolean>({
  default: false,
  type: Boolean,
});

const cartStore = useCartStore();
const { cartProducts, cartTotal, cartCount, isCheckingOut } =
  storeToRefs(cartStore);
</script>

<template>
  <v-navigation-drawer
    v-model="modelValue"
    location="right"
    scrim
    temporary
    width="500"
  >
    <v-card
      flat
      :rounded="0"
      :border="0"
      class="d-flex flex-column h-100 overflow-hidden"
    >
      <v-card-title class="text-h6">Cart</v-card-title>
      <v-card-subtitle class="text-subtitle-1 text-grey">
        Your shopping cart items
      </v-card-subtitle>
      <v-divider class="my-2"></v-divider>
      <v-card-text class="flex-grow-1 overflow-auto py-0">
        <v-list density="compact">
          <CartItem
            v-for="(product, index) in cartProducts"
            :key="index"
            :product="product"
            @remove="cartStore.removeFromCart(product)"
            @increase="
              cartStore.addToCart(product, (product.pivot?.quantity || 0) + 1)
            "
            @decrease="
              cartStore.addToCart(
                product,
                Math.max(1, (product.pivot?.quantity || 1) - 1),
              )
            "
          />
        </v-list>
        <EmptyState
          v-if="cartCount === 0 && !isCheckingOut"
          title="Your Cart is Empty"
          description="Add items to your cart to proceed with checkout."
          icon="mdi-cart-outline"
        />
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions class="pa-0">
        <div class="w-100 pa-4">
          <!-- Cart Summary -->
          <div class="d-flex justify-space-between align-center mb-3">
            <div class="text-body-1 text-grey-darken-2">
              <v-icon icon="mdi-cart" size="small" class="mr-2"></v-icon>
              {{ cartCount }}
              {{ cartCount === 1 ? 'Item' : 'Items' }}
            </div>
            <v-btn
              v-if="cartCount > 0"
              variant="text"
              color="error"
              size="small"
              prepend-icon="mdi-delete-sweep"
              data-testid="clear-cart-btn"
              @click="cartStore.clearCart"
            >
              Clear Cart
            </v-btn>
          </div>

          <!-- Total Price Section -->
          <div class="bg-primary-lighten-5 rounded-lg pa-3 mb-4">
            <div class="d-flex justify-space-between align-center">
              <span class="text-h6 font-weight-medium text-primary">
                Total Amount
              </span>
              <span class="text-h5 font-weight-bold text-primary">
                ${{ cartTotal.toFixed(2) }}
              </span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex flex-column gap-2">
            <v-btn
              block
              color="primary"
              size="large"
              variant="elevated"
              :disabled="cartCount === 0"
              :loading="isCheckingOut"
              prepend-icon="mdi-credit-card"
              class="text-none font-weight-bold"
              data-testid="checkout-btn"
              @click="cartStore.checkoutCart"
            >
              Proceed to Checkout
            </v-btn>
          </div>
        </div>
      </v-card-actions>
    </v-card>
  </v-navigation-drawer>
</template>
