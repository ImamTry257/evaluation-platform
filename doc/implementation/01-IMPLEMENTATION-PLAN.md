# Implementation Plan
## Platform Evaluasi Kebijakan Lingkungan Sekolah

**Version:** 1.13.0
**Status:** In Progress
**Start Date:** 14 Juli 2026

---

## 1. Overview

Dokumen ini menjelaskan rencana implementasi development untuk Platform Evaluasi Kebijakan Lingkungan Sekolah. Project ini menggunakan Laravel 13 (backend) dan Vue 3 (frontend) dengan arsitektur REST API.

**Referensi Dokumentasi:**
- [System Architecture](../SYSTEM_ARCHITECTURE.md)
- [Folder Structure](../FOLDER-STRUCTURE.md)
- [API Specification](../API-SPECIFICATION.md)
- [ERD](../ERD.md)
- [Database Design](../DATABASE-DESIGN.md)
- [Business Process](../BUSINESS-PROCESS.md)

**HTML Reference:**
- `doc/html/login.html` - Login unified (Admin & Respondent)
- `doc/html/register.html` - Register Respondent
- `doc/html/about-us.html` - About Us

---

## 2. Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend | Laravel | 13 |
| PHP | PHP | 8.3+ |
| Auth | Laravel Sanctum | - |
| Frontend | Vue.js | 3 |
| Build Tool | Vite | - |
| Language | TypeScript | - |
| State Management | Pinia | - |
| Router | Vue Router | - |
| HTTP Client | Axios | - |
| CSS Framework | Tailwind CSS | - |
| Database | MySQL | 8.0+ |
| Export | Laravel Excel, DomPDF | - |
| Testing (Backend) | PHPUnit | - |
| Testing (Frontend) | Vitest | - |

---

## 3. Development Phases

### Phase 1: Project Scaffolding (Q3 2026 - Juli Minggu 3)
**Target:** Setup project structure dan environment

| Task | Deskripsi | Status |
|------|-----------|--------|
| Laravel scaffolding | `composer create-project laravel/laravel .` | ⬜ |
| Vue.js setup | Install Vue, Pinia, Router, Axios, TailwindCSS di `resources/js/` | ⬜ |
| Install backend dependencies | Sanctum, Laravel Excel, DomPDF | ⬜ |
| Konfigurasi environment | `.env` file, database connection | ⬜ |
| Setup folder structure | Ikuti FOLDER-STRUCTURE.md | ⬜ |
| Vite configuration | `vite.config.ts` dengan laravel-vite-plugin | ⬜ |

**Deliverable:** Single Laravel project dengan Vue.js di dalamnya siap dikembangkan

---

### Phase 2: Database & Models (Q3 2026 - Juli Minggu 4)
**Target:** Database schema dan Eloquent models

| Task | Deskripsi | Status |
|------|-----------|--------|
| Migrations | 13 tabel sesuai ERD | ⬜ |
| Models | 13 Eloquent models | ⬜ |
| Relationships | Definisi relasi antar models | ⬜ |
| Seeders | Admin default, sample data | ⬜ |
| Factory | Model factories untuk testing | ⬜ |

**Deliverable:** Database siap, models berfungsi

**Referensi:** [04-DATABASE-SETUP](04-DATABASE-SETUP.md), [ERD](../ERD.md)

---

### Phase 3: Authentication (Q3 2026 - Agustus Minggu 1)
**Target:** Sistem autentikasi Admin dan Responden dengan Unified Login

| Task | Deskripsi | Status |
|------|-----------|--------|
| Login endpoint | POST /auth/login (unified: admin + respondent) | ✅ |
| Register endpoint | POST /auth/register (respondent only) | ✅ |
| Logout endpoint | POST /auth/logout (revoke token) | ✅ |
| Profile endpoint | GET /auth/profile (get user data) | ✅ |
| Middleware | VerifyAdminRole, VerifyRespondentRole | ✅ |
| ~~Auto-register~~ | ~~Responden auto-register via email~~ (dihapus) | ❌ |
| Rate limiting | 5 attempts per 15 minutes | ✅ |
| Frontend login page | login.html → LoginView.vue (unified) | ✅ |
| Route guards | Auth guard, role-based redirect | ✅ |
| Response format | Standard: `{ status, message, data/errors }` | ✅ |
| Sanctum setup | Personal access tokens migration | ✅ |

