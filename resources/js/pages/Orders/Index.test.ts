import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { nextTick } from 'vue';
import OrdersIndex from '@/Pages/Orders/Index.vue';
import { useOrdersStore } from '@/Stores/orders';
import { Order } from '@/Types/entities';
import { setupMainAppMocks, createTestWrapperConfig } from '@/Tests/Helpers/mocks';

// Setup all main app mocks (layouts, router, services)
setupMainAppMocks();

// Mock child components used in Orders/Index
vi.mock('@/Components/General/SpinnerLoader.vue', () => ({
  default: {
    name: 'SpinnerLoader',
    template: '<div class="spinner-loader" data-testid="spinner-loader">{{ text }}</div>',
    props: ['text'],
  },
}));

vi.mock('@/Components/Orders/OrdersList.vue', () => ({
  default: {
    name: 'OrdersList',
    template: '<div class="orders-list" data-testid="orders-list"></div>',
    props: ['orders'],
    emits: ['view-order', 'update-order'],
  },
}));

vi.mock('@/Components/Orders/OrdersSummary.vue', () => ({
  default: {
    name: 'OrdersSummary',
    template: '<div class="orders-summary" data-testid="orders-summary"></div>',
    props: ['ordersCount', 'totalSpent'],
  },
}));

// Mock vue-router
const mockPush = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
  createRouter: vi.fn(),
  createWebHistory: vi.fn(),
  createWebHashHistory: vi.fn(),
  createMemoryHistory: vi.fn(),
}));

