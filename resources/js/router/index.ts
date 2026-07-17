import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Public routes
    {
      path: '/',
      component: () => import('@/views/public/PublicLayout.vue'),
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('@/views/public/PublicHomeView.vue'),
        },
        {
          path: 'about',
          name: 'about',
          component: () => import('@/views/public/AboutUsView.vue'),
        },
      ],
    },
    // Auth routes
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
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
        {
          path: 'period',
          name: 'admin-period',
          component: () => import('@/views/admin/PeriodView.vue'),
        },
        {
          path: 'component',
          name: 'admin-component',
          component: () => import('@/views/admin/MasterComponentView.vue'),
        },
        {
          path: 'questionnaire',
          name: 'admin-questionnaire',
          component: () => import('@/views/admin/MasterQuestionnairesView.vue'),
        },
        {
          path: 'sub-component',
          name: 'admin-sub-component',
          component: () => import('@/views/admin/MasterSubComponentView.vue'),
        },
        {
          path: 'indicator',
          name: 'admin-indicator',
          component: () => import('@/views/admin/MasterIndicatorView.vue'),
        },
        {
          path: 'question',
          name: 'admin-question',
          component: () => import('@/views/admin/MasterQuestionView.vue'),
        },
        {
          path: 'question/add',
          name: 'admin-question-add',
          component: () => import('@/views/admin/AddQuestionView.vue'),
        },
        {
          path: 'question/:id/edit',
          name: 'admin-question-edit',
          component: () => import('@/views/admin/EditQuestionView.vue'),
        },
        {
          path: 'respondent',
          name: 'admin-respondent',
          component: () => import('@/views/admin/MasterRespondentView.vue'),
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