**Deliverable:** Login berfungsi untuk kedua role dengan satu halaman login

**Referensi:** [05-MODULE-AUTH](05-MODULE-AUTH.md)

#### Login Flow (New - Unified)

```
┌─────────────────────────────────────────────────────────┐
│                    /login (Unified)                      │
│                                                         │
│   Username: [_______________]                           │
│   Password: [_______________]                           │
│                                                         │
│   [Masuk ke Platform →]                                 │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              Backend: /api/v1/auth/login                 │
│  • Find user by username (email)                        │
│  • Verify password                                      │
│  • Return token + role                                  │
└─────────────────────────────────────────────────────────┘
                          │
            ┌─────────────┴─────────────┐
            ▼                           ▼
    role === 'ADMIN'           role === 'RESPONDENT'
            │                           │
            ▼                           ▼
┌──────────────────────┐    ┌──────────────────────┐
│      /admin          │    │     /respondent       │
│   Admin Dashboard    │    │   Angket & Hasil      │
└──────────────────────┘    └──────────────────────┘
```

#### Perubahan dari Flow Lama

| Aspek | Flow Lama | Flow Baru |
|-------|-----------|-----------|
| Input | Email only (respondent), Email + Password (admin) | Username (email) + Password (semua) |
| Halaman Login | Terpisah (login-admin, login-responden) | **Unified** (login.html) |
| Auto-register | ✅ Respondent auto-register | ❌ **Dihapus** - harus daftar manual |
| Frontend | 2 file HTML terpisah | 1 file HTML unified |

---

### Phase 4: Master Data CRUD (Q3 2026 - Agustus Minggu 2-3)
**Target:** CRUD operations untuk semua master data

| Task | Deskripsi | Status |
|------|-----------|--------|
| Periode | Controller, Routes, Validation, Pagination | ✅ |
| Kuesioner | Controller, Routes, Validation, Relations | ✅ |
| Komponen | Controller, Routes, Validation, Relations | ✅ |
| Sub Komponen | Controller, Routes, Validation, Relations | ✅ |
| Indikator | Controller, Routes, Validation, Relations | ✅ |
| Pertanyaan | Controller, Routes, Validation, Relations | ✅ |
| Responden | Controller, Routes, Validation, Relations | ✅ |
| Rekomendasi | Controller, Routes, Validation, Relations | ✅ |

**Deliverable:** Semua master data bisa di-CRUD

**Referensi:** [06-MODULE-MASTER-DATA](06-MODULE-MASTER-DATA.md)

---

### Phase 5: Evaluation Engine (Q3 2026 - Agustus Minggu 4 - September Minggu 1)
**Target:** Sesi evaluasi, pengisian angket, scoring

| Task | Deskripsi | Status |
|------|-----------|--------|
| Start evaluation | POST /evaluations/start | ✅ |
| Save answer | POST /evaluations/{id}/answers | ✅ |
| Auto-save | Periodic auto-save mechanism | ✅ |
| Resume session | Resume interrupted session | ✅ |
| Submit evaluation | POST /evaluations/{id}/submit | ✅ |
| Scoring engine | Likert 1-7, indicator scoring | ✅ |
| Category engine | Kategori A-E (Standar Baku Ideal) | ✅ |
| Recommendation engine | Map recommendations per indicator | ✅ |
| ~~Frontend evaluation flow~~ | ~~Input angket page, timer, progress~~ | N/A (Frontend) |

**Deliverable:** Responden bisa mengisi evaluasi lengkap

**Referensi:** [07-MODULE-EVALUATION](07-MODULE-EVALUATION.md), ADR-001

---

### Phase 6: Results & Recommendations (Q3 2026 - September Minggu 2)
**Target:** Menampilkan hasil evaluasi dan rekomendasi

