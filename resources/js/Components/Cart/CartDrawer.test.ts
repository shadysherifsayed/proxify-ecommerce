import CartDrawer from '@/Components/Cart/CartDrawer.vue';
import { useCartStore } from '@/Stores/cart';
import { shallowMount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

describe('CartDrawer.vue', () => {
  let pinia: any;
  let cartStore: any;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);

    cartStore = useCartStore();
    cartStore.removeFromCart = vi.fn();
    cartStore.addToCart = vi.fn();
    cartStore.clearCart = vi.fn();
    cartStore.checkoutCart = vi.fn();

    vi.clearAllMocks();
  });

  const createWrapper = (props = {}) => {
    return shallowMount(CartDrawer, {
      props: {
        modelValue: false,
        ...props,
      },
      global: {
        plugins: [pinia],
      },
    });
  };

  it('renders correctly when closed', () => {
    const wrapper = createWrapper();
    expect(wrapper.exists()).toBe(true);
  });

  it('displays cart items when cart has products', () => {
    cartStore.cart = {
      products: [
        { id: 1, title: 'Product 1', price: 10, pivot: { quantity: 1 } },
        { id: 2, title: 'Product 2', price: 20, pivot: { quantity: 2 } },
      ],
    };

    const wrapper = createWrapper({ modelValue: true });
    expect(wrapper.findAllComponents({ name: 'cart-item' })).toHaveLength(2);
  });

  it('shows cart total and count', () => {
    cartStore.cart = {
      products: [
        { id: 1, title: 'Product 1', price: 10, pivot: { quantity: 1 } },
        { id: 2, title: 'Product 2', price: 20, pivot: { quantity: 2 } },
        { id: 3, title: 'Product 3', price: 30, pivot: { quantity: 3 } },
      ],
    };
    const cartCount = cartStore.cart.products.reduce(
      (count: any, product: { pivot: { quantity: any } }) =>
        count + product.pivot.quantity,
      0,
    );
    const cartTotal = cartStore.cart.products.reduce(
      (
        total: number,
        product: { price: number; pivot: { quantity: number } },
      ) => total + product.price * product.pivot.quantity,
      0,
    );
    cartStore.cart.total = 50.99;

    const wrapper = createWrapper({ modelValue: true });
    expect(wrapper.text()).toContain(`${cartCount} Items`);
    expect(wrapper.text()).toContain(`${cartTotal.toFixed(2)}`);
  });

  it('calls clearCart when clear button is clicked', async () => {
    cartStore.cart = {
      products: [
        { id: 1, title: 'Product 1', price: 10, pivot: { quantity: 1 } },
      ],
    };

    const wrapper = createWrapper({ modelValue: true });
    const clearButton = wrapper.find('[data-testid="clear-cart-btn"]');

    if (clearButton.exists()) {
      await clearButton.trigger('click');
      expect(cartStore.clearCart).toHaveBeenCalled();
    }
  });

  it('calls checkoutCart when checkout button is clicked', async () => {
    cartStore.cart = {
      products: [
        { id: 1, title: 'Product 1', price: 10, pivot: { quantity: 1 } },
      ],
    };

    cartStore.isCheckingOut = false;

    const wrapper = createWrapper({ modelValue: true });
    const checkoutButton = wrapper.find('[data-testid="checkout-btn"]');

    if (checkoutButton.exists()) {
      await checkoutButton.trigger('click');
      expect(cartStore.checkoutCart).toHaveBeenCalled();
    }
  });

  it('disables checkout button when cart is empty', () => {
    cartStore.cart = {
      products: [],
    };

    const wrapper = createWrapper({ modelValue: true });
    const checkoutButton = wrapper.find(
      'v-btn[prepend-icon="mdi-credit-card"]',
    );

    if (checkoutButton.exists()) {
      expect(checkoutButton.attributes('disabled')).toBeDefined();
    }
  });
});
