import CartService from '@/Services/CartService';
import { Cart, Product } from '@/Types/entities';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useCartStore = defineStore('cart', () => {
  const cart = ref<Cart | null>(null);
  const isCheckingOut = ref(false);

  const cartProducts = computed(() => cart.value?.products || []);

  const cartTotal = computed(() => {
    if (!cart.value) {
      return 0;
    }
    return cart.value.products.reduce((total, product) => {
      return total + product.price * product.pivot.quantity;
    }, 0);
  });

  const cartCount = computed(() => {
    if (!cart.value) {
      return 0;
    }
    return cart.value.products.reduce((count, product) => {
      return count + product.pivot.quantity;
    }, 0);
  });

  async function fetchCart() {
    try {
      if (cart.value) {
        return; // If cart is already fetched, no need to fetch again
      }
      const response = await CartService.fetchCart();
      cart.value = response.cart;
    } catch {
      cart.value = null;
    }
  }

  async function clearCart() {
    if (!cart.value) {
      return;
    }
    await CartService.clearCart();
    cart.value.products = [];
  }

  async function checkoutCart() {
    if (!cart.value) {
      return;
    }
    try {
      isCheckingOut.value = true;
      const order = await CartService.checkoutCart();
      cart.value.products = [];
      return order;
    } catch (error) {
      throw error;
    } finally {
      isCheckingOut.value = false;
    }
  }

  async function addToCart(product: Product, quantity?: number) {
    if (!cart.value) {
      await fetchCart();
    }
    if (!cart.value) {
      return;
    }

    const existingProduct = cart.value.products.find(
      (item) => item.id === product.id,
    );

    if (!quantity && existingProduct) {
      quantity = existingProduct.pivot.quantity + 1; // Default to 1 if no quantity is specified
    } else if (!quantity) {
      quantity = 1; // Default to 1 if no quantity is specified and product is not in cart
    }

    await CartService.addToCart(product.id, quantity);

    if (existingProduct) {
      existingProduct.pivot.quantity = quantity;
    } else {
      const newProduct = { ...product, pivot: { quantity } };
      cart.value.products.push(newProduct);
    }
  }

  function removeFromCart(product: Product) {
    if (!cart.value) {
      return;
    }
    CartService.removeFromCart(product.id);
    cart.value.products = cart.value.products.filter(
      (item) => item.id !== product.id,
    );
  }

  function resetCart() {
    cart.value = null;
  }

  return {
    cart,
    cartProducts,
    cartTotal,
    cartCount,
    isCheckingOut,
    resetCart,
    fetchCart,
    clearCart,
    addToCart,
    checkoutCart,
    removeFromCart,
  };
});
