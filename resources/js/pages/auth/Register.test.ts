import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import Register from '@/Pages/Auth/Register.vue';
import { useAuthStore } from '@/Stores/auth';
import { nextTick } from 'vue';
import { setupAuthMocks, createTestWrapperConfig } from '@/Tests/Helpers/mocks';

// Setup all auth-related mocks
setupAuthMocks();

describe('Register.vue', () => {
  let pinia: any;
  let authStore: any;

  beforeEach(() => {
    // Create a fresh Pinia instance for each test
    pinia = createPinia();
    setActivePinia(pinia);
    
    // Get the auth store and mock its methods
    authStore = useAuthStore();
    authStore.register = vi.fn();
    
    // Clear all mocks
    vi.clearAllMocks();
  });

  const createWrapper = (props = {}) => {
    return shallowMount(Register, {
      ...createTestWrapperConfig(pinia),
      props,
    });
  };

  it('initializes with correct default values', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    expect(vm.form.name).toBe('');
    expect(vm.form.email).toBe('');
    expect(vm.form.password).toBe('');
    expect(vm.form.password_confirmation).toBe('');
    expect(vm.isSubmitting).toBe(false);
    expect(vm.validationErrors).toEqual({});
    expect(vm.errorMessage).toBe('');
  });

  it('has correct name validation rules', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test name required validation
    expect(vm.rules.name[0]('')).toBe('Name is required');
    expect(vm.rules.name[0]('John')).toBe(true);
    
    // Test name minimum length validation
    expect(vm.rules.name[1]('Jo')).toBe('Name must be at least 3 characters long');
    expect(vm.rules.name[1]('John')).toBe(true);
    
    // Test name maximum length validation
    const longName = 'a'.repeat(51);
    expect(vm.rules.name[2](longName)).toBe('Name must be at most 50 characters long');
    expect(vm.rules.name[2]('John Doe')).toBe(true);
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
    expect(vm.rules.password[0]('Password123!')).toBe(true);
    
    // Test password complexity validation (uses validatePassword utility)
    expect(vm.rules.password[1]('weak')).toBe('Password must be at least 8 characters long and contain at least one number and one special character');
    expect(vm.rules.password[1]('StrongPass123!')).toBe(true);
  });

  it('has correct password confirmation validation rules', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set a password to test confirmation against
    vm.form.password = 'Password123!';
    
    // Test password confirmation required validation
    expect(vm.rules.password_confirmation[0]('')).toBe('Password confirmation is required');
    expect(vm.rules.password_confirmation[0]('Password123!')).toBe(true);
    
    // Test password match validation
    expect(vm.rules.password_confirmation[1]('DifferentPass123!')).toBe('Passwords do not match');
    expect(vm.rules.password_confirmation[1]('Password123!')).toBe(true);
  });

  it('can update form data programmatically', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Update form data
    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'Password123!';
    vm.form.password_confirmation = 'Password123!';
    
    await nextTick();
    
    expect(vm.form.name).toBe('John Doe');
    expect(vm.form.email).toBe('john@example.com');
    expect(vm.form.password).toBe('Password123!');
    expect(vm.form.password_confirmation).toBe('Password123!');
  });

  it('calls authStore.register with correct data when form is valid', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'Password123!';
    vm.form.password_confirmation = 'Password123!';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Call register function
    await vm.register();
    
    expect(authStore.register).toHaveBeenCalledWith({
      name: 'John Doe',
      email: 'john@example.com',
      password: 'Password123!',
      password_confirmation: 'Password123!',
    });
    expect(authStore.register).toHaveBeenCalledTimes(1);
  });

  it('does not call authStore.register when form validation fails', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set invalid form data
    vm.form.name = '';
    vm.form.email = 'invalid-email';
    vm.form.password = 'weak';
    vm.form.password_confirmation = 'different';
    
    // Mock form validation to return invalid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: false }),
    };
    vm.formRef = mockFormRef;
    
    // Call register function
    await vm.register();
    
    expect(authStore.register).not.toHaveBeenCalled();
  });

  it('sets isSubmitting to true during registration process', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'Password123!';
    vm.form.password_confirmation = 'Password123!';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock register to check isSubmitting synchronously
    authStore.register.mockImplementation(() => {
      // At this point, isSubmitting should be true
      expect(vm.isSubmitting).toBe(true);
      return Promise.resolve();
    });
    
    // Call register function
    await vm.register();
    
    // Check that isSubmitting is false after completion
    expect(vm.isSubmitting).toBe(false);
  });

  it('handles registration errors with validation errors correctly', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.name = 'John Doe';
    vm.form.email = 'existing@example.com';
    vm.form.password = 'Password123!';
    vm.form.password_confirmation = 'Password123!';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock register to throw error with validation errors
    const errorWithValidation = new Error('Registration failed');
    (errorWithValidation as any).validationErrors = {
      email: ['Email is already taken'],
      name: ['Name is invalid'],
    };
    authStore.register.mockRejectedValue(errorWithValidation);
    
    // Call register function
    await vm.register();
    
    expect(vm.validationErrors).toEqual({
      email: ['Email is already taken'],
      name: ['Name is invalid'],
    });
    expect(vm.errorMessage).toBe('');
    expect(vm.isSubmitting).toBe(false);
  });

  it('handles registration errors with response message correctly', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'Password123!';
    vm.form.password_confirmation = 'Password123!';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock register to throw error with response message
    const errorWithMessage = new Error('Registration failed');
    (errorWithMessage as any).response = {
      data: {
        message: 'Server is currently unavailable',
      },
    };
    authStore.register.mockRejectedValue(errorWithMessage);
    
    // Call register function
    await vm.register();
    
    expect(vm.errorMessage).toBe('Server is currently unavailable');
    expect(vm.validationErrors).toEqual({});
    expect(vm.isSubmitting).toBe(false);
  });

  it('handles registration errors without specific message correctly', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'Password123!';
    vm.form.password_confirmation = 'Password123!';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock register to throw generic error
    const genericError = new Error('Network error');
    authStore.register.mockRejectedValue(genericError);
    
    // Call register function
    await vm.register();
    
    expect(vm.errorMessage).toBe('An error occurred during registration.');
    expect(vm.validationErrors).toEqual({});
    expect(vm.isSubmitting).toBe(false);
  });

  it('clears validation errors when starting new registration attempt', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set some existing validation errors
    vm.validationErrors = { 
      email: ['Previous error'],
      name: ['Another error'],
    };
    
    // Set form data
    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'Password123!';
    vm.form.password_confirmation = 'Password123!';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Call register function
    await vm.register();
    
    // Validation errors should be cleared before registration attempt
    expect(vm.validationErrors).toEqual({});
  });

  it('resets isSubmitting to false even if registration fails', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set form data
    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'Password123!';
    vm.form.password_confirmation = 'Password123!';
    
    // Mock form validation to return valid
    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;
    
    // Mock register to throw error
    authStore.register.mockRejectedValue(new Error('Registration failed'));
    
    // Call register function
    await vm.register();
    
    // isSubmitting should be false even after error
    expect(vm.isSubmitting).toBe(false);
  });

  it('can programmatically trigger router navigation', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test that the router mock is available
    expect(vm.$router).toBeDefined();
    expect(vm.$router.push).toBeDefined();
    
    // Simulate calling router push (as would happen in login button click)
    vm.$router.push('login');
    
    expect(vm.$router.push).toHaveBeenCalledWith('login');
  });

  it('maintains component state correctly during interactions', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test initial state
    expect(vm.isSubmitting).toBe(false);
    expect(vm.errorMessage).toBe('');
    
    // Simulate state changes that would happen during user interaction
    vm.isSubmitting = true;
    vm.errorMessage = 'Test error';
    await nextTick();
    
    expect(vm.isSubmitting).toBe(true);
    expect(vm.errorMessage).toBe('Test error');
    
    vm.isSubmitting = false;
    vm.errorMessage = '';
    await nextTick();
    
    expect(vm.isSubmitting).toBe(false);
    expect(vm.errorMessage).toBe('');
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

  it('uses the correct password validation utility', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test that the password validation rule uses the validatePassword utility
    // Test various password formats (based on the utility's requirements)
    expect(vm.rules.password[1]('Password123!')).toBe(true);
    expect(vm.rules.password[1]('MySecure1@')).toBe(true);
    
    // Test invalid formats
    expect(vm.rules.password[1]('short')).toBe('Password must be at least 8 characters long and contain at least one number and one special character');
    expect(vm.rules.password[1]('onlyletters')).toBe('Password must be at least 8 characters long and contain at least one number and one special character');
    expect(vm.rules.password[1]('NoSpecialChar123')).toBe('Password must be at least 8 characters long and contain at least one number and one special character');
  });

  it('handles edge cases in form validation', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Test empty string vs null/undefined for all fields
    expect(vm.rules.name[0](null)).toBe('Name is required');
    expect(vm.rules.name[0](undefined)).toBe('Name is required');
    expect(vm.rules.email[0](null)).toBe('Email is required');
    expect(vm.rules.email[0](undefined)).toBe('Email is required');
    expect(vm.rules.password[0](null)).toBe('Password is required');
    expect(vm.rules.password[0](undefined)).toBe('Password is required');
    expect(vm.rules.password_confirmation[0](null)).toBe('Password confirmation is required');
    expect(vm.rules.password_confirmation[0](undefined)).toBe('Password confirmation is required');
    
    // Test whitespace-only strings (the actual rule uses !!value, so whitespace is truthy)
    expect(vm.rules.name[0]('   ')).toBe(true);
    expect(vm.rules.email[0]('   ')).toBe(true);
    expect(vm.rules.password[0]('   ')).toBe(true);
    expect(vm.rules.password_confirmation[0]('   ')).toBe(true);
  });

  it('validates password confirmation dynamically with current password', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;
    
    // Set initial password
    vm.form.password = 'FirstPassword123!';
    
    // Test confirmation with first password
    expect(vm.rules.password_confirmation[1]('FirstPassword123!')).toBe(true);
    expect(vm.rules.password_confirmation[1]('WrongPassword123!')).toBe('Passwords do not match');
    
    // Change password
    vm.form.password = 'SecondPassword456@';
    await nextTick();
    
    // Test confirmation with new password
    expect(vm.rules.password_confirmation[1]('SecondPassword456@')).toBe(true);
    expect(vm.rules.password_confirmation[1]('FirstPassword123!')).toBe('Passwords do not match');
  });
});
