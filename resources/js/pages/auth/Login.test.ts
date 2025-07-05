import Login from '@/Pages/Auth/Login.vue';
import { useAuthStore } from '@/Stores/auth';
import { shallowMount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

describe('Login.vue', () => {
  let pinia: any;
  let authStore: any;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);

    authStore = useAuthStore();
    authStore.login = vi.fn();

    vi.clearAllMocks();
  });

  const createWrapper = () => {
    return shallowMount(Login, {
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

    expect(vm.form.email).toBe('');
    expect(vm.form.password).toBe('');
    expect(vm.isSubmitting).toBe(false);
  });

  it('validates email format', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    expect(vm.rules.email[1]('test@example.com')).toBe(true);
    expect(vm.rules.email[1]('invalid-email')).toBe('Email must be valid');
  });

  it('validates required fields', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    expect(vm.rules.email[0]('')).toBe('Email is required');
    expect(vm.rules.password[0]('')).toBe('Password is required');
  });

  it('calls login with form data when valid', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    vm.form.email = 'test@example.com';
    vm.form.password = 'password123';

    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;

    await vm.login();

    expect(authStore.login).toHaveBeenCalledWith({
      email: 'test@example.com',
      password: 'password123',
    });
  });

  it('does not login when form is invalid', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: false }),
    };
    vm.formRef = mockFormRef;

    await vm.login();

    expect(authStore.login).not.toHaveBeenCalled();
  });

  it('handles login errors', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm as any;

    vm.form.email = 'test@example.com';
    vm.form.password = 'wrongpassword';

    const mockFormRef = {
      validate: vi.fn().mockResolvedValue({ valid: true }),
    };
    vm.formRef = mockFormRef;

    const error = new Error('Login failed');
    (error as any).validationErrors = { email: ['Invalid credentials'] };
    authStore.login.mockRejectedValue(error);

    await vm.login();

    expect(vm.validationErrors).toEqual({ email: ['Invalid credentials'] });
    expect(vm.isSubmitting).toBe(false);
  });
});
