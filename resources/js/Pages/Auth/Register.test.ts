import Register from '@/Pages/Auth/Register.vue';
import { useAuthStore } from '@/Stores/auth';
import { shallowMount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

describe('Register.vue', () => {
  let pinia: any;
  let authStore: any;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);

    authStore = useAuthStore();
    authStore.register = vi.fn();

    vi.clearAllMocks();
  });

  const createWrapper = () => {
    return shallowMount(Register, {
      props: {
        modelValue: false,
      },
      global: {
        plugins: [pinia],
      },
    });
  };

  it('initializes with empty form', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    expect(vm.form.name).toBe('');
    expect(vm.form.email).toBe('');
    expect(vm.form.password).toBe('');
    expect(vm.form.password_confirmation).toBe('');
    expect(vm.isSubmitting).toBe(false);
  });

  it('validates required fields', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    expect(vm.rules.name[0]('')).toBe('Name is required');
    expect(vm.rules.email[0]('')).toBe('Email is required');
    expect(vm.rules.password[0]('')).toBe('Password is required');
    expect(vm.rules.password_confirmation[0]('')).toBe(
      'Password confirmation is required',
    );
  });

  it('validates email format', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    expect(vm.rules.email[1]('test@example.com')).toBe(true);
    expect(vm.rules.email[1]('invalid-email')).toBe('Email must be valid');
  });

  it('validates password confirmation matches', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    vm.form.password = 'password123';
    expect(vm.rules.password_confirmation[1]('password123')).toBe(true);
    expect(vm.rules.password_confirmation[1]('different')).toBe(
      'Passwords do not match',
    );
  });

  it('calls register with form data when valid', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'password123';
    vm.form.password_confirmation = 'password123';

    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;

    await vm.register();

    expect(authStore.register).toHaveBeenCalledWith({
      name: 'John Doe',
      email: 'john@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    });
  });

  it('does not register when form is invalid', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: false }),
    };
    vm.formRef = mockFormRef;

    await vm.register();

    expect(authStore.register).not.toHaveBeenCalled();
  });

  it('handles registration errors', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    vm.form.name = 'John Doe';
    vm.form.email = 'john@example.com';
    vm.form.password = 'password123';
    vm.form.password_confirmation = 'password123';

    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;

    const error = new Error('Registration failed');
    (error as any).validationErrors = { email: ['Email already exists'] };
    authStore.register.mockRejectedValue(error);

    await vm.register();

    expect(vm.validationErrors).toEqual({ email: ['Email already exists'] });
    expect(vm.isSubmitting).toBe(false);
  });
});
