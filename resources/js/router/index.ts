import { createWebHistory, createRouter } from 'vue-router'

import Home from '@/pages/Home.vue'
import Login from '@/pages/auth/Login.vue'
import Register from '@/pages/auth/Register.vue'

const routes = [
  { path: '/', component: Home },
  { path: '/login', component: Login },
  { path: '/register', component: Register },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
