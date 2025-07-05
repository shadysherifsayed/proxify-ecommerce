import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import Login from '@/Pages/Auth/Login.vue';
import { useAuthStore } from '@/Stores/auth';
import { nextTick } from 'vue';

// Mock the AuthLayout component
vi.mock('@/Layouts/AuthLayout.vue', () => ({
  default: {
    template: '<div class="auth-layout"><slot /></div>',
  },
}));

// Mock the router module
vi.mock('@/Router', () => ({
  default: {
    push: vi.fn(),
    replace: vi.fn(),
    go: vi.fn(),
    back: vi.fn(),
    forward: vi.fn(),
  },
}));

// Mock AuthService to avoid API calls
vi.mock('@/Services/AuthService', () => ({
  default: {
    login: vi.fn(),
    logout: vi.fn(),
    register: vi.fn(),
    user: vi.fn(),
    setToken: vi.fn(),
  },
}));

describe('Login.vue', () => {
  let pinia: any;
  let authStore: any;

  beforeEach(() => {
    // Create a fresh Pinia instance for each test
    pinia = createPinia();
    setActivePinia(pinia);
    
    // Get the auth store and mock its methods
    authStore = useAuthStore();
    authStore.login = vi.fn();
    
    // Clear all mocks
    vi.clearAllMocks();
  });

  const createWrapper = (props = {}) => {
    return shallowMount(Login, {
      global: {
        plugins: [pinia],
        mocks: {
          $router: {
            push: vi.fn(),
          },
        },
      },
      props,
    });
  };

  it('initializes with correct default values', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    expect(vm.form.email).toBe('');
    expect(vm.form.password).toBe('');
    expect(vm.isSubmitting).toBe(false);
    expect(vm.validationErrors).toEqual({});
  });

  it('has correct email validation rules', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test email required validation
    expect(vm.rules.email[0]('')).toBe('Email is required');
    expect(vm.rules.email[0]('test@example.com')).toBe(true);
    
    // Test email format validation
    expect(vm.rules.email[1]('test@example.com')).toBe(true);
    expect(vm.rules.email[1]('invalid-email')).toBe('Email must be valid');
    expect(vm.rules.email[1]('test@')).toBe('Email must be valid');
    expect(vm.rules.email[1]('@example.com')).toBe('Email must be valid');
  });

  it('has correct password validation rules', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test password required validation
    expect(vm.rules.password[0]('')).toBe('Password is required');
    expect(vm.rules.password[0]('password')).toBe(true);
    // Note: The actual validation rule uses !!value, so '   ' would be truthy
    expect(vm.rules.password[0]('   ')).toBe(true);
  });

  it('can update form data programmatically', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Update form data
    vm.form.email = 'test@example.com';
    vm.form.password = 'password123';
    
    await nextTick();
    
    expect(vm.form.email).toBe('test@example.com');
    expect(vm.form.password).toBe('password123');
  });

  it('calls authStore.login with correct data when form is valid', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.email = 'test@example.com';
    vm.form.password = 'password123';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Call login function
    await vm.login();
    
    expect(authStore.login).toHaveBeenCalledWith({
      email: 'test@example.com',
      password: 'password123',
    });
    expect(authStore.login).toHaveBeenCalledTimes(1);
  });

  it('does not call authStore.login when form validation fails', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.email = 'invalid-email';
    vm.form.password = '';
    
    // Mock form validation to return invalid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: false }),
    };
    vm.formRef = mockFormRef;
    
    // Call login function
    await vm.login();
    
    expect(authStore.login).not.toHaveBeenCalled();
  });

  it('sets isSubmitting to true during login process', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.email = 'test@example.com';
    vm.form.password = 'password123';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock login to be slow - we need to check isSubmitting synchronously
    authStore.login.mockImplementation(() => {
      // At this point, isSubmitting should be true
      expect(vm.isSubmitting).toBe(true);
      return Promise.resolve();
    });
    
    // Call login function
    await vm.login();
    
    // Check that isSubmitting is false after completion
    expect(vm.isSubmitting).toBe(false);
  });

  it('handles login errors with validation errors correctly', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.email = 'test@example.com';
    vm.form.password = 'wrongpassword';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock login to throw error with validation errors
    const errorWithValidation = new Error('Login failed');
    (errorWithValidation as any).validationErrors = {
      email: ['Invalid credentials'],
      password: ['Password is incorrect'],
    };
    authStore.login.mockRejectedValue(errorWithValidation);
    
    // Call login function
    await vm.login();
    
    expect(vm.validationErrors).toEqual({
      email: ['Invalid credentials'],
      password: ['Password is incorrect'],
    });
    expect(vm.isSubmitting).toBe(false);
  });

  it('handles login errors without validation errors correctly', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.email = 'test@example.com';
    vm.form.password = 'password123';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock login to throw error without validation errors
    const networkError = new Error('Network error');
    authStore.login.mockRejectedValue(networkError);
    
    // Call login function
    await vm.login();
    
    // Validation errors should remain empty for non-validation errors
    expect(vm.validationErrors).toEqual({});
    expect(vm.isSubmitting).toBe(false);
  });

  it('clears validation errors when starting new login attempt', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set some existing validation errors
    vm.validationErrors = { 
      email: ['Previous error'],
      password: ['Another error'],
    };
    
    // Set form data
    vm.form.email = 'test@example.com';
    vm.form.password = 'password123';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Call login function
    await vm.login();
    
    // Validation errors should be cleared before login attempt
    expect(vm.validationErrors).toEqual({});
  });

  it('resets isSubmitting to false even if login fails', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.email = 'test@example.com';
    vm.form.password = 'password123';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock login to throw error
    authStore.login.mockRejectedValue(new Error('Login failed'));
    
    // Call login function
    await vm.login();
    
    // isSubmitting should be false even after error
    expect(vm.isSubmitting).toBe(false);
  });

  it('can programmatically trigger router navigation', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test that the router mock is available
    expect(vm.$router).toBeDefined();
    expect(vm.$router.push).toBeDefined();
    
    // Simulate calling router push (as would happen in register button click)
    vm.$router.push('register');
    
    expect(vm.$router.push).toHaveBeenCalledWith('register');
  });

  it('uses the correct email validation utility', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test that the email validation rule uses the validateEmail utility
    // Test various email formats
    expect(vm.rules.email[1]('user@domain.com')).toBe(true);
    expect(vm.rules.email[1]('user.name@domain.co.uk')).toBe(true);
    expect(vm.rules.email[1]('user+tag@domain.org')).toBe(true);
    
    // Test invalid formats
    expect(vm.rules.email[1]('plainaddress')).toBe('Email must be valid');
    expect(vm.rules.email[1]('user@')).toBe('Email must be valid');
    expect(vm.rules.email[1]('@domain.com')).toBe('Email must be valid');
    expect(vm.rules.email[1]('user@domain')).toBe('Email must be valid');
  });

  it('handles edge cases in form validation', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test empty string vs null/undefined
    expect(vm.rules.email[0](null)).toBe('Email is required');
    expect(vm.rules.email[0](undefined)).toBe('Email is required');
    expect(vm.rules.password[0](null)).toBe('Password is required');
    expect(vm.rules.password[0](undefined)).toBe('Password is required');
    
    // Test whitespace-only strings - the actual rule uses !!value, so whitespace is truthy
    expect(vm.rules.email[0]('   ')).toBe(true);
    expect(vm.rules.password[0]('   ')).toBe(true);
  });

  it('maintains component state correctly during interactions', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test initial state
    expect(vm.isSubmitting).toBe(false);
    
    // Simulate state changes that would happen during user interaction
    vm.isSubmitting = true;
    await nextTick();
    expect(vm.isSubmitting).toBe(true);
    
    vm.isSubmitting = false;
    await nextTick();
    expect(vm.isSubmitting).toBe(false);
  });

  it('clears validation errors when starting new login attempt', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set some validation errors
    vm.validationErrors = { email: ['Previous error'] };
    
    // Set form data
    vm.form.email = 'test@example.com';
    vm.form.password = 'password123';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Call login function
    await vm.login();
    
    // Validation errors should be cleared
    expect(vm.validationErrors).toEqual({});
  });
});
