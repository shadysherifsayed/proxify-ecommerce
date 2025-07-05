/**
 * Example usage of the extracted test mocks
 * 
 * This file demonstrates how to use the mocks from tests/helpers/mocks.ts
 * in different scenarios for testing Vue components
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { 
  setupAuthMocks, 
  createTestWrapperConfig, 
  mockAuthLayout,
  mockRouter,
  mockAuthService 
} from './mocks';

// Example 1: Using all auth mocks together
setupAuthMocks();

describe('Example Component Test with All Auth Mocks', () => {
  let pinia: any;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
  });

  it('should work with all auth mocks', () => {
    // This component would have access to all mocked dependencies
    // const wrapper = shallowMount(SomeAuthComponent, createTestWrapperConfig(pinia));
    // ... test implementation
    expect(true).toBe(true); // Placeholder
  });
});

// Example 2: Using individual mocks selectively
describe('Example Component Test with Selective Mocks', () => {
  beforeEach(() => {
    // Only mock what this component needs
    mockRouter();
    mockAuthService();
    // Don't mock AuthLayout if this component doesn't use it
  });

  it('should work with selective mocks', () => {
    // This test only has router and auth service mocked
    expect(true).toBe(true); // Placeholder
  });
});

// Example 3: Using the wrapper config with additional mocks
describe('Example Component Test with Custom Mocks', () => {
  let pinia: any;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
  });

  it('should work with custom additional mocks', () => {
    const customMocks = {
      $toast: {
        success: vi.fn(),
        error: vi.fn(),
      },
      $i18n: {
        t: vi.fn((key: string) => key),
      },
    };

    // Use the helper with additional custom mocks
    const wrapperConfig = createTestWrapperConfig(pinia, customMocks);
    
    // const wrapper = shallowMount(SomeComponent, {
    //   ...wrapperConfig,
    //   props: { someProp: 'value' }
    // });

    expect(true).toBe(true); // Placeholder
  });
});

// Example 4: Creating a specific mock setup for a group of related components
const setupCartMocks = () => {
  mockAuthService();
  vi.mock('@/Services/CartService', () => ({
    default: {
      addItem: vi.fn(),
      removeItem: vi.fn(),
      getItems: vi.fn(),
      clear: vi.fn(),
    },
  }));
};

describe('Example Cart Component Tests', () => {
  beforeEach(() => {
    setupCartMocks();
  });

  it('should work with cart-specific mocks', () => {
    // This test has both auth and cart service mocked
    expect(true).toBe(true); // Placeholder
  });
});
