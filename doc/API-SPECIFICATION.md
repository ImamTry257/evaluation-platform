# API Specification
## Platform Evaluasi Kebijakan Lingkungan Sekolah

**Version:** 1.0.0  
**Base URL:** `/api/v1`  
**Authentication:** Laravel Sanctum (Bearer Token)

---

# 1. Overview

API mengikuti prinsip RESTful dan JSON sebagai format data.

Semua request dan response menggunakan format JSON dengan status HTTP standard. Parameter menggunakan camelCase format.

---

# 2. Authentication

## Login
Authenticate user dan mendapatkan Bearer token.

**Endpoint:** `POST /auth/login`

**Request:**
```json
{
  "username": "admin",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
    "user": {
      "id": 1,
      "name": "Administrator",
      "username": "admin",
      "email": "admin@cbt.com",
      "role": "ADMIN"
    }
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "status": false,
  "message": "Username atau password salah",
  "errors": []
}
```

**Error Response (403 Forbidden) - Inactive Account:**
```json
{
  "status": false,
  "message": "Akun tidak aktif",
  "errors": []
}
```

**Error Response (422 Validation Error):**
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

---

## Login Admin
Authenticate admin/superadmin dan mendapatkan Bearer token. Khusus untuk role admin dan superadmin.

**Endpoint:** `POST /auth/login-admin`

**Request:**
```json
{
  "email": "admin@cbt.com",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
    "user": {
      "id": 1,
      "name": "Administrator",
      "username": "admin",
      "email": "admin@cbt.com",
      "role": "ADMIN"
    }
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "status": false,
  "message": "Email atau password salah",
  "errors": []
}
```

**Error Response (403 Forbidden) - Non-Admin Role:**
```json
{
  "status": false,
  "message": "Akun ini tidak memiliki akses admin",
  "errors": []
}
```

**Error Response (403 Forbidden) - Inactive Account:**
```json
{
  "status": false,
  "message": "Akun tidak aktif",
  "errors": []
}
```

**Error Response (422 Validation Error):**
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

## Register
Register new respondent account.

**Endpoint:** `POST /auth/register`

**Request:**
```json
{
  "name": "Budi Santoso",
  "username": "budi_santoso",
  "email": "budi@sekolah.id",
  "password": "password123",
  "passwordConfirmation": "password123"
}
```

**Response (201 Created):**
```json
{
  "status": true,
  "message": "Registration successful",
  "data": {
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
    "user": {
      "id": 3,
      "name": "Budi Santoso",
      "username": "budi_santoso",
      "email": "budi@sekolah.id",
      "role": "RESPONDENT"
    }
  }
}
```

**Error Response (422 Validation Error):**
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "username": ["The username field is required."],
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

**Error Response (422 Duplicate):**
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

## Logout
Revoke user token.

**Endpoint:** `POST /auth/logout`

**Headers:** `Authorization: Bearer ***

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Logged out successfully",
  "data": null
}
```

**Error Response (401 Unauthorized):**
```json
{
  "status": false,
  "message": "Unauthenticated.",
  "errors": []
}
```

---

## Profile
Get current user profile.

**Endpoint:** `GET /auth/profile`

**Headers:** `Authorization: Bearer ***

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "Administrator",
    "username": "admin",
    "email": "admin@cbt.com",
    "role": "ADMIN"
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "status": false,
  "message": "Unauthenticated.",
  "errors": []
}
```

---

# 3. Master Data - Admin Only

---

## 3.1 Evaluation Periods

> **Note:** Evaluation Periods menggunakan soft delete. Record yang dihapus akan memiliki `deleted_at` timestamp dan tidak akan muncul di list/get biasa.

### List Periods
**Endpoint:** `GET /periods`

Hanya mengembalikan periode yang belum di-soft delete (`deleted_at IS NULL`).

