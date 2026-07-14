# Backend Setup Guide
## Laravel 12 Installation & Configuration

**Target:** Setup Laravel 12 backend untuk Platform Evaluasi Kebijakan Lingkungan Sekolah

**Note:** Vue.js frontend berada di dalam Laravel project (`resources/js/`), bukan project terpisah.

---

## 1. Prerequisites

| Software | Version | Notes |
|----------|---------|-------|
| PHP | 8.3+ | Required by Laravel 12 |
| Composer | 2.x | PHP dependency manager |
| MySQL | 8.0+ | Database |
| Node.js | 18+ | For Vite asset compilation |

---

## 2. Installation

### 2.1 Create Laravel Project

```bash
# From project root (cbt/)
composer create-project laravel/laravel .

# Install frontend dependencies (Vue.js inside Laravel)
npm install
```

### 2.2 Install Dependencies

```bash
# Authentication
composer require laravel/sanctum

# Excel export
composer require maatwebsite/excel

# PDF export
composer require barryvdh/laravel-dompdf
```

---

## 3. Environment Configuration

### 3.1 Copy Environment File

```bash
cp .env.example .env
```

### 3.2 Generate Application Key

```bash
php artisan key:generate
```

### 3.3 Configure `.env`

```env
APP_NAME="CBT Platform"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cbt_platform
DB_USERNAME=root
DB_PASSWORD=

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:5173
SESSION_DRIVER=cookie

# Frontend URL (for CORS)
FRONTEND_URL=http://localhost:5173
```

---

## 4. Folder Structure Setup

Ikuti struktur dari [FOLDER-STRUCTURE.md](../FOLDER-STRUCTURE.md):

```bash
# Create directories
mkdir -p app/Http/Controllers/Api/Auth
mkdir -p app/Http/Controllers/Api/Admin
mkdir -p app/Http/Controllers/Api/Respondent
mkdir -p app/Http/Middleware
mkdir -p app/Http/Requests/Auth
mkdir -p app/Http/Requests/Admin
mkdir -p app/Http/Requests/Respondent
mkdir -p app/Models
mkdir -p app/Services/Auth
mkdir -p app/Services/Master
mkdir -p app/Services/Evaluation
mkdir -p app/Services/Report
mkdir -p app/Repositories/Interfaces
mkdir -p app/Repositories/Eloquent
mkdir -p app/Enums
mkdir -p app/Traits
mkdir -p app/Events
mkdir -p app/Listeners
mkdir -p app/Jobs
mkdir -p database/migrations
mkdir -p database/seeders
mkdir -p database/factories
mkdir -p tests/Feature
mkdir -p tests/Unit
```

---

## 5. CORS Configuration

### 5.1 Publish CORS Config

```bash
php artisan config:publish cors
```

### 5.2 Update `config/cors.php`

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

---

## 6. Sanctum Configuration

### 6.1 Publish Sanctum Config

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 6.2 Run Sanctum Migrations

```bash
php artisan migrate
```

---

## 7. API Routes

### 7.1 Create Route Files

```bash
# Routes directory already exists in Laravel 12
# Create API route groups
```

### 7.2 Route Structure (`routes/api.php`)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Admin\*;
use App\Http\Controllers\Api\Respondent\*;

// Public routes
Route::post('/auth/login', [LoginController::class, 'login']);

// Admin routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('periods', PeriodController::class);
    Route::apiResource('questionnaires', QuestionnaireController::class);
    // ... other admin routes
});

// Respondent routes
Route::prefix('evaluations')->middleware(['auth:sanctum', 'respondent'])->group(function () {
    Route::post('/start', [EvaluationController::class, 'start']);
    Route::post('/{sessionId}/answers', [EvaluationController::class, 'saveAnswer']);
    Route::post('/{sessionId}/submit', [EvaluationController::class, 'submit']);
    // ... other evaluation routes
});
```

---

## 8. Traits Setup

### 8.1 HasApiResponse Trait

```php
<?php

namespace App\Traits;

trait HasApiResponse
{
    protected function successResponse($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
```

---

## 9. Verification

```bash
# Start development server
php artisan serve

# Test API endpoint
curl http://localhost:8000/api/v1/auth/login

# Check routes
php artisan route:list
```

---

**Lanjut ke:** [04-DATABASE-SETUP](04-DATABASE-SETUP.md) → [05-MODULE-AUTH](05-MODULE-AUTH.md)
