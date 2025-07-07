import CartItem from '@/Components/Cart/CartItem.vue';
import type { CartProduct } from '@/Types/entities';
import { shallowMount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { createVuetify } from 'vuetify';

describe('CartItem.vue', () => {
  const vuetify = createVuetify();

  const mockProduct: CartProduct = {
    id: 1,
    title: 'Test Product',
    description: 'Test description',
    price: 29.99,
    image: 'https://example.com/image.jpg',
    reviews_count: 10,
    rating: 4.5,
    category_id: 1,
    created_at: '2023-01-01',
    updated_at: '2023-01-01',
    category: {
      id: 1,
      name: 'Test Category',
      created_at: '2023-01-01',
      updated_at: '2023-01-01',
    },
    pivot: {
      quantity: 2,
    },
  };

  const createWrapper = (product = mockProduct) => {
    return shallowMount(CartItem, {
      props: { product },
      global: {
        plugins: [vuetify],
      },
    });
  };

  it('renders product information correctly', () => {
    const wrapper = createWrapper();

    expect(wrapper.text()).toContain('Test Product');
    expect(wrapper.text()).toContain('$29.99');
    expect(wrapper.text()).toContain('2');
    expect(wrapper.text()).toContain('$59.98'); // price * quantity
  });

  it('emits remove event when delete button is clicked', async () => {
    const wrapper = createWrapper();

    const deleteBtn = wrapper.find('[data-testid="remove-btn"]');
    await deleteBtn.trigger('click');

    expect(wrapper.emitted('remove')).toBeTruthy();
  });

  it('emits increase event when plus button is clicked', async () => {
    const wrapper = createWrapper();

    const plusBtn = wrapper.find('[data-testid="increase-btn"]');
    await plusBtn.trigger('click');

    expect(wrapper.emitted('increase')).toBeTruthy();
  });

  it('emits decrease event when minus button is clicked', async () => {
    const wrapper = createWrapper();

    const minusBtn = wrapper.find('[data-testid="decrease-btn"]');
    await minusBtn.trigger('click');

    expect(wrapper.emitted('decrease')).toBeTruthy();
  });

  it('disables minus button when quantity is 1', () => {
    const productWithQuantity1 = {
      ...mockProduct,
      pivot: { quantity: 1 },
    };

    const wrapper = createWrapper(productWithQuantity1);
    const minusBtn = wrapper.find('[data-testid="decrease-btn"]');

    expect(minusBtn.attributes('disabled')).toBeDefined();
  });

  it('displays correct total price', () => {
    const wrapper = createWrapper();

    // 29.99 * 2 = 59.98
    expect(wrapper.text()).toContain('$59.98');
  });
});
