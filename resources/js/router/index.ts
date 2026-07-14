import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Auth
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guest: true },
    },
    // Admin routes
    {
      path: '/admin',
      component: () => import('@/views/admin/AdminLayout.vue'),
      meta: { requiresAuth: true, role: 'ADMIN' },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: () => import('@/views/admin/DashboardView.vue'),
        },
      ],
    },
    // Respondent routes
    {
      path: '/respondent',
      component: () => import('@/views/respondent/RespondentLayout.vue'),
      meta: { requiresAuth: true, role: 'RESPONDENT' },
      children: [
        {
          path: '',
          name: 'respondent-home',
          component: () => import('@/views/respondent/PlatformExplanationView.vue'),
        },
      ],
    },
  ],
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next(authStore.user?.role === 'ADMIN' ? '/admin' : '/respondent')
  } else {
    next()
  }
})

export default router