**Query Parameters:**
- `page` (optional) - default: 1
- `limit` (optional) - default: 10
- `search` (optional) - search by name
- `isActive` (optional) - filter by status

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Periods retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "name": "Evaluasi 2024",
        "description": "Periode evaluasi tahun 2024",
        "startDate": "2024-01-01T00:00:00.000000Z",
        "endDate": "2024-12-31T00:00:00.000000Z",
        "isActive": true,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": {
      "page": 1,
      "limit": 10,
      "total": 1
    }
  }
}
```

---

### Create Period
**Endpoint:** `POST /periods`

**Request:**
```json
{
  "name": "Evaluasi 2024",
  "description": "Periode evaluasi tahun 2024",
  "startDate": "2024-01-01",
  "endDate": "2024-12-31",
  "isActive": true
}
```

**Response (201 Created):**
```json
{
  "status": true,
  "message": "Period created successfully",
  "data": {
    "id": 1,
    "name": "Evaluasi 2024",
    "description": "Periode evaluasi tahun 2024",
    "startDate": "2024-01-01T00:00:00.000000Z",
    "endDate": "2024-12-31T00:00:00.000000Z",
    "isActive": true,
    "created_at": "2024-01-01T10:00:00.000000Z",
    "updated_at": "2024-01-01T10:00:00.000000Z"
  }
}
```

---

### Get Period Detail
**Endpoint:** `GET /periods/{id}`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Period retrieved successfully",
  "data": {
    "id": 1,
    "name": "Evaluasi 2024",
    "description": "Periode evaluasi tahun 2024",
    "startDate": "2024-01-01T00:00:00.000000Z",
    "endDate": "2024-12-31T00:00:00.000000Z",
    "isActive": true,
    "created_at": "2024-01-01T10:00:00.000000Z",
    "updated_at": "2024-01-01T10:00:00.000000Z"
  }
}
```

---

### Update Period
**Endpoint:** `PUT /periods/{id}`

**Request:**
```json
{
  "name": "Evaluasi 2024 - Updated",
  "isActive": false
}
```

**Response (200 OK):** Same as Get Period Detail

---

### Delete Period (Soft Delete)
**Endpoint:** `DELETE /periods/{id}`

Melakukan soft delete - mengatur `deleted_at` ke waktu sekarang. Record tidak benar-benar dihapus dari database.

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Period deleted successfully",
  "data": null
}
```

**Behavior:**
- `deleted_at` diatur ke timestamp sekarang
- Record tidak muncul di `GET /periods` atau `GET /periods/{id}`
- Data tetap tersimpan di database untuk audit trail

---

## 3.2 Questionnaires

### List Questionnaires
**Endpoint:** `GET /questionnaires`

**Query Parameters:**
- `evaluationPeriodId` (optional) - filter by period
- `status` (optional) - draft, published, closed
- `page` (optional)
- `perPage` (optional)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "evaluationPeriodId": 1,
      "title": "Kuesioner Kebijakan Lingkungan",
      "description": "Instrumen penelitian evaluasi kebijakan lingkungan",
      "durationMinutes": 60,
      "status": "published",
      "createdAt": "2024-01-01T10:00:00Z"
    }
  ],
  "pagination": {}
}
```

---

### Create Questionnaire
**Endpoint:** `POST /questionnaires`

**Request:**
```json
{
  "evaluationPeriodId": 1,
  "title": "Kuesioner Kebijakan Lingkungan",
  "description": "Instrumen penelitian evaluasi kebijakan lingkungan",
  "durationMinutes": 60,
  "status": "draft"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "evaluationPeriodId": 1,
    "title": "Kuesioner Kebijakan Lingkungan",
    "description": "Instrumen penelitian evaluasi kebijakan lingkungan",
    "durationMinutes": 60,
    "status": "draft",
    "createdAt": "2024-01-01T10:00:00Z"
  }
}
```

---

### Publish Questionnaire
**Endpoint:** `POST /questionnaires/{id}/publish`

**Request:**
```json
{}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Kuesioner berhasil dipublikasikan",
  "data": {
    "id": 1,
    "status": "published"
  }
}
```

---

### Delete Questionnaire (Soft Delete)
**Endpoint:** `DELETE /questionnaires/{id}`

