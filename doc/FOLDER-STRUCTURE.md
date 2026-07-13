# Folder Structure
## Platform Evaluasi Kebijakan Lingkungan Sekolah

**Version:** 1.0.0

---

# 1. Overview

Folder structure dirancang mengikuti best practices untuk Laravel dan Vue.js SPA, dengan separasi yang jelas antara backend dan frontend.

---

# 2. Root Directory Structure

```
cbt/
├── backend/                 # Laravel Backend
├── frontend/                # Vue.js Frontend
├── doc/                     # Documentation
│   ├── html/               # UI Mockups
│   ├── ADR/                # Architecture Decision Records
│   ├── SYSTEM_ARCHITECTURE.md
│   ├── DATABASE-DESIGN.md
│   ├── ERD.md
│   ├── API-SPECIFICATION.md
│   ├── FOLDER-STRUCTURE.md
│   ├── CHANGELOG.md
│   └── ...
├── .gitignore
├── README.md
└── docker-compose.yml      # Optional: for local development
```

---

# 3. Backend Structure (Laravel 12)

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── LoginController.php
│   │   │   │   │   ├── LogoutController.php
│   │   │   │   │   └── ProfileController.php
│   │   │   │   ├── Admin/
│   │   │   │   │   ├── PeriodController.php
│   │   │   │   │   ├── QuestionnaireController.php
│   │   │   │   │   ├── ComponentController.php
│   │   │   │   │   ├── SubComponentController.php
│   │   │   │   │   ├── IndicatorController.php
│   │   │   │   │   ├── QuestionController.php
│   │   │   │   │   ├── RespondentController.php
│   │   │   │   │   ├── RecommendationController.php
│   │   │   │   │   ├── SettingController.php
│   │   │   │   │   ├── MonitoringController.php
│   │   │   │   │   └── ReportController.php
│   │   │   │   └── Respondent/
│   │   │   │       ├── EvaluationController.php
│   │   │   │       ├── ResultController.php
│   │   │   │       └── RecommendationController.php
│   │   ├── Middleware/
│   │   │   ├── VerifyAdminRole.php
│   │   │   ├── VerifyRespondentRole.php
│   │   │   └── HandleCors.php
│   │   └── Requests/
│   │       ├── Auth/
│   │       │   └── LoginRequest.php
│   │       ├── Admin/
│   │       │   ├── StorePeriodRequest.php
│   │       │   ├── StoreQuestionnaireRequest.php
│   │       │   └── ...
│   │       └── Respondent/
│   │           ├── StartEvaluationRequest.php
│   │           ├── SaveAnswerRequest.php
│   │           └── SubmitEvaluationRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── EvaluationPeriod.php
│   │   ├── Questionnaire.php
│   │   ├── Component.php
│   │   ├── SubComponent.php
│   │   ├── Indicator.php
│   │   ├── Question.php
│   │   ├── ResponseSession.php
│   │   ├── ResponseAnswer.php
│   │   ├── EvaluationResult.php
│   │   ├── EvaluationResultDetail.php
│   │   ├── Recommendation.php
│   │   └── Setting.php
│   ├── Services/
│   │   ├── Auth/
│   │   │   └── AuthService.php
│   │   ├── Master/
│   │   │   ├── PeriodService.php
│   │   │   ├── QuestionnaireService.php
│   │   │   ├── ComponentService.php
│   │   │   ├── SubComponentService.php
│   │   │   ├── IndicatorService.php
│   │   │   ├── QuestionService.php
│   │   │   ├── RespondentService.php
│   │   │   ├── RecommendationService.php
│   │   │   └── SettingService.php
│   │   ├── Evaluation/
│   │   │   ├── EvaluationService.php
│   │   │   ├── ScoringEngine.php
│   │   │   ├── CategoryEngine.php
│   │   │   └── RecommendationEngine.php
│   │   ├── Report/
│   │   │   ├── ReportService.php
│   │   │   ├── ExcelExportService.php
│   │   │   └── PdfExportService.php
│   │   └── BaseService.php
│   ├── Repositories/
│   │   ├── User/
│   │   │   ├── UserRepository.php
│   │   │   └── UserRepositoryInterface.php
│   │   ├── Master/
│   │   │   ├── PeriodRepository.php
│   │   │   ├── QuestionnaireRepository.php
│   │   │   ├── ComponentRepository.php
│   │   │   ├── SubComponentRepository.php
│   │   │   ├── IndicatorRepository.php
│   │   │   ├── QuestionRepository.php
│   │   │   ├── RespondentRepository.php
│   │   │   ├── RecommendationRepository.php
│   │   │   └── SettingRepository.php
│   │   ├── Evaluation/
│   │   │   ├── ResponseSessionRepository.php
│   │   │   ├── ResponseAnswerRepository.php
│   │   │   ├── EvaluationResultRepository.php
│   │   │   └── EvaluationResultDetailRepository.php
│   │   ├── Interfaces/
│   │   │   └── RepositoryInterface.php
│   │   └── BaseRepository.php
│   ├── Exceptions/
│   │   ├── InvalidCredentialsException.php
│   │   ├── ResourceNotFoundException.php
│   │   ├── UnauthorizedException.php
│   │   ├── ValidationException.php
│   │   └── EvaluationException.php
│   ├── Events/
│   │   ├── EvaluationSubmitted.php
│   │   ├── EvaluationTimeout.php
│   │   ├── ResultsGenerated.php
│   │   └── RecommendationMatched.php
│   ├── Listeners/
│   │   ├── SendEvaluationSubmittedNotification.php
│   │   └── LogEvaluationEvent.php
│   ├── Jobs/
│   │   ├── CalculateEvaluationResult.php
│   │   ├── GenerateExcelReport.php
│   │   └── GeneratePdfReport.php
│   ├── Traits/
│   │   ├── HasApiResponse.php
│   │   ├── HasValidation.php
│   │   └── HasPagination.php
│   └── Enums/
│       ├── UserRole.php
│       ├── QuestionnaireStatus.php
│       ├── ResponseSessionStatus.php
│       ├── EvaluationCategory.php
│       └── EvaluationStatus.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── sanctum.php
│   ├── evaluation.php        # Custom config for evaluation
│   └── ...
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_evaluation_periods_table.php
│   │   ├── 2024_01_01_000003_create_questionnaires_table.php
│   │   ├── 2024_01_01_000004_create_components_table.php
│   │   ├── 2024_01_01_000005_create_sub_components_table.php
│   │   ├── 2024_01_01_000006_create_indicators_table.php
│   │   ├── 2024_01_01_000007_create_questions_table.php
│   │   ├── 2024_01_01_000008_create_response_sessions_table.php
│   │   ├── 2024_01_01_000009_create_response_answers_table.php
│   │   ├── 2024_01_01_000010_create_evaluation_results_table.php
│   │   ├── 2024_01_01_000011_create_evaluation_result_details_table.php
│   │   ├── 2024_01_01_000012_create_recommendations_table.php
│   │   └── 2024_01_01_000013_create_settings_table.php
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── QuestionnaireFactory.php
│   │   ├── ComponentFactory.php
│   │   ├── IndicatorFactory.php
│   │   ├── QuestionFactory.php
│   │   ├── ResponseSessionFactory.php
│   │   └── ...
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── PeriodSeeder.php
│       ├── QuestionnaireSeeder.php
│       ├── ComponentSeeder.php
│       ├── IndicatorSeeder.php
│       └── QuestionSeeder.php
├── routes/
│   ├── api.php               # API routes
│   ├── auth.php              # Auth routes
│   └── health.php            # Health check routes
├── storage/
│   ├── app/
│   │   ├── exports/          # Excel/PDF exports
│   │   └── uploads/
│   ├── logs/
│   └── framework/
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   ├── LoginTest.php
│   │   │   └── LogoutTest.php
│   │   ├── Admin/
│   │   │   ├── PeriodTest.php
│   │   │   ├── QuestionnaireTest.php
│   │   │   └── ...
│   │   └── Respondent/
│   │       ├── EvaluationTest.php
│   │       ├── SubmissionTest.php
│   │       └── ResultTest.php
│   ├── Unit/
│   │   ├── Services/
│   │   │   ├── ScoringEngineTest.php
│   │   │   ├── CategoryEngineTest.php
│   │   │   └── RecommendationEngineTest.php
│   │   └── Models/
│   │       └── UserTest.php
│   ├── TestCase.php
│   └── CreatesApplication.php
├── .env.example
├── .env.testing
├── artisan
├── composer.json
├── composer.lock
├── phpunit.xml
└── README.md
```

---

## 3.1 Backend Naming Conventions

### Files and Directories
- **Controllers:** Singular, PascalCase + `Controller.php`
  - Example: `EvaluationController.php`
  
- **Models:** Singular, PascalCase
  - Example: `ResponseSession.php`
  
- **Services:** Singular, PascalCase + `Service.php`
  - Example: `ScoringEngine.php`
  
- **Repositories:** Singular, PascalCase + `Repository.php`
  - Example: `QuestionRepository.php`
  
- **Requests:** Plural, PascalCase + `Request.php`
  - Example: `StoreQuestionRequest.php`
  
- **Migrations:** YYYY_MM_DD_HHMMSS + snake_case description
  - Example: `2024_01_01_000001_create_users_table.php`
  
- **Tests:** Subject + `Test.php`
  - Example: `ScoringEngineTest.php`

### Database Tables
- Plural, snake_case
  - Example: `response_sessions`, `evaluation_results`

### Database Columns
- snake_case
- Foreign keys: `{singular_table}_id`
  - Example: `response_session_id`, `indicator_id`

---

# 4. Frontend Structure (Vue.js 3 + Vite)

```
frontend/
├── src/
│   ├── App.vue
│   ├── main.ts
│   ├── vite-env.d.ts
│   ├── assets/
│   │   ├── styles/
│   │   │   ├── globals.css
│   │   │   ├── variables.css
│   │   │   └── tailwind.css
│   │   ├── images/
│   │   │   ├── logo.svg
│   │   │   └── ...
│   │   └── fonts/
│   ├── components/
│   │   ├── layout/
│   │   │   ├── Header.vue
│   │   │   ├── Sidebar.vue
│   │   │   ├── Footer.vue
│   │   │   └── MainLayout.vue
│   │   ├── common/
│   │   │   ├── Button.vue
│   │   │   ├── Modal.vue
│   │   │   ├── Alert.vue
│   │   │   ├── Pagination.vue
│   │   │   ├── Loading.vue
│   │   │   ├── Table.vue
│   │   │   └── Form.vue
│   │   ├── auth/
│   │   │   ├── LoginForm.vue
│   │   │   └── ProfileCard.vue
│   │   └── evaluation/
│   │       ├── QuestionCard.vue
│   │       ├── QuestionForm.vue
│   │       ├── ResultCard.vue
│   │       ├── RecommendationCard.vue
│   │       └── ProgressBar.vue
│   ├── views/
│   │   ├── auth/
│   │   │   └── LoginView.vue
│   │   ├── respondent/
│   │   │   ├── DashboardView.vue
│   │   │   ├── PlatformExplanationView.vue
│   │   │   ├── EvaluationListView.vue
│   │   │   ├── EvaluationFormView.vue
│   │   │   ├── ResultView.vue
│   │   │   └── RecommendationView.vue
│   │   └── admin/
│   │       ├── DashboardView.vue
│   │       ├── PeriodListView.vue
│   │       ├── PeriodFormView.vue
│   │       ├── QuestionnaireListView.vue
│   │       ├── QuestionnaireFormView.vue
│   │       ├── ComponentListView.vue
│   │       ├── ComponentFormView.vue
│   │       ├── SubComponentListView.vue
│   │       ├── IndicatorListView.vue
│   │       ├── QuestionListView.vue
│   │       ├── QuestionFormView.vue
│   │       ├── RespondentListView.vue
│   │       ├── RespondentFormView.vue
│   │       ├── RecommendationListView.vue
│   │       ├── RecommendationFormView.vue
│   │       ├── MonitoringView.vue
│   │       ├── ReportView.vue
│   │       └── SettingView.vue
│   ├── stores/
│   │   ├── index.ts
│   │   ├── auth.ts
│   │   ├── user.ts
│   │   ├── evaluation.ts
│   │   ├── period.ts
│   │   ├── questionnaire.ts
│   │   ├── component.ts
│   │   ├── respondent.ts
│   │   └── ui.ts
│   ├── services/
│   │   ├── api.ts              # Axios instance
│   │   ├── auth.service.ts
│   │   ├── period.service.ts
│   │   ├── questionnaire.service.ts
│   │   ├── component.service.ts
│   │   ├── evaluation.service.ts
│   │   ├── result.service.ts
│   │   ├── respondent.service.ts
│   │   ├── report.service.ts
│   │   └── setting.service.ts
│   ├── router/
│   │   ├── index.ts            # Router configuration
│   │   ├── routes.ts           # Route definitions
│   │   ├── middleware/
│   │   │   ├── auth.ts
│   │   │   ├── admin.ts
│   │   │   └── respondent.ts
│   │   └── guards/
│   │       ├── requireAuth.ts
│   │       ├── requireAdmin.ts
│   │       └── requireRespondent.ts
│   ├── composables/
│   │   ├── useAuth.ts
│   │   ├── useNotification.ts
│   │   ├── usePagination.ts
│   │   ├── useFetch.ts
│   │   └── useForm.ts
│   ├── types/
│   │   ├── index.ts
│   │   ├── models.ts           # Data models/interfaces
│   │   ├── api.ts              # API request/response types
│   │   ├── forms.ts            # Form data types
│   │   └── enums.ts            # Enumerations
│   ├── utils/
│   │   ├── date.ts
│   │   ├── format.ts
│   │   ├── validation.ts
│   │   ├── localStorage.ts
│   │   └── helpers.ts
│   └── constants/
│       ├── api.ts              # API endpoints
│       ├── messages.ts         # UI messages
│       ├── enums.ts            # App-wide enums
│       └── config.ts           # Configuration values
├── public/
│   ├── favicon.ico
│   └── robots.txt
├── tests/
│   ├── unit/
│   │   ├── stores/
│   │   ├── services/
│   │   ├── utils/
│   │   └── composables/
│   ├── integration/
│   │   ├── views/
│   │   ├── components/
│   │   └── ...
│   └── setup.ts
├── .env.example
├── .env.development
├── .env.production
├── index.html
├── tsconfig.json
├── tsconfig.app.json
├── tsconfig.node.json
├── vite.config.ts
├── vitest.config.ts
├── prettier.config.js
├── eslint.config.js
├── package.json
├── package-lock.json
└── README.md
```

---

## 4.1 Frontend Naming Conventions

### Files and Directories
- **Vue Components:** PascalCase + `.vue`
  - Example: `LoginForm.vue`, `ResultCard.vue`
  
- **Views:** PascalCase + `View.vue`
  - Example: `DashboardView.vue`, `EvaluationFormView.vue`
  
- **Services:** camelCase + `.service.ts`
  - Example: `auth.service.ts`, `evaluation.service.ts`
  
- **Stores:** camelCase + `.ts`
  - Example: `auth.ts`, `evaluation.ts`
  
- **Composables:** camelCase + `.ts`
  - Example: `useAuth.ts`, `useFetch.ts`
  
- **Types/Interfaces:** PascalCase + `.ts`
  - Example: `models.ts`, `api.ts`
  
- **Utils:** camelCase + `.ts`
  - Example: `validation.ts`, `format.ts`
  
- **Tests:** Component name + `.test.ts` or `.spec.ts`
  - Example: `LoginForm.test.ts`, `useAuth.spec.ts`

### Component Structure
```vue
<template>
  <!-- HTML -->
