# Module: Authentication
## Implementasi Sistem Autentikasi

**Referensi:**
- [API Specification - Auth](../API-SPECIFICATION.md)
- [Business Process - Proses 10](../BUSINESS-PROCESS.md)
- HTML Reference: `doc/html/login.html`

---

## 1. Overview

Autentikasi menggunakan Laravel Sanctum dengan **Unified Login** untuk semua tipe pengguna:

```
┌─────────────────────────────────────────────────────────┐
│                    LOGIN UNIFIED                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│   ┌─────────────┐      ┌─────────────┐                 │
│   │ Admin/      │      │  Respondent │                 │
│   │ SuperAdmin  │      │  Username + │                 │
│   │ Email +     │      │  Password   │                 │
│   │ Password    │      └──────┬──────┘                 │
│   └──────┬──────┘             │                        │
│          │                    │                        │
│          ▼                    ▼                        │
│   ┌─────────────┐      ┌─────────────┐                 │
│   │   /admin    │      │ /respondent │                 │
│   │  Dashboard  │      │  Angket     │                 │
│   └─────────────┘      └─────────────┘                 │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Role Hierarchy:**
```
SUPERADMIN > ADMIN > RESPONDENT
```

**Flow:**
1. User mengakses `/login` (respondent) atau `/login/admin` (admin/superadmin)
2. Memasukkan credentials:
   - Respondent: Username + Password
   - Admin/Superadmin: Email + Password
3. Sistem mengidentifikasi role berdasarkan credential
4. Redirect ke dashboard sesuai role:
   - Admin → `/admin`
   - Superadmin → `/admin` (dengan module Master Admin)
   - Respondent → `/respondent`

---

## 2. API Endpoints

| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| POST | `/api/v1/auth/register` | Register respondent baru | No |
| POST | `/api/v1/auth/login` | Login respondent | No |
| POST | `/api/v1/auth/login-admin` | Login admin/superadmin | No |
| POST | `/api/v1/auth/logout` | Logout | Yes |
| GET | `/api/v1/auth/profile` | Get user profile | Yes |

**Register Request:**
```json
{
  "name": "Budi Santoso",
  "username": "budi_santoso",
  "email": "budi@sekolah.id",
  "password": "password123",
  "passwordConfirmation": "password123"
}
```

**Register Validation:**
| Field | Rules |
|-------|-------|
| name | required, string, max:255 |
| username | required, string, max:255, unique:users,username |
| email | required, email, unique:users,email |
| password | required, string, min:8, same:passwordConfirmation |

**Login Request:**
```json
{
  "username": "user@sekolah.id",
  "password": "password123"
}
```

**Response Format (Standard):**

Success:
```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

Error:
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": []
}
```

**Login Response (Success):**
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "name": "Admin User",
      "username": "admin",
      "email": "admin@sekolah.id",
      "role": "ADMIN"
    }
  }
}
```

**Login Response (Error - Invalid Credentials):**
```json
{
  "status": false,
  "message": "Username atau password salah",
  "errors": []
}
```

**Login Response (Error - Validation):**
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "username": ["The username field is required."],
    "password": ["The password field is required."]
  }
}
```

**Login Response (Error - Inactive Account):**
```json
{
  "status": false,
  "message": "Akun tidak aktif",
  "errors": []
}
```

---

**Login Admin Request:**
```json
{
  "email": "admin@sekolah.id",
  "password": "password123"
}
```

**Login Admin Response (Success):**
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "name": "Admin User",
      "username": "admin",
      "email": "admin@sekolah.id",
      "role": "ADMIN"
    }
  }
}
```

**Login Admin Response (Error - Invalid Credentials):**
```json
{
  "status": false,
  "message": "Email atau password salah",
  "errors": []
}
```

**Login Admin Response (Error - Non-Admin Role):**
```json
{
  "status": false,
  "message": "Akun ini tidak memiliki akses admin",
  "errors": []
}
```

**Login Admin Response (Error - Validation):**
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

---

## 3. Backend Implementation