Melakukan soft delete - mengatur `deleted_at` ke waktu sekarang. Record tidak benar-benar dihapus dari database.

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Questionnaire deleted successfully",
  "data": null
}
```

**Behavior:**
- `deleted_at` diatur ke timestamp sekarang
- Record tidak muncul di `GET /questionnaires` atau `GET /questionnaires/{id}`
- Data tetap tersimpan di database untuk audit trail

---

## 3.3 Components

### List Components
**Endpoint:** `GET /questionnaires/{questionnaireId}/components`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "questionnaireId": 1,
      "name": "Kebijakan Struktural",
      "description": "Komponen kebijakan struktural",
      "orderNumber": 1,
      "isActive": true,
      "createdAt": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Component
**Endpoint:** `POST /questionnaires/{questionnaireId}/components`

**Request:**
```json
{
  "name": "Kebijakan Struktural",
  "description": "Komponen kebijakan struktural",
  "orderNumber": 1,
  "isActive": true
}
```

**Response (201 Created):** Same structure as list

---

### Update Component
**Endpoint:** `PUT /components/{id}`

**Request:**
```json
{
  "name": "Kebijakan Struktural - Updated",
  "orderNumber": 2,
  "isActive": true
}
```

**Response (200 OK):** Updated component data

---

### Delete Component
**Endpoint:** `DELETE /components/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Komponen berhasil dihapus"
}
```

---

## 3.4 Sub Components

> **Note:** Sub Components menggunakan soft delete. DELETE akan set `deleted_at` = now().

### List Sub Components
**Endpoint:** `GET /components/{componentId}/sub-components`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "componentId": 1,
      "name": "Kerangka Organisasi",
      "description": "Sub komponen kerangka organisasi",
      "orderNumber": 1,
      "isActive": true,
      "createdAt": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Sub Component
**Endpoint:** `POST /components/{componentId}/sub-components`

**Request:**
```json
{
  "name": "Kerangka Organisasi",
  "description": "Sub komponen kerangka organisasi",
  "orderNumber": 1,
  "isActive": true
}
```

**Response (201 Created):** Same structure as list

---

## 3.5 Indicators

> **Note:** Indicators menggunakan soft delete. DELETE akan set `deleted_at` = now().

### List Indicators
**Endpoint:** `GET /sub-components/{subComponentId}/indicators`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "subComponentId": 1,
      "name": "Keberadaan organisasi, struktur dan tugas",
      "description": "Indicator keberadaan organisasi",
      "orderNumber": 1,
      "isActive": true,
      "createdAt": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Indicator
**Endpoint:** `POST /sub-components/{subComponentId}/indicators`

**Request:**
```json
{
  "name": "Keberadaan organisasi, struktur dan tugas",
  "description": "Indicator keberadaan organisasi",
  "orderNumber": 1,
  "isActive": true
}
```

**Response (201 Created):** Same structure as list

---

## 3.6 Questions

> **Note:** Questions menggunakan soft delete. DELETE akan set `deleted_at` = now().

### List Questions
**Endpoint:** `GET /indicators/{indicatorId}/questions`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "indicatorId": 1,
      "questionText": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
      "weight": 1.5,
      "orderNumber": 1,
      "isActive": true,
      "createdAt": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Question
**Endpoint:** `POST /indicators/{indicatorId}/questions`

**Request:**
```json
{
  "questionText": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
  "weight": 1.5,
  "orderNumber": 1,
  "isActive": true
}
```

**Response (201 Created):** Same structure as list

---

## 3.7 Respondents

### List Respondents
**Endpoint:** `GET /respondents`

**Query Parameters:**
- `page` (optional)
- `perPage` (optional)
- `search` (optional) - search by name or email

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Responden 1",
      "email": "responden@example.com",
      "isActive": true,
      "createdAt": "2024-01-01T10:00:00Z"
    }
  ],
  "pagination": {}
}
```

---

### Create Respondent
**Endpoint:** `POST /respondents`

**Request:**
```json
{
  "name": "Responden 1",
  "email": "responden@example.com",
  "password": "password123",
  "isActive": true
}
```

**Response (201 Created):** Respondent data

---

### Import Respondents (Bulk)
**Endpoint:** `POST /respondents/import`

**Request (multipart/form-data):**
```
file: <CSV file>
```

CSV Format:
```
name,email,password
Responden 1,responden1@example.com,password123
Responden 2,responden2@example.com,password123
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "2 responden berhasil diimport",
  "data": {
    "imported": 2,
    "failed": 0
  }
}
```

---

## 3.8 Recommendations

### List Recommendations
**Endpoint:** `GET /recommendations`

**Query Parameters:**
- `indicatorId` (optional) - filter by indicator

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "indicatorId": 1,
      "minScore": 1.0,
      "maxScore": 2.33,
      "category": "Kurang",
      "recommendation": "Perlu perbaikan dalam implementasi...",
      "createdAt": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Recommendation
**Endpoint:** `POST /recommendations`

**Request:**
```json
{
  "indicatorId": 1,
  "minScore": 1.0,
  "maxScore": 2.33,
  "category": "Kurang",
  "recommendation": "Perlu perbaikan dalam implementasi..."
}
```

**Response (201 Created):** Recommendation data

---

# 4. Evaluation - Respondent

## Start Evaluation
Mulai sesi evaluasi baru.

**Endpoint:** `POST /evaluations/start`

**Request:**
```json
{
  "questionnaireId": 1
}
```