</template>

<script setup lang="ts">
  // TypeScript
</script>

<style scoped>
  /* CSS */
</style>
```

### Pinia Store Structure
```typescript
// stores/auth.ts
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({}),
  getters: {},
  actions: {}
})
```

---

# 5. Shared / Documentation

```
doc/
├── SYSTEM_ARCHITECTURE.md
├── DATABASE-DESIGN.md
├── ERD.md
├── API-SPECIFICATION.md
├── FOLDER-STRUCTURE.md
├── CHANGELOG.md
├── ADR/
│   ├── ADR-001-Evaluation-Logic.md
│   └── ADR-002-...md
├── html/
│   ├── login.html
│   ├── admin/
│   │   ├── dashboard.html
│   │   ├── period.html
│   │   ├── master-questionnaires.html
│   │   ├── master-component.html
│   │   ├── master-sub-component.html
│   │   ├── master-indicator.html
│   │   ├── master-question.html
│   │   ├── master-responden.html
│   │   ├── master-recomendation.html
│   │   ├── setting.html
│   │   ├── report-responden.html
│   │   └── report-detail-responden.html
│   └── reponden/
│       ├── platform-explanation.html
│       ├── input-angket.html
│       └── result-and-recomendation-angket.html
├── CHANGELOG.md
└── README.md
```

---

# 6. Key Directories Explained

## Backend Directories

### `app/Http/Controllers`
Controllers handle incoming HTTP requests dan mengembalikan responses.
Diorganisir berdasarkan role (Admin, Respondent).

### `app/Services`
Business logic layer yang menangani complex operations.
Contoh: `ScoringEngine.php`, `CategoryEngine.php`.

### `app/Repositories`
Data access layer yang menghandle semua database queries.
Memisahkan business logic dari database operations.

### `database/migrations`
Schema definitions yang dieksekusi secara berurutan.
Harus idempotent dan dapat di-rollback.

### `tests/`
Unit dan Feature tests untuk semua komponen sistem.
Feature tests menguji seluruh flow dari request hingga response.

## Frontend Directories

### `src/components/`
Reusable UI components yang dipecah berdasarkan fungsi (layout, common, domain-specific).

### `src/views/`
Full page components yang merepresentasikan routes.
Satu view per route.

### `src/stores/`
Pinia stores untuk state management.
Satu store per major feature.

### `src/services/`
API communication layer menggunakan Axios.
Satu service per backend module.

### `src/types/`
TypeScript interfaces dan types untuk type safety.

### `src/composables/`
Reusable logic yang dapat di-share antar components.

---

# 7. Development Workflow

## Backend Setup
```bash
cd backend
cp .env.example .env
composer install
php artisan migrate
php artisan seed
php artisan serve
```

## Frontend Setup
```bash
cd frontend
npm install
npm run dev
```

## Docker Setup (Optional)
```bash
docker-compose up -d
```

---

# 8. Git Ignore Patterns

### Backend
```
backend/vendor/
backend/.env
backend/.env.local
backend/storage/logs/
backend/storage/framework/cache/
backend/bootstrap/cache/
backend/node_modules/
```

### Frontend
```
frontend/node_modules/
frontend/dist/
frontend/.env.local
frontend/.env.*.local
```

---

# 9. Build Outputs

### Backend
- No specific build output, Laravel runs from source
- Compiled assets in `public/`

### Frontend
- Production build output: `frontend/dist/`
- Served by Laravel from `public/` directory

---

# 10. Environment Configuration

### Backend `.env`
```
APP_NAME="CBT Platform"
APP_ENV=local
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cbt_platform
DB_USERNAME=root
DB_PASSWORD=
```

### Frontend `.env.development`
```
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

### Frontend `.env.production`
```
VITE_API_BASE_URL=https://api.example.com/api/v1
```

---

# 11. Dependencies & Versions

## Backend (Laravel 12)
- PHP 8.3+
- Laravel 12
- Laravel Sanctum
- Laravel Excel
- DomPDF

## Frontend (Vue 3)
- Node.js 16+
- Vue 3
- Vite
- TypeScript
- Pinia
- Vue Router
- Axios
- Tailwind CSS

---

# 12. Future Enhancements

- Monorepo structure jika multi-app
- Shared type definitions antara backend dan frontend
- API documentation generator (Swagger/OpenAPI)
- Docker containerization
- CI/CD pipeline configuration
- Load testing directory structure
