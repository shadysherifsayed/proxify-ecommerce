import { createWebHistory, createRouter } from 'vue-router'

import Products from '@/Pages/Products/Index.vue'
import ProductDetails from '@/Pages/Products/Details.vue'
import Login from '@/Pages/Auth/Login.vue'
import Register from '@/Pages/Auth/Register.vue'
import Orders from '@/Pages/Orders/Index.vue'
import OrderDetails from '@/Pages/Orders/Details.vue'

const routes = [
  { path: '/login', component: Login, name: 'auth.login' },
  { path: '/register', component: Register, name: 'auth.register' },
  
  { path: '/', component: Products, name: 'products.index' },
  { path: '/products/:id', component: ProductDetails, name: 'products.show' },

  { path: '/orders', component: Orders, name: 'orders.index' },
  { path: '/orders/:id', component: OrderDetails, name: 'orders.show' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