| Task | Deskripsi | Status |
|------|-----------|--------|
| Result endpoint | GET /evaluations/{id}/results | ⬜ |
| Recommendation display | Rekomendasi per indikator | ⬜ |
| ~~Frontend result page~~ | ~~result-and-recomendation → Vue~~ | N/A (Frontend) |

**Deliverable:** Hasil dan rekomendasi tampil dengan benar

---

### Phase 7: Monitoring & Reports (Q3-Q4 2026 - September Minggu 3-4)
**Target:** Monitoring sesi dan export laporan

| Task | Deskripsi | Status |
|------|-----------|--------|
| Monitoring endpoint | GET /admin/sessions, filter | ⬜ |
| Report endpoint | GET /admin/reports | ⬜ |
| Export Excel | Laravel Excel integration | ⬜ |
| Export PDF | DomPDF integration | ⬜ |
| ~~Frontend monitoring~~ | ~~Monitoring page → Vue~~ | N/A (Frontend) |
| ~~Frontend reports~~ | ~~Report pages → Vue~~ | N/A (Frontend) |

**Deliverable:** Admin bisa monitoring dan export laporan

**Referensi:** [08-MODULE-MONITORING](08-MODULE-MONITORING.md)

---

### Phase 8: Settings & Polish (Q4 2026 - Oktober Minggu 1)
**Target:** Pengaturan sistem dan finishing

| Task | Deskripsi | Status |
|------|-----------|--------|
| Settings endpoint | GET/PUT /admin/settings | ⬜ |
| ~~Settings page~~ | ~~Setting page → Vue~~ | N/A (Frontend) |
| Error handling | Global error handling, validation | ⬜ |
| ~~Loading states~~ | ~~Loading indicators, skeletons~~ | N/A (Frontend) |
| ~~Responsive design~~ | ~~Mobile responsiveness~~ | N/A (Frontend) |
| ~~Dark mode (optional)~~ | ~~Theme switching~~ | N/A (Frontend) |

**Deliverable:** Aplikasi siap untuk testing

**Referensi:** [09-MODULE-SETTINGS](09-MODULE-SETTINGS.md)

---

## 4. Architecture Pattern

```
┌─────────────────────────────────────────────────────────┐
│                    FRONTEND (Vue 3)                      │
│  Vue Router → Pinia Store → Axios → Laravel API         │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                    BACKEND (Laravel 13)                  │
│  Middleware → Controller → Service → Repository → Eloquent│
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                    DATABASE (MySQL)                      │
└─────────────────────────────────────────────────────────┘
```

---

## 5. Module Documentation

| Module | File | Deskripsi |
|--------|------|-----------|
| Authentication | [05-MODULE-AUTH](05-MODULE-AUTH.md) | Login, register, middleware |
| Master Data | [06-MODULE-MASTER-DATA](06-MODULE-MASTER-DATA.md) | CRUD semua master data |
| Evaluation | [07-MODULE-EVALUATION](07-MODULE-EVALUATION.md) | Sesi evaluasi, angket, scoring |
| Monitoring | [08-MODULE-MONITORING](08-MODULE-MONITORING.md) | Monitoring, laporan, export |
| Settings | [09-MODULE-SETTINGS](09-MODULE-SETTINGS.md) | Pengaturan sistem |

---

## 6. Setup Guides

| Guide | File | Deskripsi |
|-------|------|-----------|
| Backend Setup | [02-BACKEND-SETUP](02-BACKEND-SETUP.md) | Laravel 13 installation |
| Frontend Setup | [03-FRONTEND-SETUP](03-FRONTEND-SETUP.md) | Vue 3 + Vite setup |
| Database Setup | [04-DATABASE-SETUP](04-DATABASE-SETUP.md) | MySQL, migrations, seeding |

---

## 7. Estimated Timeline

