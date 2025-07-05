import Login from '@/Pages/Auth/Login.vue';
import Register from '@/Pages/Auth/Register.vue';
import OrderDetails from '@/Pages/Orders/Details.vue';
import Orders from '@/Pages/Orders/Index.vue';
import ProductDetails from '@/Pages/Products/Details.vue';
import Products from '@/Pages/Products/Index.vue';
import { createRouter, createWebHistory } from 'vue-router';
import { authMiddleware, guestMiddleware } from './middleware';

const routes = [
  // Guest routes (only accessible when not authenticated)
  {
    path: '/login',
    component: Login,
    name: 'auth.login',
    beforeEnter: guestMiddleware,
  },
  {
    path: '/register',
    component: Register,
    name: 'auth.register',
    beforeEnter: guestMiddleware,
  },

  // Protected routes (only accessible when authenticated)

  { path: '/', component: Products, name: 'home', beforeEnter: authMiddleware },
  {
    path: '/products',
    component: Products,
    name: 'products.index',
    beforeEnter: authMiddleware,
  },
  {
    path: '/products/:id',
    component: ProductDetails,
    name: 'products.show',
    beforeEnter: authMiddleware,
  },

  {
    path: '/orders',
    component: Orders,
    name: 'orders.index',
    beforeEnter: authMiddleware,
  },
  {
    path: '/orders/:id',
    component: OrderDetails,
    name: 'orders.show',
    beforeEnter: authMiddleware,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