describe('Orders/Index.vue', () => {
  let pinia: any;
  let ordersStore: any;

  const mockOrders: Order[] = [
    {
      id: 1,
      user_id: 1,
      total_price: 99.99,
      status: 'completed',
      created_at: '2025-01-01T10:00:00Z',
      updated_at: '2025-01-01T10:00:00Z',
      products: [],
    },
    {
      id: 2,
      user_id: 1,
      total_price: 149.99,
      status: 'pending',
      created_at: '2025-01-02T11:00:00Z',
      updated_at: '2025-01-02T11:00:00Z',
      products: [],
    },
  ];

  beforeEach(() => {
    // Create a fresh Pinia instance for each test
    pinia = createPinia();
    setActivePinia(pinia);
    
    // Get the orders store and mock its methods
    ordersStore = useOrdersStore();
    ordersStore.fetchOrders = vi.fn();
    ordersStore.updateOrder = vi.fn();
    
    // Clear all mocks
    vi.clearAllMocks();
    mockPush.mockClear();
  });

  const createWrapper = (props = {}) => {
    return mount(OrdersIndex, {
      ...createTestWrapperConfig(pinia),
      props,
      global: {
        ...createTestWrapperConfig(pinia).global,
        stubs: {
          'v-container': { template: '<div class="v-container py-6"><slot /></div>' },
          'v-icon': { template: '<span class="v-icon" />' },
          'v-btn': { 
            template: '<button class="v-btn" @click="$emit(\'click\')"><slot /></button>',
            emits: ['click']
          },
        },
      },
    });
  };

  describe('Component Initialization', () => {
    it('renders correctly with default state', () => {
      const wrapper = createWrapper();
      
      expect(wrapper.exists()).toBe(true);
      expect(wrapper.find('.v-container').exists()).toBe(true);
      expect(wrapper.find('.py-6').exists()).toBe(true);
    });

    it('calls fetchOrders on mounted', async () => {
      createWrapper();
      
      // onMounted is called automatically during mount
      expect(ordersStore.fetchOrders).toHaveBeenCalledTimes(1);
    });

    it('displays orders summary component with correct props', () => {
      // Set store values directly on the refs
      ordersStore.orders = mockOrders;
      
      const wrapper = createWrapper();
      const ordersSummary = wrapper.findComponent({ name: 'OrdersSummary' });
      
      expect(ordersSummary.exists()).toBe(true);
      expect(ordersSummary.props('ordersCount')).toBe(mockOrders.length);
      expect(ordersSummary.props('totalSpent')).toBeCloseTo(249.98, 2);
    });
  });

  describe('Loading State', () => {
    it('shows spinner when loading', async () => {
      ordersStore.isLoading = true;
      ordersStore.orders = [];
      
      const wrapper = createWrapper();
      await nextTick();
      
      const spinner = wrapper.findComponent({ name: 'SpinnerLoader' });
      expect(spinner.exists()).toBe(true);
      expect(spinner.props('text')).toBe('Loading your orders...');
    });

    it('hides spinner when not loading', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = mockOrders;
      
      const wrapper = createWrapper();
      await nextTick();
      
      const spinner = wrapper.findComponent({ name: 'SpinnerLoader' });
      expect(spinner.exists()).toBe(false);
    });

    it('does not show orders list while loading', async () => {
      ordersStore.isLoading = true;
      ordersStore.orders = mockOrders;
      
      const wrapper = createWrapper();
      await nextTick();
      
      const ordersList = wrapper.findComponent({ name: 'OrdersList' });
      expect(ordersList.exists()).toBe(false);
    });
  });

  describe('Empty State', () => {
    it('shows empty state when no orders and not loading', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = [];
      
      const wrapper = createWrapper();
      await nextTick();
      
      const emptyState = wrapper.find('.text-center.py-12');
      expect(emptyState.exists()).toBe(true);
      
      const button = emptyState.find('.v-btn');
      expect(button.exists()).toBe(true);
      expect(button.text()).toContain('Start Shopping');
    });

    it('navigates to home page when start shopping button is clicked', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = [];
      
      const wrapper = createWrapper();
      await nextTick();
      
      const emptyState = wrapper.find('.text-center.py-12');
      const button = emptyState.find('.v-btn');
      await button.trigger('click');
      
      expect(mockPush).toHaveBeenCalledWith('/');
    });

    it('does not show empty state when loading', async () => {
      ordersStore.isLoading = true;
      ordersStore.orders = [];
      
      const wrapper = createWrapper();
      await nextTick();
      
      const emptyState = wrapper.find('.text-center.py-12');
      expect(emptyState.exists()).toBe(false);
    });

    it('does not show empty state when orders exist', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = mockOrders;
      
      const wrapper = createWrapper();
      await nextTick();
      
      const emptyState = wrapper.find('.text-center.py-12');
      expect(emptyState.exists()).toBe(false);
    });
  });

  describe('Orders List State', () => {
    it('shows orders list when orders exist and not loading', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = mockOrders;
      
      const wrapper = createWrapper();
      await nextTick();
      
      const ordersList = wrapper.findComponent({ name: 'OrdersList' });
      expect(ordersList.exists()).toBe(true);
      expect(ordersList.props('orders')).toEqual(mockOrders);
    });

    it('does not show orders list when loading', async () => {
      ordersStore.isLoading = true;
      ordersStore.orders = mockOrders;
      
      const wrapper = createWrapper();
      await nextTick();
      
      const ordersList = wrapper.findComponent({ name: 'OrdersList' });
      expect(ordersList.exists()).toBe(false);
    });

    it('does not show orders list when no orders', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = [];
      
      const wrapper = createWrapper();
      await nextTick();
      
      const ordersList = wrapper.findComponent({ name: 'OrdersList' });
      expect(ordersList.exists()).toBe(false);
    });
  });

  describe('Order Interactions', () => {
    it('handles view order event correctly', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = mockOrders;
      
      const wrapper = createWrapper();
      await nextTick();
      
      const ordersList = wrapper.findComponent({ name: 'OrdersList' });
      
      // Simulate view-order event from OrdersList component
      await ordersList.vm.$emit('view-order', 123);
      
      expect(mockPush).toHaveBeenCalledWith('/orders/123');
      expect(mockPush).toHaveBeenCalledTimes(1);
    });

    it('handles update order event correctly', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = mockOrders;
      
      const wrapper = createWrapper();
      await nextTick();
      
      const ordersList = wrapper.findComponent({ name: 'OrdersList' });
      const updateData = { status: 'cancelled' };
      
      // Simulate update-order event from OrdersList component
      await ordersList.vm.$emit('update-order', 456, updateData);
      
      expect(ordersStore.updateOrder).toHaveBeenCalledWith(456, updateData);
      expect(ordersStore.updateOrder).toHaveBeenCalledTimes(1);
    });

    it('calls viewOrder method correctly', async () => {
      const wrapper = createWrapper();
      const vm = wrapper.vm as any;
      
      // Call the viewOrder method directly
      vm.viewOrder(789);
      
      expect(mockPush).toHaveBeenCalledWith('/orders/789');
    });
  });

  describe('Component State Management', () => {
    it('correctly reacts to store state changes', async () => {
      const wrapper = createWrapper();
      
      // Initially loading
      ordersStore.isLoading = true;
      ordersStore.orders = [];
      await nextTick();
      
      expect(wrapper.findComponent({ name: 'SpinnerLoader' }).exists()).toBe(true);
      expect(wrapper.findComponent({ name: 'OrdersList' }).exists()).toBe(false);
      
      // Finished loading with orders
      ordersStore.isLoading = false;
      ordersStore.orders = mockOrders;
      await nextTick();
      
      expect(wrapper.findComponent({ name: 'SpinnerLoader' }).exists()).toBe(false);
      expect(wrapper.findComponent({ name: 'OrdersList' }).exists()).toBe(true);
    });

    it('updates orders summary when store values change', async () => {
      const wrapper = createWrapper();
      
      // Initial state
      ordersStore.orders = [];
      await nextTick();
      
      let ordersSummary = wrapper.findComponent({ name: 'OrdersSummary' });
      expect(ordersSummary.props('ordersCount')).toBe(0);
      expect(ordersSummary.props('totalSpent')).toBe(0);
      
      // Updated state
      ordersStore.orders = mockOrders;
      await nextTick();
      
      ordersSummary = wrapper.findComponent({ name: 'OrdersSummary' });
      expect(ordersSummary.props('ordersCount')).toBe(2);
      expect(ordersSummary.props('totalSpent')).toBeCloseTo(249.98, 2);
    });
  });

  describe('Edge Cases', () => {
    it('handles undefined or null orders gracefully', async () => {
      ordersStore.isLoading = false;
      ordersStore.orders = [];
      
      const wrapper = createWrapper();
      await nextTick();
      
      // Should show empty state when orders is empty
      const emptyState = wrapper.find('.text-center.py-12');
      expect(emptyState.exists()).toBe(true);
    });

    it('handles updateOrder method calls', () => {
      const wrapper = createWrapper();
      const vm = wrapper.vm as any;
      
      // Should not throw error with valid ID
      expect(() => vm.updateOrder(1, { status: 'completed' })).not.toThrow();
      expect(ordersStore.updateOrder).toHaveBeenCalledWith(1, { status: 'completed' });
    });

    it('handles updateOrder with empty data', () => {
      const wrapper = createWrapper();
      const vm = wrapper.vm as any;
      
      // Should not throw error
      expect(() => vm.updateOrder(1, {})).not.toThrow();
      expect(ordersStore.updateOrder).toHaveBeenCalledWith(1, {});
    });
  });

  describe('Method Testing', () => {
    it('updateOrder method calls store with correct parameters', () => {
      const wrapper = createWrapper();
      const vm = wrapper.vm as any;
      
      const orderData = { status: 'completed', total_price: 199.99 };
      vm.updateOrder(123, orderData);
      
      expect(ordersStore.updateOrder).toHaveBeenCalledWith(123, orderData);
      expect(ordersStore.updateOrder).toHaveBeenCalledTimes(1);
    });

    it('viewOrder method navigates with correct URL when called directly', () => {
      const wrapper = createWrapper();
      const vm = wrapper.vm as any;
      
      vm.viewOrder(456);
      
      expect(mockPush).toHaveBeenCalledWith('/orders/456');
      expect(mockPush).toHaveBeenCalledTimes(1);
    });
  });

  describe('Integration with Store', () => {
    it('uses store values correctly for reactive state', async () => {
      ordersStore.orders = mockOrders;
      ordersStore.isLoading = false;
      
      const wrapper = createWrapper();
      await nextTick();
      
      // Component should reflect the changes
      const ordersList = wrapper.findComponent({ name: 'OrdersList' });
      const ordersSummary = wrapper.findComponent({ name: 'OrdersSummary' });
      
      expect(ordersList.exists()).toBe(true);
      expect(ordersList.props('orders')).toEqual(mockOrders);
      expect(ordersSummary.props('ordersCount')).toBe(mockOrders.length);
      expect(ordersSummary.props('totalSpent')).toBeCloseTo(249.98, 2);
    });
  });

  describe('Conditional Rendering Logic', () => {
    it('renders only one main content section at a time', async () => {
      const wrapper = createWrapper();
      
      // Test loading state
      ordersStore.isLoading = true;
      ordersStore.orders = mockOrders;
      await nextTick();
      
      expect(wrapper.findComponent({ name: 'SpinnerLoader' }).exists()).toBe(true);
      expect(wrapper.findComponent({ name: 'OrdersList' }).exists()).toBe(false);
      expect(wrapper.find('.text-center.py-12').exists()).toBe(false);
      
      // Test empty state
      ordersStore.isLoading = false;
      ordersStore.orders = [];
      await nextTick();
      
      expect(wrapper.findComponent({ name: 'SpinnerLoader' }).exists()).toBe(false);
      expect(wrapper.findComponent({ name: 'OrdersList' }).exists()).toBe(false);
      expect(wrapper.find('.text-center.py-12').exists()).toBe(true);
      
      // Test orders list state
      ordersStore.isLoading = false;
      ordersStore.orders = mockOrders;
      await nextTick();
      
      expect(wrapper.findComponent({ name: 'SpinnerLoader' }).exists()).toBe(false);
      expect(wrapper.findComponent({ name: 'OrdersList' }).exists()).toBe(true);
      expect(wrapper.find('.text-center.py-12').exists()).toBe(false);
    });
  });

  describe('Component Structure', () => {
    it('maintains correct DOM structure', () => {
      const wrapper = createWrapper();
      
      // Check orders summary is present
      const ordersSummary = wrapper.findComponent({ name: 'OrdersSummary' });
      expect(ordersSummary.exists()).toBe(true);
    });

    it('applies correct CSS classes', () => {
      const wrapper = createWrapper();
      
      expect(wrapper.find('.v-container').classes()).toContain('py-6');
      
      // Check empty state classes
      ordersStore.orders = [];
      ordersStore.isLoading = false;
      const emptyWrapper = createWrapper();
      
      const emptyState = emptyWrapper.find('.text-center.py-12');
      expect(emptyState.exists()).toBe(true);
      expect(emptyState.classes()).toContain('text-center');
      expect(emptyState.classes()).toContain('py-12');
    });
  });
});