```
Q3 2026 (Juli-September):
├── Juli Minggu 3-4:    Phase 1 (Scaffolding) + Phase 2 (Database)
├── Agustus Minggu 1:   Phase 3 (Authentication)
├── Agustus Minggu 2-3: Phase 4 (Master Data CRUD)
├── Agustus Minggu 4:   Phase 5 (Evaluation Engine - start)
├── September Minggu 1: Phase 5 (Evaluation Engine - finish)
├── September Minggu 2: Phase 6 (Results & Recommendations)
└── September Minggu 3-4: Phase 7 (Monitoring & Reports)

Q4 2026 (Oktober-Desember):
├── Oktober Minggu 1:   Phase 8 (Settings & Polish)
├── Oktober-November:   Testing (Phase 9)
└── Desember:           Deployment (Phase 10)
```

---

## 8. Definition of Done

Setiap task dianggap selesai jika:
1. Code sudah ditulis dan berfungsi
2. Sudah diuji manual
3. Tidak ada error di console/log
4. Konsisten dengan dokumentasi (API spec, ERD)
5. Mengikuti naming conventions dari FOLDER-STRUCTURE.md

---

## 9. Changelog & Notes

### v1.1.0 - Flow Login Unified (15 Juli 2026)

**Perubahan:**
1. Login flow diubah dari **email-only** menjadi **username + password**
2. **Auto-register respondent dihapus** - respondent harus didaftarkan manual oleh admin
3. Frontend login **digabung** menjadi satu halaman unified (`login.html`)
4. Referensi HTML lama (`login-admin.html`, `login-responden.html`) tidak lagi digunakan

**File yang Diupdate:**
- `doc/implementation/05-MODULE-AUTH.md` - Updated auth module docs
- `doc/implementation/01-IMPLEMENTATION-PLAN.md` - Updated implementation plan

**HTML References (Aktif):**
| File | Status | Deskripsi |
|------|--------|-----------|
| `doc/html/login.html` | ✅ Aktif | Login unified untuk admin & respondent |
| `doc/html/register.html` | ✅ Aktif | Form registrasi respondent |
| `doc/html/about-us.html` | ✅ Aktif | Halaman About Us |
| `doc/html/reponden/login-tidak-dipakai.html` | ❌ Deprecated | Login respondent lama (email only) |
| `doc/html/reponden/platform-explanation.html` | ✅ Aktif | Penjelasan platform untuk respondent |
| `doc/html/reponden/input-angket.html` | ✅ Aktif | Halaman pengisian angket |
| `doc/html/reponden/result-and-recomendation-angket.html` | ✅ Aktif | Hasil & rekomendasi |

**ADR Documents:**
- `doc/ADR/ADR-002-Responden-Email-Only-Authentication.md` → **DEPRECATED** (flow lama)

### v1.2.0 - Auth API Implementation (15 Juli 2026)

**Backend Implementation:**
1. `routes/api.php` - API routes untuk auth (login, logout, profile)
2. `app/Http/Controllers/Api/Auth/AuthController.php` - Auth controller
3. `app/Traits/HasApiResponse.php` - Standard response format
4. `app/Http/Middleware/VerifyAdminRole.php` - Admin role check
5. `app/Http/Middleware/VerifyRespondentRole.php` - Respondent role check
6. `bootstrap/app.php` - Register API routes + custom exception handler

**Frontend Implementation:**
1. `resources/js/stores/auth.ts` - Updated login/logout functions
2. `resources/js/views/auth/LoginView.vue` - Full login form implementation

**Response Format:**
```json
// Success
{
  "status": true,
  "message": "Success",
  "data": {}
}

// Error
{
  "status": false,
  "message": "Validation failed",
  "errors": []
}
```

**Postman Collection:**
- `doc/postman/PolicyEval-Auth.postman_collection.json`
- `doc/postman/PolicyEval-Local.postman_environment.json`

