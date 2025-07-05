import router from '@/Router';
import AuthService from '@/Services/AuthService';
import { User } from '@/Types/entities';
import { LoginRequest, RegisterRequest } from '@/Types/requests';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useCartStore } from './cart';
import { useOrdersStore } from './orders';
import { useProductsStore } from './products';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(null);

  const isAuthenticated = computed(() => !!user.value);

  const productsStore = useProductsStore();
  const cartStore = useCartStore();
  const ordersStore = useOrdersStore();

  async function login(data: LoginRequest) {
    const response = await AuthService.login(data);
    user.value = response.user;
    setTokenAndNavigate(response.token ?? '');
  }

  async function logout() {
    try {
      await AuthService.logout();
      cartStore.resetCart();
      ordersStore.resetOrders();
      productsStore.resetProducts();
    } finally {
      user.value = null;
      token.value = null;
      router.push('/login');
    }
  }

  async function register(data: RegisterRequest) {
    const response = await AuthService.register(data);
    user.value = response.user;
    setTokenAndNavigate(response.token ?? '');
  }

  function setTokenAndNavigate(t: string) {
    AuthService.setToken(t);
    router.push('/');
  }

  async function fetchUser() {
    if (isAuthenticated.value) {
      return;
    }
    try {
      const response = await AuthService.user();
      user.value = response.user;
    } catch {
      user.value = null; // If fetching user fails, set user to null
      token.value = null; // Clear token if fetching user fails
    }
  }

  return {
    user,
    isAuthenticated,
    login,
    logout,
    register,
    fetchUser,
  };
});
