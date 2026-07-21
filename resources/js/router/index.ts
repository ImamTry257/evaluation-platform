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
      path: '/login/admin',
      name: 'login-admin',
      component: () => import('@/views/auth/LoginAdminView.vue'),
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
      component: () => import('@/views/admin/layout/AdminLayout.vue'),
      meta: { requiresAuth: true, roles: ['ADMIN', 'SUPERADMIN'] },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: () => import('@/views/admin/dashboard/DashboardView.vue'),
        },
        {
          path: 'period',
          name: 'admin-period',
          component: () => import('@/views/admin/master-data/PeriodView.vue'),
        },
        {
          path: 'instrument',
          name: 'admin-instrument',
          component: () => import('@/views/admin/master-data/QuestionnaireView.vue'),
        },
        {
          path: 'instrument/:instrumentId/component',
          name: 'admin-component',
          component: () => import('@/views/admin/master-data/ComponentView.vue'),
        },
        {
          path: 'component/:componentId/sub-component',
          name: 'admin-sub-component',
          component: () => import('@/views/admin/master-data/SubComponentView.vue'),
        },
        {
          path: 'component/:componentId/sub-component/:subComponentId/indicator',
          name: 'admin-indicator',
          component: () => import('@/views/admin/master-data/IndicatorView.vue'),
        },
        {
          path: 'component/:componentId/sub-component/:subComponentId/indicator/:indicatorId/question',
          name: 'admin-question',
          component: () => import('@/views/admin/master-data/Question/QuestionView.vue'),
        },
        {
          path: 'sub-component/:subComponentId/indicator',
          name: 'admin-sub-component-indicator',
          component: () => import('@/views/admin/master-data/IndicatorView.vue'),
        },
        {
          path: 'indicator/:indicatorId/question',
          name: 'admin-indicator-question',
          component: () => import('@/views/admin/master-data/Question/QuestionView.vue'),
        },
        {
          path: 'indicator/:indicatorId/question/add',
          name: 'admin-question-add',
          component: () => import('@/views/admin/master-data/Question/QuestionAddView.vue'),
        },
        {
          path: 'indicator/:indicatorId/question/:id',
          name: 'admin-question-show',
          component: () => import('@/views/admin/master-data/Question/QuestionShowView.vue'),
        },
        {
          path: 'indicator/:indicatorId/question/:id/edit',
          name: 'admin-question-edit',
          component: () => import('@/views/admin/master-data/Question/QuestionEditView.vue'),
        },
        {
          path: 'respondent',
          name: 'admin-respondent',
          component: () => import('@/views/admin/master-data/RespondentView.vue'),
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: () => import('@/views/admin/settings/SettingView.vue'),
        },
        {
          path: 'reports',
          name: 'admin-reports',
          component: () => import('@/views/admin/master-data/ReportView.vue'),
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
          component: () => import('@/views/respondent/ExplanationView.vue'),
        },
        {
          path: 'evaluation/:sessionId/component/:compIndex',
          name: 'respondent-evaluation',
          component: () => import('@/views/respondent/InputAngketView.vue'),
        },
        {
          path: 'result/:sessionId',
          name: 'respondent-result',
          component: () => import('@/views/respondent/ResultRecommendationView.vue'),
        },
        {
          path: 'profile',
          name: 'respondent-profile',
          component: () => import('@/views/respondent/ProfileView.vue'),
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
    console.log(authStore.user, to.meta.requiresAuth, authStore.isAuthenticated)
    const role = authStore.user?.role
    if (role === 'ADMIN' || role === 'SUPERADMIN') {
      next('/admin')
    } else {
      next('/respondent')
    }
  } else if (to.meta.roles && !to.meta.roles.includes(authStore.user?.role)) {
    console.log(authStore.user, to.meta.requiresAuth, authStore.isAuthenticated)
    // Unauthorized role - redirect to appropriate dashboard
    const role = authStore.user?.role
    if (role === 'ADMIN' || role === 'SUPERADMIN') {
      next('/admin')
    } else {
      next('/respondent')
    }
  } else {
    next()
  }
})

export default router