### 3.1 LoginController

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use HasApiResponse;

    /**
     * Unified login for Admin and Respondent
     * 
     * Flow:
     * 1. Validate username & password
     * 2. Find user by username (which is email)
     * 3. Verify password
     * 4. Return token with user role
     * 5. Frontend redirects based on role
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = strtolower($request->username);
        $password = $request->password;

        // Find user by username
        $user = User::where('username', $username)->first();

        if (!$user) {
            return $this->errorResponse('Username atau password salah', 401);
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            return $this->errorResponse('Username atau password salah', 401);
        }

        // Check if user is active
        if (!$user->isActive) {
            return $this->errorResponse('Akun tidak aktif', 403);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logged out successfully');
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }
}
```

### 3.2 Middleware

#### VerifyAdminRole

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Admin access required.',
                'errors' => [],
            ], 403);
        }

        return $next($request);
    }
}
```

#### VerifyRespondentRole

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyRespondentRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'respondent') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Respondent access required.',
                'errors' => [],
            ], 403);
        }

        return $next($request);
    }
}
```

### 3.3 Register Middleware

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\VerifyAdminRole::class,
        'respondent' => \App\Http\Middleware\VerifyRespondentRole::class,
    ]);
})
```

---

## 4. Frontend Implementation

### 4.1 Login Page (Unified)

Convert from `doc/html/login.html`:

```vue
<!-- src/views/auth/LoginView.vue -->
<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const showPassword = ref(false)
const isLoading = ref(false)
const error = ref('')

const form = reactive({
  username: '',
  password: '',
  rememberMe: false
})

async function handleLogin() {
  isLoading.value = true
  error.value = ''

  try {
    const response = await authStore.login(form.username, form.password)
    
    // Redirect based on user role
    const role = response.user.role
    if (role === 'ADMIN') {
      router.push('/admin')
    } else if (role === 'RESPONDENT') {
      router.push('/respondent')
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Login failed'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="login-container">
    <!-- Left Column: Illustration -->
    <section class="hidden lg:flex ...">
      <!-- Illustration content from login.html -->
    </section>

    <!-- Right Column: Login Form -->
    <main class="w-full lg:w-1/2 ...">
      <form @submit.prevent="handleLogin">
        <!-- Username Field -->
        <div class="space-y-1.5">
          <label>Nama Pengguna</label>
          <input 
            v-model="form.username"
            type="text"
            placeholder="nama@sekolah.id"
            required
          />
        </div>

        <!-- Password Field -->
        <div class="space-y-1.5">
          <label>Kata Sandi</label>
          <input 
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="••••••••"
            required
          />
          <button @click="showPassword = !showPassword" type="button">
            {{ showPassword ? 'visibility' : 'visibility_off' }}
          </button>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
          <label>
            <input v-model="form.rememberMe" type="checkbox" />
            <span>Ingat Saya</span>
          </label>
          <a href="#">Lupa Kata Sandi?</a>
        </div>

        <!-- Submit Button -->
        <button type="submit" :disabled="isLoading">
          <span v-if="isLoading">Memproses...</span>
          <span v-else>Masuk ke Platform</span>
        </button>
      </form>
    </main>
  </div>
</template>
```

### 4.2 Auth Store

```typescript
// src/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const user = ref<{ id: number; name: string; email: string; role: string } | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'ADMIN')
  const isRespondent = computed(() => user.value?.role === 'RESPONDENT')

  async function login(username: string, password: string) {
    const response = await api.post('/auth/login', { username, password })
    token.value = response.data.data.token
    user.value = response.data.data.user
    localStorage.setItem('token', token.value!)
    return response.data.data
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  return { 
    token, 
    user, 
    isAuthenticated, 
    isAdmin,
    isRespondent,
    login, 
    logout 
  }
})
```

### 4.3 Route Guard

```typescript
// src/router/index.ts
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Redirect to login if not authenticated
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  }
  // Redirect to dashboard if already logged in
  else if (to.meta.guest && authStore.isAuthenticated) {
    next(authStore.user?.role === 'ADMIN' ? '/admin' : '/respondent')
  }
  // Check role-based access
  else if (to.meta.role && to.meta.role !== authStore.user?.role) {
    // Unauthorized - redirect to appropriate dashboard
    next(authStore.user?.role === 'ADMIN' ? '/admin' : '/respondent')
  }
  else {
    next()
  }
})
```

---

## 5. Rate Limiting

```php
// routes/api.php
Route::post('/auth/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,15'); // 5 attempts per 15 minutes
```

---

## 6. Testing

```php
// tests/Feature/Auth/LoginTest.php
public function test_admin_can_login_with_username_password()
{
    $user = User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'admin@test.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['token', 'user' => ['id', 'name', 'email', 'role']]
        ])
        ->assertJson([
            'status' => true,
            'message' => 'Login successful',
        ]);

    $this->assertEquals('ADMIN', $response->json('data.user.role'));
}

public function test_respondent_can_login_with_username_password()
{
    $user = User::factory()->respondent()->create([
        'email' => 'respondent@test.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'respondent@test.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'data' => ['user' => ['role' => 'RESPONDENT']]
        ]);
}

public function test_wrong_password_returns_error()
{
    $user = User::factory()->admin()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'admin@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => false,
            'message' => 'Username atau password salah',
        ]);
}

public function test_validation_error_returns_errors_array()
{
    $response = $this->postJson('/api/v1/auth/login', [
        'username' => '',
        'password' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'status',
            'message',
            'errors'
        ])
        ->assertJson([
            'status' => false,
        ]);
}
```

---

## 7. HTML Reference Files

| File | Deskripsi | Status |
|------|-----------|--------|
| `doc/html/login.html` | Halaman login unified (Admin & Respondent) | ✅ Active |
| `doc/html/register.html` | Halaman registrasi respondent | ✅ Active |
| `doc/html/about-us.html` | Halaman About Us / Tim Pengembang | ✅ Active |
| `doc/html/reponden/login-tidak-dipakai.html` | Login respondent lama (email only) | ❌ Deprecated |

### 7.1 Login Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                        USER ACCESS                          │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    /login (login.html)                      │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Username: [_______________]                        │   │
│  │  Password: [_______________]                        │   │
│  │                                                     │   │
│  │  [ ] Ingat Saya        Lupa Kata Sandi?            │   │
│  │                                                     │   │
│  │  ┌─────────────────────────────────────────────┐   │   │
│  │  │        Masuk ke Platform →                  │   │   │
│  │  └─────────────────────────────────────────────┘   │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    API VALIDATION                           │
│  • Check username (email) exists                           │
│  • Verify password                                         │
│  • Return token + user role                                │
└─────────────────────────────────────────────────────────────┘
                            │
              ┌─────────────┴─────────────┐
              ▼                           ▼
┌──────────────────────┐    ┌──────────────────────┐
│   role === 'ADMIN'   │    │ role === 'RESPONDENT' │
└──────────┬───────────┘    └──────────┬───────────┘
           │                           │
           ▼                           ▼
┌──────────────────────┐    ┌──────────────────────┐
│      /admin          │    │     /respondent       │
│   Admin Dashboard    │    │   Angket & Hasil      │
└──────────────────────┘    └──────────────────────┘
```

---

**Lanjut ke:** [06-MODULE-MASTER-DATA](06-MODULE-MASTER-DATA.md)
