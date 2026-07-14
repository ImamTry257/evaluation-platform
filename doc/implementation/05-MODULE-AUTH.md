# Module: Authentication
## Implementasi Sistem Autentikasi

**Referensi:**
- [API Specification - Auth](../API-SPECIFICATION.md)
- [Business Process - Proses 10](../BUSINESS-PROCESS.md)
- ADR-002 (Email-only Auth)

---

## 1. Overview

Autentikasi menggunakan Laravel Sanctum dengan dua tipe pengguna:
- **Admin:** Email + Password
- **Respondent:** Email only (auto-register jika belum ada)

---

## 2. API Endpoints

| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| POST | `/api/v1/auth/login` | Login (admin/responden) | No |
| POST | `/api/v1/auth/logout` | Logout | Yes |
| GET | `/api/v1/auth/profile` | Get user profile | Yes |

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

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required|in:ADMIN,RESPONDENT',
            'password' => 'required_if:type,ADMIN|nullable|string',
        ]);

        $email = strtolower($request->email);
        $type = strtolower($request->type);

        // Check if active period exists
        if (!$this->hasActivePeriod()) {
            return $this->errorResponse('Email not found', 401);
        }

        // Find or create user
        $user = User::where('email', $email)->first();

        if (!$user && $type === 'respondent') {
            // Auto-register respondent
            $user = User::create([
                'role' => 'respondent',
                'email' => $email,
                'name' => explode('@', $email)[0],
                'isActive' => true,
            ]);
        }

        if (!$user) {
            return $this->errorResponse('Email not found', 401);
        }

        // Verify password for admin
        if ($type === 'admin') {
            if (!$request->password || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
        }

        // Check if user is active
        if (!$user->isActive) {
            return $this->errorResponse('Account is inactive', 403);
        }

        // Check role matches
        if ($user->role !== $type) {
            return $this->errorResponse('Invalid role for this account', 403);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
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
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }

    private function hasActivePeriod(): bool
    {
        return \App\Models\EvaluationPeriod::where('isActive', true)->exists();
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
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
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
                'success' => false,
                'message' => 'Unauthorized. Respondent access required.',
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

### 4.1 Login Page (Respondent)

Convert from `doc/html/reponden/login-responden.html`:

```vue
<!-- src/views/auth/LoginView.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const email = ref('')
const isLoading = ref(false)
const error = ref('')

async function handleLogin() {
  isLoading.value = true
  error.value = ''

  try {
    await authStore.login(email.value, undefined, 'RESPONDENT')
    router.push('/respondent')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Login failed'
  } finally {
    isLoading.value = false
  }
}
</script>
```

### 4.2 Auth Store

```typescript
// src/stores/auth.ts
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

### 4.3 Route Guard

```typescript
// src/router/index.ts
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
public function test_respondent_can_login_with_email_only()
{
    // Create active period first
    EvaluationPeriod::factory()->create(['isActive' => true]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'respondent@test.com',
        'type' => 'RESPONDENT',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['token', 'user']]);
}

public function test_admin_can_login_with_password()
{
    $user = User::factory()->admin()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'type' => 'ADMIN',
    ]);

    $response->assertStatus(200);
}
```

---

**Lanjut ke:** [06-MODULE-MASTER-DATA](06-MODULE-MASTER-DATA.md)
