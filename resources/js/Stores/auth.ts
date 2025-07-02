import AuthService from '@/Services/AuthService';
import { User } from '@/Tpes/entities';
import { LoginRequest, RegisterRequest } from '@/Tpes/requests';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useAuthStore = defineStore(
    'auth',
    () => {
        const user = ref<User | null>(null);
        const token = ref<string | null>(null);

        const isAuthenticated = computed(() => !!user.value);

        async function login(data: LoginRequest) {
            try {
                const response = await AuthService.login(data);
                user.value = response.user;
                token.value = response.token;
            } catch (error) {
                console.error('Login failed:', error);
            }
        }

        async function logout() {
            await AuthService.logout();
            user.value = null;
            token.value = null;
        }

        async function register(data: RegisterRequest) {
             try {
                const response = await AuthService.register(data);
                user.value = response.user;
                token.value = response.token;
            } catch (error) {
                console.error('Login failed:', error);
            }
        }

        async function fetchUser() {
            try {
                const response = await AuthService.user();
                user.value = response.user;
            } catch (error) {
                console.error('Fetch user failed:', error);
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
    {
        persist: {
            pick: ['token'],
        },
    },
);