### v1.3.0 - Periode API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Admin/PeriodeController.php` - CRUD controller
2. `app/Traits/HasApiResponse.php` - Added `listResponse()` for paginated data
3. `routes/api.php` - Added admin periode routes
4. `bootstrap/app.php` - Added rate limit error handler

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/periods` | List periods (paginated) |
| POST | `/api/v1/admin/periods` | Create period |
| GET | `/api/v1/admin/periods/{id}` | Get single period |
| PUT | `/api/v1/admin/periods/{id}` | Update period |
| DELETE | `/api/v1/admin/periods/{id}` | Delete period |

**Features:**
- Pagination with max limit (100 items)
- Search by name
- Filter by isActive status
- Validation (name, startDate, endDate)
- Date validation (endDate >= startDate)
- Admin-only access (middleware)

**Bug Fixes:**
- Added max limit on pagination (max 100)
- Fixed rate limit error format to match standard response

**Postman Collection:**
- `doc/postman/PolicyEval-All.postman_collection.json` - All endpoints

### v1.4.0 - Kuesioner API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Admin/KuesionerController.php` - CRUD controller
2. `app/Models/Questionnaire.php` - Fixed relationship foreign key
3. `routes/api.php` - Added kuesioner routes

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/questionnaires` | List questionnaires (paginated) |
| POST | `/api/v1/admin/questionnaires` | Create questionnaire |
| GET | `/api/v1/admin/questionnaires/{id}` | Get single questionnaire |
| PUT | `/api/v1/admin/questionnaires/{id}` | Update questionnaire |
| DELETE | `/api/v1/admin/questionnaires/{id}` | Delete questionnaire |

**Features:**
- Pagination with max limit (100 items)
- Search by title
- Filter by status (draft/published/closed)
- Filter by evaluationPeriodId
- Relation: evaluationPeriod (belongsTo)
- Validation (evaluationPeriodId, title, durationMinutes, status)
- Status enum validation (draft, published, closed)

**Bug Fix:**
- Fixed Questionnaire model relationship (evaluationPeriodId foreign key)

### v1.5.0 - Komponen API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Admin/KomponenController.php` - CRUD controller
2. `app/Models/Component.php` - Fixed relationship foreign key
3. `routes/api.php` - Added komponen routes

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/components` | List components (paginated) |
| POST | `/api/v1/admin/components` | Create component |
| GET | `/api/v1/admin/components/{id}` | Get single component |
| PUT | `/api/v1/admin/components/{id}` | Update component |
| DELETE | `/api/v1/admin/components/{id}` | Delete component |

**Features:**
- Pagination with max limit (100 items)
- Search by name
- Filter by questionnaireId
- Relation: questionnaire (belongsTo)
- Sorted by orderNumber

### v1.6.0 - Sub Komponen API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Admin/SubKomponenController.php` - CRUD controller
2. `app/Models/SubComponent.php` - Fixed relationship foreign key
3. `routes/api.php` - Added sub-components routes

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/sub-components` | List sub-components (paginated) |
| POST | `/api/v1/admin/sub-components` | Create sub-component |
| GET | `/api/v1/admin/sub-components/{id}` | Get single sub-component |
| PUT | `/api/v1/admin/sub-components/{id}` | Update sub-component |
| DELETE | `/api/v1/admin/sub-components/{id}` | Delete sub-component |

**Features:**
- Pagination with max limit (100 items)
- Search by name
- Filter by componentId
- Relation: component (belongsTo)
- Sorted by orderNumber

### v1.7.0 - Register API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Auth/AuthController.php` - Added register method
2. `routes/api.php` - Added register route

**API Endpoint:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Register new respondent |

**Request Body:**
```json
{
  "name": "Budi Santoso",
  "email": "budi@sekolah.id",
  "password": "password123",
  "passwordConfirmation": "password123"
}
```

**Validation Rules:**
| Field | Rules |
|-------|-------|
| name | required, string, max:255 |
| email | required, email, unique:users,email |
| password | required, string, min:8, same:passwordConfirmation |

**Features:**
- Auto-generate token after registration
- Auto-set role to `respondent`
- Auto-set isActive to `true`
- Email unique validation
- Password confirmation validation
- Rate limit: 10 requests per 15 minutes

