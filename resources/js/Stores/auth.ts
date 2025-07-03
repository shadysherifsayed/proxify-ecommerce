import router from '@/Router';
import AuthService from '@/Services/AuthService';
import { User } from '@/Types/entities';
import { LoginRequest, RegisterRequest } from '@/Types/requests';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useCartStore } from './cart';

export const useAuthStore = defineStore(
  'auth',
  () => {
    const user = ref<User | null>(null);
    const token = ref<string | null>(null);

    const isAuthenticated = computed(() => !!user.value);

    const cartStore = useCartStore()

    async function login(data: LoginRequest) {
      const response = await AuthService.login(data);
      user.value = response.user;
      setTokenAndNavigate(response.token ?? '');
      cartStore.fetchCart(); // Assuming you have a cart store to fetch the cart after login
    }

    async function logout() {
      try {
        await AuthService.logout();
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
      cartStore.fetchCart(); // Assuming you have a cart store to fetch the cart after registration
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
  },
);
