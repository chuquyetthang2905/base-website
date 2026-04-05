import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

/**
 * Vue Router
 *
 * Route meta fields:
 * - requiresAuth: true  → redirect to /login if not authenticated
 * - requiresGuest: true → redirect to /dashboard if already authenticated
 *                         (prevents logged-in users from visiting /login)
 * - roles: ['admin']    → require specific role(s) after auth check
 */

const routes = [
  // ─── Public routes ──────────────────────────────────────────────
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/LoginPage.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/auth/RegisterPage.vue'),
    meta: { requiresGuest: true },
  },

  // ─── Home (public landing page) ─────────────────────────────────
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/HomePage.vue'),
  },

  // ─── Protected routes ───────────────────────────────────────────
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/pages/DashboardPage.vue'),
    meta: { requiresAuth: true },
  },

  // ─── 404 Catch-all ──────────────────────────────────────────────
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/pages/NotFoundPage.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// ─── Navigation Guard ────────────────────────────────────────────────────────
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // On first navigation after page reload, accessToken is null but the
  // HttpOnly cookie may still be valid. Attempt a silent refresh once
  // before making any auth decisions.
  if (!auth.initialized) {
    await auth.refresh()
  }

  // requiresAuth: redirect to login if not authenticated
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  // requiresGuest: redirect to dashboard if already logged in
  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  // roles check: if route requires a specific role
  if (to.meta.roles && !auth.hasAnyRole(to.meta.roles)) {
    return { name: 'dashboard' }
  }
})

export default router
