# Frontend Setup Guide
## Vue 3 + Vite + TypeScript + TailwindCSS (inside Laravel)

**Target:** Setup Vue 3 frontend di dalam Laravel project (`resources/js/`)

---

## 1. Overview

Vue.js berada di dalam Laravel project, bukan project terpisah. Struktur:

```
cbt/                          ← Root (Laravel project)
├── app/
├── config/
├── database/
├── resources/
│   ├── js/                   ← Vue.js app
│   │   ├── app.ts
│   │   ├── components/
│   │   ├── views/
│   │   ├── stores/
│   │   ├── services/
│   │   ├── router/
│   │   ├── types/
│   │   ├── composables/
│   │   └── ...
│   └── css/
├── routes/
├── public/
│   └── build/                ← Vite output
├── vite.config.ts
├── package.json
└── ...
```

---

## 2. Prerequisites

| Software | Version | Notes |
|----------|---------|-------|
| PHP | 8.3+ | Laravel backend |
| Node.js | 18+ | JavaScript runtime |
| npm | 9+ | Package manager |
| Composer | 2.x | PHP dependencies |

---

## 3. Installation

### 3.1 Create Laravel Project (from root)

```bash
# From cbt/ root
composer create-project laravel/laravel .
```

### 3.2 Install Vue.js Dependencies

```bash
# Install Vue 3 + Vite
npm install vue@latest vue-router@latest pinia

# TypeScript
npm install -D typescript @types/node

# Vite plugins
npm install -D @vitejs/plugin-vue

# HTTP client
npm install axios

# CSS framework
npm install -D tailwindcss @tailwindcss/vite

# Icons
npm install lucide-vue-next

# Dev tools
npm install -D @vue/tsconfig
```

---

## 4. Vite Configuration

### 4.1 Update `vite.config.ts`

```typescript
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.ts'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
})
```

### 4.2 Install `laravel-vite-plugin`

```bash
npm install -D laravel-vite-plugin
```

---

## 5. Environment Configuration

### 5.1 Update `.env`

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
VITE_APP_NAME="CBT Platform"
```

---

## 6. CSS Setup

### 6.1 Create `resources/css/app.css`

```css
@import "tailwindcss";

/* Custom colors from master_UX.md */
:root {
  --color-primary: #006c49;
  --color-primary-container: #10b981;
  --color-secondary: #5d5f5f;
  --color-background: #f4fbf4;
  --color-surface: #f4fbf4;
  --color-error: #ba1a1a;
}

/* Font */
body {
  font-family: 'Poppins', sans-serif;
}
```

---

## 7. Vue App Entry Point

### 7.1 Create `resources/js/app.ts`

```typescript
import './bootstrap'
import '../css/app.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
```

### 7.2 Create `resources/js/App.vue`

```vue
<script setup lang="ts">
import { RouterView } from 'vue-router'
</script>

<template>
  <RouterView />
</template>
```

### 7.3 Update `resources/views/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platform Evaluasi Kebijakan Lingkungan Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="antialiased">
    <div id="app"></div>
</body>
</html>
```

### 7.4 Update `routes/web.php`

```php
use Illuminate\Support\Facades\Route;

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
```

---

## 8. Folder Structure Setup

```bash
# Create Vue directories inside resources/js
mkdir -p resources/js/components/layout
mkdir -p resources/js/components/common
mkdir -p resources/js/components/auth
mkdir -p resources/js/components/evaluation
mkdir -p resources/js/views/auth
mkdir -p resources/js/views/admin
mkdir -p resources/js/views/respondent
mkdir -p resources/js/stores
mkdir -p resources/js/services
mkdir -p resources/js/types
mkdir -p resources/js/composables
mkdir -p resources/js/utils
mkdir -p resources/js/constants
```

---

## 9. TypeScript Configuration

### 9.1 Create `tsconfig.json`

```json
{
  "compilerOptions": {
    "target": "ESNext",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "strict": true,
    "jsx": "preserve",
    "resolveJsonModule": true,
    "isolatedModules": true,
    "esModuleInterop": true,
    "lib": ["ESNext", "DOM"],
    "skipLibCheck": true,
    "noEmit": true,
    "paths": {
      "@/*": ["./resources/js/*"]
    },
    "baseUrl": "."
  },
  "include": ["resources/js/**/*.ts", "resources/js/**/*.vue"],
  "exclude": ["node_modules"]
}
```

---

## 10. Axios Configuration

### 10.1 Create `resources/js/services/api.ts`

```typescript
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor - attach token
api.interceptors.request.use((config) => {
  const authStore = useAuthStore()
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }
  return config
})

// Response interceptor - handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
```

---

## 11. Router Configuration

### 11.1 Create `resources/js/router/index.ts`

```typescript
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
        // ... other admin routes
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
        // ... other respondent routes
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
```

---

## 12. Pinia Store Example

### 12.1 Create `resources/js/stores/auth.ts`

```typescript
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const user = ref<any>(null)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password?: string, type: string = 'RESPONDENT') {
    const response = await api.post('/auth/login', { email, password, type })
    token.value = response.data.data.token
    user.value = response.data.data.user
    localStorage.setItem('token', token.value!)
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  return { token, user, isAuthenticated, login, logout }
})
```

---

## 13. Development Commands

```bash
# Install dependencies
composer install
npm install

# Development (run together)
php artisan serve          # Terminal 1: Laravel backend
npm run dev                # Terminal 2: Vite dev server

# Build for production
npm run build

# Run tests
php artisan test           # Backend tests
npm run test               # Frontend tests
```

---

## 14. HTML Prototype Conversion

Untuk mengkonversi HTML prototype ke Vue components:

1. Buka file HTML di `doc/html/admin/` atau `doc/html/reponden/`
2. Copy struktur HTML ke Vue component di `resources/js/views/`
3. Ganti static content dengan dynamic data
4. Replace hardcoded links dengan Vue Router
5. Add Pinia store untuk state management
6. Add API calls menggunakan Axios

**Contoh konversi:**
```
doc/html/admin/dashboard.html → resources/js/views/admin/DashboardView.vue
doc/html/reponden/login-responden.html → resources/js/views/auth/LoginView.vue
doc/html/reponden/input-angket.html → resources/js/views/respondent/EvaluationFormView.vue
```

---

**Lanjut ke:** [05-MODULE-AUTH](05-MODULE-AUTH.md) → [06-MODULE-MASTER-DATA](06-MODULE-MASTER-DATA.md)
