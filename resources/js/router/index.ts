import { createWebHistory, createRouter } from 'vue-router'

import Products from '@/Pages/Products/Index.vue'
import Login from '@/Pages/Auth/Login.vue'
import Register from '@/Pages/Auth/Register.vue'
import Orders from '@/Pages/Orders/Index.vue'
import OrderDetails from '@/Pages/Orders/Details.vue'

const routes = [
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  
  { path: '/', component: Products },
  { path: '/orders', component: Orders },
  { path: '/orders/:id', component: OrderDetails },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
