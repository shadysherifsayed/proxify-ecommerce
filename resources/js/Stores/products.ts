import AuthService from '@/Services/AuthService';
import { User } from '@/Tpes/entities';
import { LoginRequest } from '@/Tpes/requests';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useProductsStore = defineStore(
    'auth',
    () => {
        const products = ref([]);
        const product  = ref(null)


        async function fetchProducts() {
            
        }
        return {
            product,
            products,

            fetchProducts,
            
        };
    },
);