**Response (201 Created):**
```json
{
  "status": true,
  "message": "Evaluation started successfully",
  "data": {
    "session": {
      "id": 1,
      "userId": 10,
      "questionnaireId": 1,
      "status": "in_progress",
      "startedAt": "2024-01-01T10:00:00Z",
      "remainingSeconds": 3600
    },
    "questionnaire": {
      "id": 1,
      "title": "Kuesioner Evaluasi 2026",
      "components": [...]
    },
    "scoringLevels": [
      {"id": 1, "title": "Sangat Tidak Sesuai", "value": "1", "is_active": 1, "description": "Sangat Tidak Sesuai"},
      {"id": 2, "title": "Tidak Sesuai", "value": "2", "is_active": 1, "description": "Tidak Sesuai"},
      {"id": 3, "title": "Kurang Sesuai", "value": "3", "is_active": 1, "description": "Kurang Sesuai"},
      {"id": 4, "title": "Netral", "value": "4", "is_active": 1, "description": "Netral"},
      {"id": 5, "title": "Cukup Sesuai", "value": "5", "is_active": 1, "description": "Cukup Sesuai"},
      {"id": 6, "title": "Sesuai", "value": "6", "is_active": 1, "description": "Sesuai"},
      {"id": 7, "title": "Sangat Sesuai", "value": "7", "is_active": 1, "description": "Sangat Sesuai"}
    ],
    "isResumed": false
  }
}
```

> **Note:** `scoringLevels` berisi data master level skor (Likert 1-7) yang digunakan responden untuk menjawab setiap pertanyaan. Frontend menggunakan data ini untuk menampilkan radio button/opsi jawaban dengan label yang benar.

---

## Resume Evaluation
Resume sesi evaluasi yang belum selesai.

**Endpoint:** `GET /evaluations/{sessionId}`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Session retrieved successfully",
  "data": {
    "session": {
      "id": 1,
      "userId": 10,
      "questionnaireId": 1,
      "status": "in_progress",
      "startedAt": "2024-01-01T10:00:00Z",
      "remainingSeconds": 2800,
      "answers": [...]
    },
    "scoringLevels": [
      {"id": 1, "title": "Sangat Tidak Sesuai", "value": "1", "is_active": 1},
      {"id": 2, "title": "Tidak Sesuai", "value": "2", "is_active": 1},
      {"id": 3, "title": "Kurang Sesuai", "value": "3", "is_active": 1},
      {"id": 4, "title": "Netral", "value": "4", "is_active": 1},
      {"id": 5, "title": "Cukup Sesuai", "value": "5", "is_active": 1},
      {"id": 6, "title": "Sesuai", "value": "6", "is_active": 1},
      {"id": 7, "title": "Sangat Sesuai", "value": "7", "is_active": 1}
    ]
  }
}
```

---

## Auto Save Answer
Simpan jawaban otomatis (periodic save).

**Endpoint:** `POST /evaluations/{sessionId}/auto-save`

**Request:**
```json
{
  "answers": [
    {
      "questionId": 1,
      "score": 5
    },
    {
      "questionId": 2,
      "score": 6
    }
  ]
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Jawaban tersimpan",
  "data": {
    "savedAt": "2024-01-01T10:05:00Z",
    "answersSaved": 2
  }
}
```

---

## Save Single Answer
Simpan satu jawaban (as respondent fills).

**Endpoint:** `POST /evaluations/{sessionId}/answers`

**Request:**
```json
{
  "questionId": 1,
  "score": 5
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "responseAnswerId": 1,
    "questionId": 1,
    "score": 5,
    "savedAt": "2024-01-01T10:05:00Z"
  }
}
```

---

## Submit Evaluation
Submit sesi evaluasi (final submission).

**Endpoint:** `POST /evaluations/{sessionId}/submit`

**Request:**
```json
{}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Evaluasi berhasil disubmit",
  "data": {
    "sessionId": 1,
    "status": "submitted",
    "submittedAt": "2024-01-01T10:30:00Z"
  }
}
```

---

# 5. Results

## Get Evaluation Results
Dapatkan hasil evaluasi setelah submit.

Evaluation dihitung per Indicator dengan kategori A-E berdasarkan Standar Baku Ideal dan Rerata Ideal.

**Endpoint:** `GET /evaluations/{sessionId}/results`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "evaluationResultId": 1,
    "sessionId": 1,
    "overallScore": 312.5,
    "overallPercentage": 75.5,
    "overallCategory": "B",
    "conclusion": "Implementasi kebijakan lingkungan sudah baik...",
    "submittedAt": "2024-01-01T10:30:00Z",
    "indicatorResults": [
      {
        "indicatorId": 1,
        "indicatorName": "Keberadaan organisasi, struktur dan tugas",
        "score": 6.5,
        "percentage": 92.86,
        "category": "A",
        "recommendation": "Pertahankan implementasi saat ini..."
      },
      {
        "indicatorId": 2,
        "indicatorName": "Peran dan fungsi organisasi",
        "score": 5.2,
        "percentage": 74.29,
        "category": "B",
        "recommendation": "Tingkatkan keterlibatan organisasi dalam..."
      }
    ]
  }
}
```