### v1.8.0 - Indikator API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Admin/IndikatorController.php` - CRUD controller
2. `routes/api.php` - Added indicators routes

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/indicators` | List indicators (paginated) |
| POST | `/api/v1/admin/indicators` | Create indicator |
| GET | `/api/v1/admin/indicators/{id}` | Get single indicator |
| PUT | `/api/v1/admin/indicators/{id}` | Update indicator |
| DELETE | `/api/v1/admin/indicators/{id}` | Delete indicator |

**Features:**
- Pagination with max limit (100 items)
- Search by name
- Filter by subComponentId
- Relation: subComponent (belongsTo), questions (hasMany), recommendations (hasMany)
- Sorted by orderNumber
- Validation (subComponentId, name, orderNumber)

**Bug Fixes - Explicit Foreign Key in Relationships:**
All Eloquent models updated to use explicit foreign keys (camelCase) matching migration column names:
- `Indicator` - subComponent(), questions(), recommendations(), evaluationResultDetails()
- `Question` - indicator(), responseAnswers()
- `Component` - subComponents()
- `SubComponent` - indicators()
- `EvaluationResult` - responseSession(), details()
- `EvaluationResultDetail` - evaluationResult(), indicator()
- `Recommendation` - indicator()
- `ResponseAnswer` - responseSession(), question()
- `ResponseSession` - user(), questionnaire(), answers()
- `EvaluationPeriod` - questionnaires()
- `User` - responseSessions()
- `Questionnaire` - components(), responseSessions()

**Postman Collection:**
- `doc/postman/PolicyEval-All.postman_collection.json` - Added Indikator endpoints

### v1.9.0 - Question API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Admin/PertanyaanController.php` - CRUD controller
2. `routes/api.php` - Added questions routes

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/questions` | List questions (paginated) |
| POST | `/api/v1/admin/questions` | Create question |
| GET | `/api/v1/admin/questions/{id}` | Get single question |
| PUT | `/api/v1/admin/questions/{id}` | Update question |
| DELETE | `/api/v1/admin/questions/{id}` | Delete question |

**Features:**
- Pagination with max limit (100 items)
- Search by questionText
- Filter by indicatorId
- Relation: indicator (belongsTo)
- Sorted by orderNumber
- Validation (indicatorId, questionText, weight, orderNumber)

**Postman Collection:**
- `doc/postman/PolicyEval-All.postman_collection.json` - Added Question endpoints

### v1.10.0 - Respondent API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Admin/RespondenController.php` - CRUD controller
2. `routes/api.php` - Added respondents routes

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/respondents` | List respondents (paginated) |
| POST | `/api/v1/admin/respondents` | Create respondent |
| GET | `/api/v1/admin/respondents/{id}` | Get single respondent |
| PUT | `/api/v1/admin/respondents/{id}` | Update respondent |
| DELETE | `/api/v1/admin/respondents/{id}` | Delete respondent |

**Features:**
- Pagination with max limit (100 items)
- Search by name, username, or email
- Filter by isActive status
- Password is optional on update (only updated if provided)
- Delete protection: cannot delete respondent with existing evaluation sessions
- Password hashed on create/update
- Username/email unique validation (ignore self on update)
- Response excludes password field

**Postman Collection:**
- `doc/postman/PolicyEval-All.postman_collection.json` - Added Respondent endpoints

### v1.11.0 - Recommendation API Implementation (16 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Admin/RekomendasiController.php` - CRUD controller
2. `routes/api.php` - Added recommendations routes

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/recommendations` | List recommendations (paginated) |
| POST | `/api/v1/admin/recommendations` | Create recommendation |
| GET | `/api/v1/admin/recommendations/{id}` | Get single recommendation |
| PUT | `/api/v1/admin/recommendations/{id}` | Update recommendation |
| DELETE | `/api/v1/admin/recommendations/{id}` | Delete recommendation |

**Features:**
- Pagination with max limit (100 items)
- Search by recommendationText
- Filter by indicatorId
- Filter by category (A/B/C/D/E)
- Relation: indicator (belongsTo)
- Sorted by minScore
- Validation:
  - minScore/maxScore required, numeric, min:0
  - maxScore must be >= minScore (gte validation)
  - category must be single char A-E (size:1 + in validation)

**Postman Collection:**
- `doc/postman/PolicyEval-All.postman_collection.json` - Added Recommendation endpoints

### v1.12.0 - Evaluation Engine API Implementation (17 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Respondent/EvaluasiController.php` - Evaluation engine controller
2. `routes/api.php` - Added evaluation routes

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/evaluations/start` | Start or resume evaluation session |
| GET | `/api/v1/evaluations/{sessionId}` | Get session details |
| POST | `/api/v1/evaluations/{sessionId}/answers` | Save/update answer |
| POST | `/api/v1/evaluations/{sessionId}/submit` | Submit evaluation |
| GET | `/api/v1/evaluations/{sessionId}/results` | Get results |

**Features:**
- Start new session or resume existing inProgress session
- Load full questionnaire structure (components → sub-components → indicators → questions)
- Save answers with upsert (unique constraint: sessionId + questionId)
- Score validation: Likert scale 1-7
- Submit validation: all questions must be answered
- Scoring engine:
  - Indicator score: weighted average `Σ(score × weight) / Σ(weight)`
  - Overall score: weighted average of all indicators
  - Percentage: `(score / 7) × 100` (Likert 1-7 scale)
- Category engine: A (≥90%), B (≥75%), C (≥60%), D (≥45%), E (<45%)
- Recommendation engine: map recommendations per indicator based on score range
- Conclusion text based on category

**Bug Fixes:**
- Fixed ResponseSession `result()` HasOne relationship - added explicit foreign key `responseSessionId`
- Fixed scoring engine percentage calculation - was using wrong formula (now correctly divides by max score 7)

**Postman Collection:**
- `doc/postman/PolicyEval-All.postman_collection.json` - Added Evaluation endpoints

### v1.13.0 - Auto-Save API Implementation (17 Juli 2026)

**Backend Implementation:**
1. `app/Http/Controllers/Api/Respondent/EvaluasiController.php` - Added autoSave method
2. `routes/api.php` - Added autosave route

**API Endpoint:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/evaluations/{sessionId}/autosave` | Auto-save time and answers |

