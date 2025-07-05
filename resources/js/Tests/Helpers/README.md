# Test Helpers - Mocks

This directory contains reusable test mocks and utilities for testing Vue components.

## Files

- `mocks.ts` - Main mocks file with comprehensive test utilities
- `mocks.example.ts` - Example usage patterns (not executed, just for reference)

## Quick Start

### Basic Auth Components
For most auth-related components:

```typescript
import { setupAuthMocks, createTestWrapperConfig } from '@/Tests/Helpers/mocks';

setupAuthMocks();
// Sets up: AuthLayout, Router, AuthService
```

### Main App Components
For components using the main layout and services:

```typescript
import { setupMainAppMocks } from '@/Tests/Helpers/mocks';

setupMainAppMocks();
// Sets up: MainLayout, Router, All Services
```

### Everything
For comprehensive testing:

```typescript
import { setupCompleteMocks } from '@/Tests/Helpers/mocks';

setupCompleteMocks();
// Sets up: All layouts, router, services, stores, utilities, external libs
```

## Available Mocks

### Layout Mocks
- `mockAuthLayout()` - Mocks `@/Layouts/AuthLayout.vue`
- `mockMainLayout()` - Mocks `@/Layouts/MainLayout.vue`
- `mockLayouts()` - Mocks all layout components

### Service Mocks
- `mockAuthService()` - Mocks `@/Services/AuthService`
- `mockCartService()` - Mocks `@/Services/CartService`
- `mockProductService()` - Mocks `@/Services/ProductService`
- `mockCategoryService()` - Mocks `@/Services/CategoryService`
- `mockOrderService()` - Mocks `@/Services/OrderService`
- `mockBaseService()` - Mocks `@/Services/BaseService`
- `mockAllServices()` - Mocks all service classes

### Router & Navigation
- `mockRouter()` - Mocks `@/Router` module
- `mockVueUtilities()` - Mocks Vue Router composables (`useRouter`, `useRoute`)

### Store Mocks
- `mockStores()` - Mocks Pinia stores (`auth`, `cart`)

### Utility Mocks
- `mockValidationUtils()` - Mocks `@/Utils/validation`
- `mockExternalLibraries()` - Mocks external libs like `axios`

### Pre-configured Setups
- `setupAuthMocks()` - Auth layout + router + auth service
- `setupMainAppMocks()` - Main layout + router + all services
- `setupAllMocks()` - All layouts + router + all services
- `setupCompleteMocks()` - Everything (ultimate setup)

## Usage Examples

### Standard Component Test

```typescript
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { setupAuthMocks, createTestWrapperConfig } from '@/Tests/Helpers/mocks';
import YourComponent from '@/Pages/YourComponent.vue';

// Setup mocks for auth components
setupAuthMocks();

describe('YourComponent', () => {
  let pinia: any;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
  });

  const createWrapper = (props = {}) => {
    return shallowMount(YourComponent, {
      ...createTestWrapperConfig(pinia),
      props,
    });
  };

  it('should work', () => {
    const wrapper = createWrapper();
    expect(wrapper.exists()).toBe(true);
  });
});
```

### Selective Mocking

```typescript
import { mockRouter, mockCartService, mockProductService } from '@/Tests/Helpers/mocks';

// Only mock what you need
mockRouter();
mockCartService();
mockProductService();
```

### Custom Additional Mocks

```typescript
const customMocks = {
  $toast: {
    success: vi.fn(),
    error: vi.fn(),
  },
  $i18n: {
    t: vi.fn((key: string) => key),
  },
};

const wrapper = shallowMount(Component, {
  ...createTestWrapperConfig(pinia, customMocks),
  props: { /* your props */ }
});
```

## Service Mock Details

### AuthService Mock
```typescript
{
  login: vi.fn(),
  logout: vi.fn(),
  register: vi.fn(),
  user: vi.fn(),
  setToken: vi.fn(),
}
```

### CartService Mock
```typescript
{
  fetchCart: vi.fn(),
  addToCart: vi.fn(),
  removeFromCart: vi.fn(),
  clearCart: vi.fn(),
  checkoutCart: vi.fn(),
}
```

### ProductService Mock
```typescript
{
  fetchProducts: vi.fn(),
  fetchProduct: vi.fn(),
  updateProduct: vi.fn(),
  uploadProductImage: vi.fn(),
}
```

### CategoryService Mock
```typescript
{
  fetchCategories: vi.fn(),
}
```

### OrderService Mock
```typescript
{
  fetchOrders: vi.fn(),
  fetchOrder: vi.fn(),
  updateOrder: vi.fn(),
}
```

## Store Mock Details

### Auth Store Mock
```typescript
{
  user: null,
  token: null,
  isAuthenticated: false,
  login: vi.fn(),
  logout: vi.fn(),
  register: vi.fn(),
  fetchUser: vi.fn(),
}
```

### Cart Store Mock
```typescript
{
  items: [],
  total: 0,
  itemCount: 0,
  addItem: vi.fn(),
  removeItem: vi.fn(),
  updateQuantity: vi.fn(),
  clear: vi.fn(),
  fetchCart: vi.fn(),
}
```

## Best Practices

1. **Use appropriate setup functions**: Choose the right level of mocking for your test
2. **Clear mocks**: Always call `vi.clearAllMocks()` in `beforeEach`
3. **Mock only what you need**: For faster tests, use selective mocking when possible
4. **Test logic, not implementation**: Focus on testing component behavior, not mocked responses
5. **Keep mocks realistic**: Mock functions should return data that resembles real API responses

## Benefits

- **Comprehensive Coverage**: Mocks for all major app dependencies
- **Flexible**: Use everything or pick what you need
- **Consistent**: Standardized mock implementations
- **Maintainable**: Update mocks in one place
- **Type Safe**: Full TypeScript support
- **Performance**: Fast tests without real API calls
