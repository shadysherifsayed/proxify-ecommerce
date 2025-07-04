import { useAuthStore } from '@/Stores/auth';
import { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';

export async function authMiddleware(
  to: RouteLocationNormalized,
  from: RouteLocationNormalized,
  next: NavigationGuardNext,
) {
  const authStore = useAuthStore();
  await authStore.fetchUser(); // Ensure auth status is checked
  if (!authStore.isAuthenticated) {
    next({ name: 'auth.login', query: { redirect: to.fullPath } });
  } else {
    next();
  }
}

export async function guestMiddleware(
  to: RouteLocationNormalized,
  from: RouteLocationNormalized,
  next: NavigationGuardNext,
) {
  const authStore = useAuthStore();
  if (authStore.isAuthenticated) {
    next({ name: 'home' });
  } else {
    next();
  }
}