**Features:**
- Save remaining time (remainingSeconds)
- Optional batch save answers (upsert)
- Returns savedAt timestamp for client sync
- Validation: remainingSeconds required, answers optional

**Postman Collection:**
- `doc/postman/PolicyEval-All.postman_collection.json` - Added Auto-Save endpoint

---

### v1.14.0 - Sidebar Navigation & Hierarchy Flow (17 Juli 2026)

**Keputusan:** [ADR-003-Sidebar-Navigation-Hierarchy-Flow](../ADR/ADR-003-Sidebar-Navigation-Hierarchy-Flow.md)

**Perubahan Sidebar:**
- Component, Sub Component, Indicator, Pertanyaan **dihapus dari sidebar**
- Sidebar hanya menampilkan: Period, Instrument Penelitian, Responden, Monitoring, Reports, Settings
- Component s.d. Pertanyaan diakses melalui **drill-down table** dari halaman Instrument Penelitian

**Navigasi:**
- Breadcrumbs klikable di setiap level drill-down, berfungsi sebagai navigasi utama antar instrument
- Tombol "← Kembali" di header setiap halaman drill-down
- Info bar di halaman Component menampilkan instrument yang sedang dilihat
- More menu (⋮) dengan hover dropdown untuk akses cepat ke View, Edit, Toggle Status, Hapus

**Aturan Status:**
| Level | Status |
|-------|--------|
| Instrument Penelitian | Draft / Published / Closed |
| Component s.d. Pertanyaan | Active / Inactive |

**Constraints:**
- Published & Closed = terkunci total (tidak bisa edit/tambah/hapus)
- Inactive tidak cascade ke child
- Soft delete untuk semua operasi hapus

**File Terkait:**
- `doc/ADR/ADR-003-Sidebar-Navigation-Hierarchy-Flow.md`
- `doc/sidebar-flow-concept.html`
- `doc/BUSINESS-PROCESS.md` (updated)
- `doc/SYSTEM_ARCHITECTURE.md` (updated)

---

**Catatan:** Dokumentasi ini akan diperbarui seiring progress development. Update dilakukan setelah setiap phase selesai.
