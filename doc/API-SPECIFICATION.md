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
Authenticate user dan mendapatkan Bearer token. Unified endpoint untuk semua role dengan parameter type untuk forward-compatibility.

**Endpoint:** `POST /auth/login`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "type": "ADMIN"
}
```

**Type values:**
- `ADMIN` - Admin user (requires email + password)
- `RESPONDENT` - Responden user (email only, auto-register if not exists)

### Admin Login Flow
Admin login memerlukan email + password verification.

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "password123",
  "type": "ADMIN"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "admin@example.com",
      "role": "admin"
    }
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Email not found"
}
```

atau

```json
{
  "success": false,
  "message": "Password salah"
}
```

### Responden Login Flow
Responden login hanya memerlukan email. Jika email belum terdaftar di sistem, akan otomatis di-register sebagai responden.

**Request:**
```json
{
  "email": "responden@example.com",
  "type": "RESPONDENT"
}
```

**Response (200 OK) - Existing Responden:**
```json
{
  "success": true,
  "data": {
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
    "user": {
      "id": 10,
      "email": "responden@example.com",
      "role": "respondent",
      "createdAt": "2024-01-01T10:00:00Z"
    }
  }
}
```

**Response (201 Created) - New Responden (Auto-registered):**
```json
{
  "success": true,
  "message": "Responden berhasil didaftarkan",
  "data": {
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
    "user": {
      "id": 11,
      "email": "responden_baru@example.com",
      "role": "respondent",
      "createdAt": "2024-01-01T10:05:00Z"
    }
  }
}
```

**Error Response (401 Unauthorized) - Responden not eligible:**
```json
{
  "success": false,
  "message": "Email not found"
}
```

Note: Responden dianggap NOT FOUND jika email tidak terdaftar DAN tidak ada active evaluation period.

---

## Logout
Revoke user token.

**Endpoint:** `POST /auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

---

## Profile
Get current user profile.

**Endpoint:** `GET /auth/profile`

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "admin",
    "createdAt": "2024-01-01T10:00:00Z"
  }
}
```

---

# 3. Master Data - Admin Only

---

## 3.1 Evaluation Periods

### List Periods
**Endpoint:** `GET /periods`

**Query Parameters:**
- `page` (optional) - default: 1
- `perPage` (optional) - default: 15

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Evaluasi 2024",
      "description": "Periode evaluasi tahun 2024",
      "startDate": "2024-01-01",
      "endDate": "2024-12-31",
      "isActive": true,
      "createdAt": "2024-01-01T10:00:00Z"
    }
  ],
  "pagination": {
    "total": 10,
    "perPage": 15,
    "currentPage": 1,
    "lastPage": 1
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
  "success": true,
  "data": {
    "id": 1,
    "name": "Evaluasi 2024",
    "description": "Periode evaluasi tahun 2024",
    "startDate": "2024-01-01",
    "endDate": "2024-12-31",
    "isActive": true,
    "createdAt": "2024-01-01T10:00:00Z"
  }
}
```

---

### Get Period Detail
**Endpoint:** `GET /periods/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Evaluasi 2024",
    "description": "Periode evaluasi tahun 2024",
    "startDate": "2024-01-01",
    "endDate": "2024-12-31",
    "isActive": true,
    "createdAt": "2024-01-01T10:00:00Z",
    "updatedAt": "2024-01-01T10:00:00Z"
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

### Delete Period
**Endpoint:** `DELETE /periods/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Periode berhasil dihapus"
}
```

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
  "orderNumber": 1
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
  "orderNumber": 2
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
  "orderNumber": 1
}
```

**Response (201 Created):** Same structure as list

---

## 3.5 Indicators

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
  "orderNumber": 1
}
```

**Response (201 Created):** Same structure as list

---

## 3.6 Questions

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
      "question": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
      "weight": 1.5,
      "orderNumber": 1,
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
  "question": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
  "weight": 1.5,
  "orderNumber": 1
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
  "success": true,
  "data": {
    "sessionId": 1,
    "questionnaireId": 1,
    "status": "inProgress",
    "startedAt": "2024-01-01T10:00:00Z",
    "remainingSeconds": 3600,
    "questions": [
      {
        "id": 1,
        "indicatorId": 1,
        "question": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
        "orderNumber": 1
      }
    ]
  }
}
```

---

## Resume Evaluation
Resume sesi evaluasi yang belum selesai.

**Endpoint:** `GET /evaluations/{sessionId}/resume`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "sessionId": 1,
    "status": "inProgress",
    "remainingSeconds": 2800,
    "answeredCount": 15,
    "totalQuestions": 50,
    "questions": [
      {
        "id": 1,
        "question": "Apakah ada bukti tertulis?",
        "answeredAt": "2024-01-01T10:05:00Z",
        "score": 5
      }
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
- `status` (optional) - inProgress, submitted, timeout
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
