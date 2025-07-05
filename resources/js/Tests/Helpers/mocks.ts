import { vi } from 'vitest';

/**
 * Common test mocks for Vue components
 * This file contains reusable mocks for testing Vue components
 */

// Mock the AuthLayout component
export const mockAuthLayout = () => {
  vi.mock('@/Layouts/AuthLayout.vue', () => ({
    default: {
      template: '<div class="auth-layout"><slot /></div>',
    },
  }));
};

// Mock the MainLayout component
export const mockMainLayout = () => {
  vi.mock('@/Layouts/MainLayout.vue', () => ({
    default: {
      template: '<div class="main-layout"><slot /></div>',
    },
  }));
};

// Mock all layouts
export const mockLayouts = () => {
  mockAuthLayout();
  mockMainLayout();
};

// Mock the router module
export const mockRouter = () => {
  vi.mock('@/Router', () => ({
    default: {
      push: vi.fn(),
      replace: vi.fn(),
      go: vi.fn(),
      back: vi.fn(),
      forward: vi.fn(),
    },
  }));
};

// Mock AuthService to avoid API calls
export const mockAuthService = () => {
  vi.mock('@/Services/AuthService', () => ({
    default: {
      login: vi.fn(),
      logout: vi.fn(),
      register: vi.fn(),
      user: vi.fn(),
      setToken: vi.fn(),
    },
  }));
};

// Mock CartService
export const mockCartService = () => {
  vi.mock('@/Services/CartService', () => ({
    default: {
      fetchCart: vi.fn(),
      addToCart: vi.fn(),
      removeFromCart: vi.fn(),
      clearCart: vi.fn(),
      checkoutCart: vi.fn(),
    },
  }));
};

// Mock ProductService
export const mockProductService = () => {
  vi.mock('@/Services/ProductService', () => ({
    default: {
      fetchProducts: vi.fn(),
      fetchProduct: vi.fn(),
      updateProduct: vi.fn(),
      uploadProductImage: vi.fn(),
    },
  }));
};

// Mock CategoryService
export const mockCategoryService = () => {
  vi.mock('@/Services/CategoryService', () => ({
    default: {
      fetchCategories: vi.fn(),
    },
  }));
};

// Mock OrderService
export const mockOrderService = () => {
  vi.mock('@/Services/OrderService', () => ({
    default: {
      fetchOrders: vi.fn(),
      fetchOrder: vi.fn(),
      updateOrder: vi.fn(),
    },
  }));
};

// Mock BaseService
export const mockBaseService = () => {
  vi.mock('@/Services/BaseService', () => ({
    BaseService: vi.fn().mockImplementation(() => ({
      client: {
        interceptors: {
          response: {
            use: vi.fn(),
          },
        },
      },
      send: vi.fn(),
    })),
  }));
};

// Mock all services
export const mockAllServices = () => {
  mockAuthService();
  mockCartService();
  mockProductService();
  mockCategoryService();
  mockOrderService();
  mockBaseService();
};

// Common mock setup for auth-related components
export const setupAuthMocks = () => {
  mockAuthLayout();
  mockRouter();
  mockAuthService();
};

// Common mock setup for main app components
export const setupMainAppMocks = () => {
  mockMainLayout();
  mockRouter();
  mockAllServices();
};

// Complete mock setup for all components
export const setupAllMocks = () => {
  mockLayouts();
  mockRouter();
  mockAllServices();
};

// Mock common Vue utilities
export const mockVueUtilities = () => {
  // Mock vue-router composables
  vi.mock('vue-router', async (importOriginal) => {
    const actual = await importOriginal<typeof import('vue-router')>();
    return {
      ...actual,
      useRouter: () => ({
        push: vi.fn(),
        replace: vi.fn(),
        go: vi.fn(),
        back: vi.fn(),
        forward: vi.fn(),
      }),
      useRoute: () => ({
        path: '/',
        params: {},
        query: {},
        meta: {},
        name: 'test-route',
      }),
    };
  });
};

// Mock Pinia stores
export const mockStores = () => {
  vi.mock('@/Stores/auth', () => ({
    useAuthStore: vi.fn(() => ({
      user: null,
      token: null,
      isAuthenticated: false,
      login: vi.fn(),
      logout: vi.fn(),
      register: vi.fn(),
      fetchUser: vi.fn(),
    })),
  }));

  vi.mock('@/Stores/cart', () => ({
    useCartStore: vi.fn(() => ({
      items: [],
      total: 0,
      itemCount: 0,
      addItem: vi.fn(),
      removeItem: vi.fn(),
      updateQuantity: vi.fn(),
      clear: vi.fn(),
      fetchCart: vi.fn(),
    })),
  }));

  vi.mock('@/Stores/orders', () => ({
    useOrdersStore: vi.fn(() => ({
      orders: [],
      isLoading: false,
      ordersCount: 0,
      totalSpent: 0,
      ordersByStatus: {},
      fetchOrders: vi.fn(),
      fetchOrder: vi.fn(),
      updateOrder: vi.fn(),
    })),
  }));
};

// Mock validation utilities
export const mockValidationUtils = () => {
  vi.mock('@/Utils/validation', () => ({
    validateEmail: vi.fn((email: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)),
    validatePassword: vi.fn((password: string) => password.length >= 8),
  }));
};

// Mock external libraries
export const mockExternalLibraries = () => {
  // Mock axios if needed
  vi.mock('axios', () => ({
    default: {
      create: vi.fn(() => ({
        interceptors: {
          request: { use: vi.fn() },
          response: { use: vi.fn() },
        },
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
      })),
      get: vi.fn(),
      post: vi.fn(),
      put: vi.fn(),
      patch: vi.fn(),
      delete: vi.fn(),
    },
  }));
};

// Ultimate setup - mocks everything
export const setupCompleteMocks = () => {
  setupAllMocks();
  mockVueUtilities();
  mockStores();
  mockValidationUtils();
  mockExternalLibraries();
};

// Mock for creating test wrappers with common configuration
export const createTestWrapperConfig = (pinia: any, additionalMocks: Record<string, any> = {}) => ({
  global: {
    plugins: [pinia],
    mocks: {
      $router: {
        push: vi.fn(),
      },
      ...additionalMocks,
    },
  },
});