**Category Scale:**
- A: Kesesuaian Sangat Tinggi (86-100%)
- B: Kesesuaian Tinggi (71-85%)
- C: Kesesuaian Sedang (56-70%)
- D: Kesesuaian Rendah (41-55%)
- E: Kesesuaian Sangat Rendah (0-40%)

---

# 6. Admin - Monitoring & Report

## List Evaluation Sessions
Monitor semua sesi evaluasi.

**Endpoint:** `GET /admin/sessions`

**Query Parameters:**
- `questionnaireId` (optional)
- `status` (optional) - in_progress, submitted, timeout
- `page` (optional)
- `perPage` (optional)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "userId": 10,
      "userName": "Responden 1",
      "questionnaireId": 1,
      "status": "submitted",
      "startedAt": "2024-01-01T10:00:00Z",
      "submittedAt": "2024-01-01T10:30:00Z",
      "durationMinutes": 30,
      "progress": 100
    }
  ],
  "pagination": {}
}
```

---

## Get Session Detail
**Endpoint:** `GET /admin/sessions/{sessionId}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user": {
      "id": 10,
      "name": "Responden 1",
      "email": "responden@example.com"
    },
    "questionnaireId": 1,
    "status": "submitted",
    "startedAt": "2024-01-01T10:00:00Z",
    "submittedAt": "2024-01-01T10:30:00Z",
    "answersCount": 50,
    "evaluationResult": {
      "totalScore": 312.5,
      "percentage": 75.5,
      "category": "Baik"
    }
  }
}
```

---

## Export Results to Excel
**Endpoint:** `POST /admin/reports/export-excel`

**Query Parameters:**
- `questionnaireId` (required)
- `evaluationPeriodId` (optional)

**Response:** Excel file download

---

## Export Results to PDF
**Endpoint:** `POST /admin/reports/export-pdf`

**Query Parameters:**
- `sessionId` (required)

**Response:** PDF file download

---

# 7. Settings

## Get Settings
**Endpoint:** `GET /settings`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "activePeriodId": 1,
    "evaluationDurationMinutes": 60,
    "autoSaveIntervalSeconds": 30,
    "allowResumeSession": true,
    "timeoutAfterMinutes": 120
  }
}
```

---

## Update Settings
**Endpoint:** `PUT /settings`

**Request:**
```json
{
  "activePeriodId": 1,
  "evaluationDurationMinutes": 60,
  "autoSaveIntervalSeconds": 30,
  "allowResumeSession": true,
  "timeoutAfterMinutes": 120
}
```

**Response (200 OK):** Updated settings data

---

# 8. Error Responses

## Standard Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "fieldName": ["Error detail 1", "Error detail 2"]
  }
}
```

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created |
| 400 | Bad Request - Invalid input |
| 401 | Unauthorized - Missing/invalid token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error |

---

# 9. Rate Limiting

API rate limiting: **60 requests per minute** per user.

Response Headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1703001600
```

---

# 10. Pagination

Default pagination settings:

```json
{
  "total": 100,
  "perPage": 15,
  "currentPage": 1,
  "lastPage": 7,
  "from": 1,
  "to": 15
}
```

---

# 11. Timestamps

Semua timestamps menggunakan ISO 8601 format dengan UTC timezone:
```
2024-01-01T10:00:00Z
```

---

# 12. API Versioning

API menggunakan URL versioning:
```
/api/v1/...
```

Future versions akan tersedia di:
```
/api/v2/...
```

---

# 13. Data Types

| Type | Format | Example |
|------|--------|---------|
| integer | 32-bit integer | 123 |
| bigint | 64-bit integer | 9223372036854775807 |
| decimal | Fixed-point | 123.45 |
| string | Text | "Hello" |
| boolean | true/false | true |
| datetime | ISO 8601 | "2024-01-01T10:00:00Z" |
| enum | Limited set | "draft", "published" |
| tinyint | 0-255 | 7 |

---

# 14. Webhook Events (Future)

Planned webhook events untuk integrasi eksternal:

- `evaluation.submitted` - Saat responden submit evaluasi
- `evaluation.timeout` - Saat waktu evaluasi habis
- `results.generated` - Saat hasil evaluasi selesai dihitung
- `recommendation.matched` - Saat rekomendasi matched dengan score
