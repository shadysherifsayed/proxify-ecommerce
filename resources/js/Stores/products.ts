import ProductService from '@/Services/ProductService';
import { Product } from '@/Types/entities';
import { Pagination } from '@/Types/responses';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useProductsStore = defineStore(
    'products',
    () => {
        const products = ref<Product[]>([]);
        const productsMeta = ref<Pagination>({
            next: null,
            prev: null
        });
        const product = ref<Product | null>(null);

        /**
         * Fetches the list of products from the ProductService.
         * If the request fails, it sets products to an empty array and resets pagination metadata.
         */
        async function fetchProducts() {
            try {
                // if next is not null, fetch next page
                const response = await ProductService.list()
                products.value = response.data
                productsMeta.value = {
                    next: response.next_cursor,
                    prev: response.prev_cursor
                }
            } catch {
                products.value = []
                productsMeta.value = {
                    next: null,
                    prev: null
                };
            }

        }
        return {
            product,
            products,
            productsMeta,
            fetchProducts,
        };
    },
);
