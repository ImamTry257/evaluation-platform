# API Specification
## Platform Evaluasi Kebijakan Lingkungan Sekolah

**Version:** 1.1.0  
**Base URL:** `http://localhost:8000/api/v1`  
**Authentication:** Laravel Sanctum (Bearer Token)

---

# 1. Overview

API mengikuti prinsip RESTful dan JSON sebagai format data.

Semua request dan response menggunakan format JSON dengan status HTTP standard. Parameter menggunakan **camelCase** untuk request dan response.

## Standard Response Format

Semua response mengikuti format dari `HasApiResponse` trait:

### Success Response
```json
{
  "status": true,
  "message": "Success message",
  "data": { ... }
}
```

### Error Response
```json
{
  "status": false,
  "message": "Error message",
  "errors": {
    "fieldName": ["Error detail 1", "Error detail 2"]
  }
}
```

### Paginated List Response
```json
{
  "status": true,
  "message": "Data retrieved successfully",
  "data": {
    "contents": [ ... ],
    "meta": {
      "page": 1,
      "limit": 15,
      "total": 100
    }
  }
}
```

---

# 2. Authentication

Semua endpoint auth bersifat publik (kecuali logout/profile/validate).

## Register

Register akun respondent baru.

**Endpoint:** `POST /auth/register`

**Headers:** None (public)

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

**Error Response (422 Validation):**
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

## Login (Unified)

Login untuk semua role (admin, superadmin, respondent) via username + password.

**Endpoint:** `POST /auth/login`

**Headers:** None (public)

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
    "expiredAt": "2026-07-24 11:00:00",
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

**Error Response (401):**
```json
{
  "status": false,
  "message": "Username atau password salah",
  "errors": []
}
```

---

## Login Admin

Login khusus admin/superadmin via email. Menolak respondent.

**Endpoint:** `POST /auth/login-admin`

**Headers:** None (public)

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
    "expiredAt": "2026-07-24 11:00:00",
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

**Error Response (403) — Non-Admin:**
```json
{
  "status": false,
  "message": "Akun ini tidak memiliki akses admin",
  "errors": []
}
```

---

## Logout

Revoke token yang sedang aktif.

**Endpoint:** `POST /auth/logout`

**Headers:** `Authorization: Bearer ***`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Logged out successfully",
  "data": null
}
```

---

## Profile

Mendapatkan data user yang sedang login.

**Endpoint:** `GET /auth/profile`

**Headers:** `Authorization: Bearer ***`

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
    "role": "ADMIN",
    "isActive": true,
    "createdAt": "2024-01-01T10:00:00.000000Z",
    "updatedAt": "2024-01-01T10:00:00.000000Z",
    "lastLoginAt": "2026-07-24T10:30:00.000000Z"
  }
}
```

---

## Validate Token

Validasi token masih aktif dan return data user.

**Endpoint:** `GET /auth/validate`

**Headers:** `Authorization: Bearer ***`

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

---

# 3. Admin — Master Data

Semua endpoint di section ini memerlukan:
- **Headers:** `Authorization: Bearer ***` (role admin/superadmin)
- **Base path:** `/admin/...`

---

## 3.1 Evaluation Periods

> **Note:** Periods menggunakan soft delete.

### List Periods

**Endpoint:** `GET /admin/periods`

**Query Parameters:**
- `page` (optional) — default: 1
- `limit` (optional) — default: 10
- `search` (optional) — search by name
- `isActive` (optional) — filter by status (true/false)

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
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
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

### Create Period

**Endpoint:** `POST /admin/periods`

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

**Response (201 Created):** Same structure as list item.

### Get Period Detail

**Endpoint:** `GET /admin/periods/{id}`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Period retrieved successfully",
  "data": { ... }
}
```

### Update Period

**Endpoint:** `PUT /admin/periods/{id}`

**Request:**
```json
{
  "name": "Evaluasi 2024 - Updated",
  "isActive": false
}
```

**Response (200 OK):** Updated period data.

### Delete Period (Soft Delete)

**Endpoint:** `DELETE /admin/periods/{id}`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Period deleted successfully",
  "data": null
}
```

---

## 3.2 Questionnaires

### List Questionnaires

**Endpoint:** `GET /admin/questionnaires`

**Query Parameters:**
- `evaluationPeriodId` (optional)
- `status` (optional) — `draft`, `published`, `closed`
- `page` (optional)
- `limit` (optional)
- `search` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Questionnaires retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "evaluationPeriodId": 1,
        "title": "Kuesioner Kebijakan Lingkungan",
        "description": "Instrumen penelitian evaluasi kebijakan lingkungan",
        "durationMinutes": 60,
        "status": "published",
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

### Create Questionnaire

**Endpoint:** `POST /admin/questionnaires`

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

**Response (201 Created):** Questionnaire data.

### Get Questionnaire Detail

**Endpoint:** `GET /admin/questionnaires/{id}`

### Update Questionnaire

**Endpoint:** `PUT /admin/questionnaires/{id}`

### Delete Questionnaire (Soft Delete)

**Endpoint:** `DELETE /admin/questionnaires/{id}`

### Publish Questionnaire

**Endpoint:** `POST /admin/questionnaires/{id}/publish`

**Request:** `{}` (empty body)

**Response (200 OK):**
```json
{
  "status": true,
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

**Endpoint:** `GET /admin/components`

**Query Parameters:**
- `questionnaireId` (optional)
- `page`, `limit`, `search` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Components retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "questionnaireId": 1,
        "name": "Kebijakan Struktural",
        "description": "Komponen kebijakan struktural",
        "orderNumber": 1,
        "isActive": true,
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

### Create Component

**Endpoint:** `POST /admin/components`

**Request:**
```json
{
  "name": "Kebijakan Struktural",
  "description": "Komponen kebijakan struktural",
  "orderNumber": 1,
  "isActive": true
}
```

### Update Component

**Endpoint:** `PUT /admin/components/{id}`

### Delete Component

**Endpoint:** `DELETE /admin/components/{id}`

---

## 3.4 Sub Components

> **Note:** Menggunakan soft delete.

### List Sub Components

**Endpoint:** `GET /admin/sub-components`

**Query Parameters:**
- `componentId` (optional)
- `page`, `limit`, `search` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Sub components retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "componentId": 1,
        "name": "Kerangka Organisasi",
        "description": "Sub komponen kerangka organisasi",
        "orderNumber": 1,
        "isActive": true,
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

### Create Sub Component

**Endpoint:** `POST /admin/sub-components`

**Request:**
```json
{
  "componentId": 1,
  "name": "Kerangka Organisasi",
  "description": "Sub komponen kerangka organisasi",
  "orderNumber": 1,
  "isActive": true
}
```

### Update / Delete Sub Component

**Endpoint:** `PUT /admin/sub-components/{id}` | `DELETE /admin/sub-components/{id}`

---

## 3.5 Indicators

> **Note:** Menggunakan soft delete.

### List Indicators

**Endpoint:** `GET /admin/indicators`

**Query Parameters:**
- `subComponentId` (optional)
- `page`, `limit`, `search` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Indicators retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "subComponentId": 1,
        "name": "Keberadaan organisasi, struktur dan tugas",
        "description": "Indicator keberadaan organisasi",
        "orderNumber": 1,
        "isActive": true,
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

### Create / Update / Delete

**Endpoint:** `POST /admin/indicators` | `PUT /admin/indicators/{id}` | `DELETE /admin/indicators/{id}`

---

## 3.6 Questions

> **Note:** Menggunakan soft delete.

### List Questions

**Endpoint:** `GET /admin/questions`

**Query Parameters:**
- `indicatorId` (optional)
- `page`, `limit`, `search` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Questions retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "indicatorId": 1,
        "questionText": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
        "weight": 1.5,
        "orderNumber": 1,
        "isActive": true,
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

### Create Question

**Endpoint:** `POST /admin/questions`

**Request:**
```json
{
  "indicatorId": 1,
  "questionText": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
  "weight": 1.5,
  "orderNumber": 1,
  "isActive": true
}
```

### Update / Delete

**Endpoint:** `PUT /admin/questions/{id}` | `DELETE /admin/questions/{id}`

---

## 3.7 Respondents

### List Respondents

**Endpoint:** `GET /admin/respondents`

**Query Parameters:**
- `page`, `perPage`, `search` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Respondents retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "name": "Responden 1",
        "email": "responden@example.com",
        "isActive": true,
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

### Create Respondent

**Endpoint:** `POST /admin/respondents`

**Request:**
```json
{
  "name": "Responden 1",
  "email": "responden@example.com",
  "password": "password123",
  "isActive": true
}
```

### Import Respondents (Bulk CSV)

**Endpoint:** `POST /admin/respondents/import`

**Request (multipart/form-data):**
```
file: <CSV file>
```

**CSV Format:**
```
name,email,password
Responden 1,responden1@example.com,password123
```

**Response (200 OK):**
```json
{
  "status": true,
  "message": "2 responden berhasil diimport",
  "data": {
    "imported": 2,
    "failed": 0
  }
}
```

### Update / Delete Respondent

**Endpoint:** `PUT /admin/respondents/{id}` | `DELETE /admin/respondents/{id}`

---

## 3.8 Recommendations

### List Recommendations

**Endpoint:** `GET /admin/recommendations`

**Query Parameters:**
- `indicatorId` (optional)
- `page`, `limit`, `search` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Recommendations retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "indicatorId": 1,
        "minScore": 1.0,
        "maxScore": 2.33,
        "category": "Kurang",
        "recommendation": "Perlu perbaikan dalam implementasi...",
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

### Create / Update / Delete

**Endpoint:** `POST /admin/recommendations` | `PUT /admin/recommendations/{id}` | `DELETE /admin/recommendations/{id}`

---

## 3.9 Scoring Levels

### List Scoring Levels

**Endpoint:** `GET /admin/scoring-levels`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Scoring levels retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "title": "Sangat Tidak Sesuai",
        "value": "1",
        "description": "Sangat Tidak Sesuai",
        "isActive": true,
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

### Create / Update / Delete

**Endpoint:** `POST /admin/scoring-levels` | `PUT /admin/scoring-levels/{id}` | `DELETE /admin/scoring-levels/{id}`

---

# 4. Admin — Settings

## Get Settings

**Endpoint:** `GET /admin/settings`

**Headers:** `Authorization: Bearer ***` (admin only)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Settings retrieved successfully",
  "data": {
    "activePeriodId": 1,
    "evaluationDurationMinutes": 60,
    "autoSaveIntervalSeconds": 30,
    "allowResumeSession": true,
    "timeoutAfterMinutes": 120
  }
}
```

## Update Settings

**Endpoint:** `PUT /admin/settings`

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

**Response (200 OK):** Updated settings data.

---

# 5. Admin — Dashboard & Monitoring

## Dashboard Summary

**Endpoint:** `GET /admin/dashboard`

**Headers:** `Authorization: Bearer ***` (admin only)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Dashboard data retrieved successfully",
  "data": {
    "summary": {
      "totalRespondent": 1284,
      "submitted": 856,
      "inProgress": 312,
      "notStarted": 116,
      "completionPercent": 67
    },
    "weeklyProgress": [
      { "day": "Sen", "date": "2024-01-01", "value": 45 },
      { "day": "Sel", "date": "2024-01-02", "value": 60 },
      { "day": "Rab", "date": "2024-01-03", "value": 75 },
      { "day": "Kam", "date": "2024-01-04", "value": 55 },
      { "day": "Jum", "date": "2024-01-05", "value": 90 },
      { "day": "Sab", "date": "2024-01-06", "value": 30 },
      { "day": "Min", "date": "2024-01-07", "value": 25 }
    ],
    "activeSessions": [
      {
        "id": 1,
        "userName": "Andi Wijaya",
        "userEmail": "andi@example.com",
        "questionnaireTitle": "Evaluasi Kebijakan Lingkungan",
        "answeredCount": 18,
        "totalQuestions": 20,
        "progress": 90,
        "remainingSeconds": 252,
        "timeRemaining": "04m 12s",
        "startedAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:25:48.000000Z"
      }
    ]
  }
}
```

---

## List Evaluation Sessions (Monitoring)

**Endpoint:** `GET /admin/sessions`

**Query Parameters:**
- `questionnaireId` (optional)
- `status` (optional) — `in_progress`, `submitted`, `timeout`
- `search` (optional) — search by name or email
- `page` (optional)
- `limit` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Sessions retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "user": {
          "id": 10,
          "name": "Responden 1",
          "email": "responden@example.com"
        },
        "questionnaire": {
          "id": 1,
          "title": "Evaluasi Kebijakan Lingkungan"
        },
        "status": "submitted",
        "startedAt": "2024-01-01T10:00:00.000000Z",
        "submittedAt": "2024-01-01T10:30:00.000000Z",
        "remainingSeconds": 0,
        "sessionNumber": 1,
        "answeredCount": 50,
        "totalQuestions": 50,
        "progress": 100,
        "result": {
          "overallScore": 5.25,
          "overallPercentage": 75.0,
          "overallCategory": "B"
        },
        "createdAt": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

---

## Get Session Detail

**Endpoint:** `GET /admin/sessions/{sessionId}`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Session retrieved successfully",
  "data": {
    "session": {
      "id": 1,
      "user": {
        "id": 10,
        "name": "Responden 1",
        "email": "responden@example.com"
      },
      "questionnaire": { ... },
      "status": "submitted",
      "startedAt": "2024-01-01T10:00:00.000000Z",
      "submittedAt": "2024-01-01T10:30:00.000000Z",
      "remainingSeconds": 0,
      "sessionNumber": 1,
      "answeredCount": 50,
      "totalQuestions": 50,
      "progress": 100,
      "answers": [ ... ],
      "result": { ... }
    }
  }
}
```

---

# 6. Admin — Reports

## Report Summary

**Endpoint:** `GET /admin/reports`

**Query Parameters:**
- `questionnaireId` (optional)
- `periodId` (optional)
- `search` (optional) — search by respondent name
- `page` (optional)
- `limit` (optional)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Report retrieved successfully",
  "data": {
    "summary": {
      "totalSessions": 200,
      "totalRespondents": 150,
      "averageScore": 130.5,
      "averagePercentage": 65.25
    },
    "categoryDistribution": {
      "A": { "label": "Sangat Tinggi (86-100%)", "count": 30, "percentage": 15.0 },
      "B": { "label": "Tinggi (71-85%)", "count": 50, "percentage": 25.0 },
      "C": { "label": "Sedang (56-70%)", "count": 60, "percentage": 30.0 },
      "D": { "label": "Rendah (41-55%)", "count": 40, "percentage": 20.0 },
      "E": { "label": "Sangat Rendah (0-40%)", "count": 20, "percentage": 10.0 }
    },
    "contents": [
      {
        "id": 1,
        "respondent": "Andi Wijaya",
        "questionnaire": "Evaluasi Kebijakan Lingkungan",
        "score": 5.25,
        "percentage": 75.0,
        "status": "SUBMITTED",
        "category": "B",
        "submissionOrder": 1,
        "startedAt": "2024-01-01T10:00:00.000000Z",
        "submittedAt": "2024-01-01T10:30:00.000000Z"
      }
    ],
    "meta": { ... }
  }
}
```

---

## Export Excel

Export bulk summary atau detail per responden ke file `.xlsx`.

**Endpoint:** `POST /admin/reports/export-excel`

**Query Parameters (Bulk Summary):**
- `questionnaireId` (optional)
- `periodId` (optional)
- `search` (optional)

**Query Parameters (Per-Session Detail):**
- `sessionId` (required for detail export)

**Response:** Excel file download (Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet)

**Filename pattern:** `laporan-evaluasi-{title}-{YYYYMMDDHHmmss}.xlsx`

### Bulk Summary Columns:
| No | Nama Responden | Email | Kuesioner | Persentase | Tanggal Isi Angket |

### Per-Session Detail Columns:
| No | Aspek | Pernyataan | Skor |

---

## Export PDF

Export laporan per responden ke file HTML (siap dikonversi PDF).

**Endpoint:** `POST /admin/reports/export-pdf`

**Request:**
```json
{
  "sessionId": 1
}
```

**Response:** HTML document with embedded CSS styling.

---

# 7. Respondent — Evaluation

Semua endpoint di section ini memerlukan `Authorization: Bearer ***` (role respondent).

**Base path:** `/evaluations/...`

---

## Get Active Questionnaire

Ambil kuesioner yang sedang aktif (published) untuk responden.

**Endpoint:** `GET /evaluations/active-questionnaire`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Kuesioner aktif ditemukan",
  "data": {
    "id": 1,
    "evaluationPeriodId": 1,
    "title": "Kuesioner Evaluasi Kebijakan Lingkungan 2026",
    "description": "Instrumen penelitian evaluasi kebijakan lingkungan",
    "durationMinutes": 20,
    "status": "published",
    "createdAt": "2024-01-01T10:00:00.000000Z",
    "updatedAt": "2024-01-01T10:00:00.000000Z",
    "evaluationPeriod": {
      "id": 1,
      "name": "Ganjil 2024/25",
      "description": "Periode evaluasi ganjil tahun ajaran 2024/25"
    },
    "session": {
      "evaluation": null,
      "statements": {
        "page": 1,
        "indicator": "Keberadaan organisasi, struktur dan tugas",
        "statementList": [
          {
            "id": 1,
            "text": "Sekolah memiliki dokumen resmi kebijakan lingkungan",
            "weight": 1,
            "orderNumber": 1
          }
        ],
        "count": 50,
        "indicatorLength": 10
      }
    }
  }
}
```

---

## Start Evaluation

Memulai sesi evaluasi baru atau melanjutkan sesi yang tertunda.

**Endpoint:** `POST /evaluations/start`

**Request:**
```json
{
  "questionnaireId": 1
}
```

**Response (201 Created) — New Session:**
```json
{
  "status": true,
  "message": "Evaluation started successfully",
  "data": {
    "session": {
      "evaluation": {
        "id": 1,
        "userId": 10,
        "questionnaireId": 1,
        "status": "in_progress",
        "startedAt": "2024-01-01T10:00:00.000000Z",
        "submittedAt": null,
        "remainingSeconds": 1200,
        "sessionNumber": 1,
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:00:00.000000Z"
      },
      "statements": {
        "page": 1,
        "indicator": "...",
        "statementList": [ ... ],
        "count": 50,
        "indicatorLength": 10
      }
    },
    "questionnaire": { ... },
    "scoringLevels": [
      { "id": 1, "title": "Sangat Tidak Sesuai", "value": "1", "isActive": 1, "description": "Sangat Tidak Sesuai" },
      { "id": 2, "title": "Tidak Sesuai", "value": "2", "isActive": 1, "description": "Tidak Sesuai" },
      { "id": 3, "title": "Kurang Sesuai", "value": "3", "isActive": 1, "description": "Kurang Sesuai" },
      { "id": 4, "title": "Netral", "value": "4", "isActive": 1, "description": "Netral" },
      { "id": 5, "title": "Cukup Sesuai", "value": "5", "isActive": 1, "description": "Cukup Sesuai" },
      { "id": 6, "title": "Sesuai", "value": "6", "isActive": 1, "description": "Sesuai" },
      { "id": 7, "title": "Sangat Sesuai", "value": "7", "isActive": 1, "description": "Sangat Sesuai" }
    ],
    "isResumed": false
  }
}
```

**Response (200 OK) — Resumed Session:**
Sama seperti di atas, dengan `isResumed: true` dan `session.evaluation.answers` sudah terisi.

> **Note:** `scoringLevels` berisi data master Likert 1-7 yang digunakan frontend untuk menampilkan opsi jawaban.

---

## Resume / Load Component Page

Mendapatkan data sesi dan pertanyaan per halaman (indicator).

**Endpoint:** `GET /evaluations/{sessionId}/component/{questionnaireId}`

**Parameters:**
- `sessionId` — ID sesi evaluasi
- `questionnaireId` — ID halaman/indicator yang akan ditampilkan

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Session retrieved successfully",
  "data": {
    "session": {
      "evaluation": {
        "id": 1,
        "userId": 10,
        "questionnaireId": 1,
        "status": "in_progress",
        "startedAt": "2024-01-01T10:00:00.000000Z",
        "submittedAt": null,
        "remainingSeconds": 2800,
        "sessionNumber": 1,
        "createdAt": "2024-01-01T10:00:00.000000Z",
        "updatedAt": "2024-01-01T10:05:00.000000Z",
        "answers": [ ... ]
      },
      "statements": {
        "page": 1,
        "indicator": "Keberadaan organisasi, struktur dan tugas",
        "statementList": [
          {
            "id": 1,
            "indicatorId": 1,
            "questionText": "Apakah ada bukti tertulis?",
            "weight": 1,
            "orderNumber": 1,
            "isActive": true,
            "number": 1
          }
        ],
        "count": 50,
        "indicatorLength": 10
      }
    },
    "scoringLevels": [ ... ]
  }
}
```

---

## Save Answer

Menyimpan jawaban untuk satu pertanyaan.

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
  "status": true,
  "message": "Answer saved successfully",
  "data": {
    "id": 1,
    "responseSessionId": 1,
    "questionId": 1,
    "score": 5,
    "createdAt": "2024-01-01T10:05:00.000000Z",
    "updatedAt": "2024-01-01T10:05:00.000000Z"
  }
}
```

---

## Auto Save

Auto-save periodik (biasanya setiap 30 detik). Menyimpan sisa waktu + jawaban.

**Endpoint:** `POST /evaluations/{sessionId}/autosave`

**Request:**
```json
{
  "remainingSeconds": 2800,
  "answers": [
    { "questionId": 1, "score": 5 },
    { "questionId": 2, "score": 6 }
  ]
}
```

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Auto-save successful",
  "data": {
    "remainingSeconds": 2800,
    "savedAt": "2024-01-01T10:05:00Z",
    "savedAnswers": [1, 2],
    "skippedAnswers": [],
    "skippedReason": "Questions do not belong to this questionnaire"
  }
}
```

> **Note:** `answers` bersifat opsional (`nullable`). Auto-save bisa dipanggil hanya untuk update `remainingSeconds` saja.

---

## Submit Evaluation

Submit final evaluasi. Menghitung skor dan menghasilkan rekomendasi.

**Endpoint:** `POST /evaluations/{sessionId}/submit`

**Request:** `{}` (empty body)

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Evaluation submitted successfully",
  "data": {
    "result": {
      "id": 1,
      "responseSessionId": 1,
      "overallScore": 5.25,
      "overallPercentage": 75.0,
      "overallCategory": "B",
      "conclusion": "Baik - Kebijakan lingkungan sekolah sudah cukup baik, perlu peningkatan di beberapa aspek",
      "createdAt": "2024-01-01T10:30:00.000000Z",
      "updatedAt": "2024-01-01T10:30:00.000000Z",
      "details": [
        {
          "id": 1,
          "evaluationResultId": 1,
          "indicatorId": 1,
          "score": 6.5,
          "percentage": 92.86,
          "category": "A",
          "recommendation": "Pertahankan implementasi saat ini...",
          "createdAt": "2024-01-01T10:30:00.000000Z",
          "updatedAt": "2024-01-01T10:30:00.000000Z"
        }
      ]
    }
  }
}
```

**Error Response (422) — Belum semua terjawab:**
```json
{
  "status": false,
  "message": "Please answer all questions before submitting",
  "errors": {
    "answered": 45,
    "total": 50
  }
}
```

---

## Get Results

Mendapatkan hasil evaluasi setelah submit.

**Endpoint:** `GET /evaluations/{sessionId}/results`

**Response (200 OK):** Sama dengan response submit di atas.

---

# 8. Category Scale

Skor dan kategori dihitung dengan rumus:

**Per-Indicator:**
```
score = (total bobot jawaban) / (total bobot pertanyaan)
percentage = (score / 7) * 100
```

**Overall:**
```
overallScore = Σ(score_indicator * totalBobot_indicator) / Σ(totalBobot_indicator)
overallPercentage = (overallScore / 7) * 100
```

**Kategori:**
| Kategori | Rentang | Label |
|----------|---------|-------|
| A | >= 90% | Sangat Tinggi |
| B | >= 75% | Tinggi |
| C | >= 60% | Sedang |
| D | >= 45% | Rendah |
| E | < 45% | Sangat Rendah |

---

# 9. Common Error Responses

## Standard Error
```json
{
  "status": false,
  "message": "Error message",
  "errors": {}
}
```

## Validation Error (422)
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "fieldName": ["The field name is required."]
  }
}
```

## Unauthenticated (401)
```json
{
  "status": false,
  "message": "Unauthenticated.",
  "errors": []
}
```

## Not Found (404)
```json
{
  "status": false,
  "message": "Resource not found",
  "errors": []
}
```

---

# 10. HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK — Request successful |
| 201 | Created — Resource created |
| 400 | Bad Request — Invalid input |
| 401 | Unauthorized — Missing/invalid token |
| 403 | Forbidden — Insufficient permissions (inactive/non-admin) |
| 404 | Not Found — Resource not found |
| 409 | Conflict — Duplicate submission |
| 422 | Unprocessable Entity — Validation failed |
| 500 | Internal Server Error |

---

# 11. Rate Limiting

| Endpoint | Limit |
|----------|-------|
| `/auth/login` | 500 requests per 15 minutes |
| `/auth/login-admin` | 100 requests per 15 minutes |
| `/auth/register` | 100 requests per 15 minutes |
| All others | Default Laravel |

Response headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1703001600
```

---

# 12. Pagination

Semua endpoint list menggunakan format pagination yang konsisten:

```json
{
  "data": {
    "contents": [ ... ],
    "meta": {
      "page": 1,
      "limit": 15,
      "total": 100
    }
  }
}
```

**Default:** `limit=15`, `max_limit=100`

---

# 13. Timestamps & Data Types

**Timestamps:** Semua menggunakan ISO 8601 dengan timezone UTC:
```
2024-01-01T10:00:00.000000Z
```

**Convention:** Database menyimpan dalam `snake_case`, API Resources mengonversi ke `camelCase` secara otomatis.

| Type | Format | Example |
|------|--------|---------|
| integer | 32-bit | 123 |
| bigint | 64-bit | 9223372036854775807 |
| decimal | Fixed-point | 123.45 |
| string | Text | "Hello" |
| boolean | true/false | true |
| datetime | ISO 8601 | "2024-01-01T10:00:00Z" |
| enum | Limited set | "in_progress", "submitted" |

---

---

# 14. V2 — Questions (Flat/Cascade API)

**Base URL:** `http://localhost:8000/api/v2`

API V2 untuk halaman pernyataan flat dengan cascade filter. Tidak ada perubahan struktur table — hanya cara query dan response shape yang berbeda dari V1.

**Headers:** `Authorization: Bearer *** (role admin/superadmin)`

---

## 14.1 List Questions

**Endpoint:** `GET /admin/questions`

Query Parameters:

| Parameter | Tipe | Wajib | Default | Deskripsi |
|-----------|------|-------|---------|-----------|
| `search` | string | - | - | Cari berdasarkan `questionText` |
| `instrumentId` | int | - | - | Filter by questionnaire |
| `componentId` | int | - | - | Filter by component |
| `subComponentId` | int | - | - | Filter by sub component |
| `indicatorId` | int | - | - | Filter by indicator |
| `page` | int | - | 1 | Halaman |
| `limit` | int | - | 10 | Per halaman (max 100) |
| `sortBy` | string | - | `order_number` | `order_number`, `created_at`, `question_text` |
| `sortOrder` | string | - | `asc` | `asc` / `desc` |

**Response 200:**

```json
{
  "status": true,
  "message": "Questions retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "questionText": "Seberapa sering Anda mematikan peralatan listrik...",
        "weight": 0.8,
        "isActive": true,
        "orderNumber": 1,
        "createdAt": "2025-01-15 00:00:00",
        "updatedAt": "2025-03-22 00:00:00",
        "indicator": {
          "id": 1,
          "name": "Indikator A1 — Efisiensi Energi",
          "subComponent": {
            "id": 1,
            "name": "Pemakaian Listrik Kelas",
            "component": {
              "id": 1,
              "name": "Energi: Konsumsi Listrik",
              "questionnaire": {
                "id": 1,
                "title": "Evaluasi Penggunaan Energi 2024",
                "period": {
                  "id": 1,
                  "name": "Ganjil 2023/2024"
                }
              }
            }
          }
        }
      }
    ],
    "meta": {
      "page": 1,
      "limit": 10,
      "total": 45,
      "totalPages": 5
    }
  }
}
```

---

## 14.2 Cascade Tree Data

**Endpoint:** `GET /admin/questions/tree`

Mengembalikan data hirarki lengkap untuk populate cascade dropdown di halaman list, add, dan edit.

**Response 200:**

```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "title": "Evaluasi Penggunaan Energi 2024",
      "period": "Ganjil 2023/2024",
      "components": [
        {
          "id": 1,
          "name": "Energi: Konsumsi Listrik",
          "subComponents": [
            {
              "id": 1,
              "name": "Pemakaian Listrik Kelas",
              "indicators": [
                { "id": 1, "name": "Indikator A1 — Efisiensi Energi" },
                { "id": 2, "name": "Indikator A2 — Penggunaan LED" }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

---

## 14.3 Create Question

**Endpoint:** `POST /admin/questions`

**Request:**

```json
{
  "indicatorId": 1,
  "questionText": "Apakah sekolah Anda menggunakan lampu LED hemat energi?",
  "weight": 0.6,
  "isActive": true
}
```

| Field | Tipe | Wajib | Validasi |
|-------|------|-------|----------|
| `indicatorId` | int | yes | `required|exists:indicators,id` |
| `questionText` | string | yes | `required|string|max:1000` |
| `weight` | float | yes | `required|numeric|min:0|max:1` |
| `isActive` | boolean | no | default `true` |
| `orderNumber` | int | no | auto jika tidak dikirim |

**Response 201:**

```json
{
  "status": true,
  "message": "Question created successfully",
  "data": {
    "id": 46,
    "questionText": "Apakah sekolah Anda menggunakan lampu LED hemat energi?",
    "weight": 0.6,
    "isActive": true,
    "orderNumber": 1,
    "createdAt": "2026-07-24 10:30:00",
    "updatedAt": "2026-07-24 10:30:00",
    "indicator": {
      "id": 1,
      "name": "Indikator A2 — Penggunaan LED",
      "subComponent": {
        "id": 1,
        "name": "Pemakaian Listrik Kelas",
        "component": {
          "id": 1,
          "name": "Energi: Konsumsi Listrik",
          "questionnaire": {
            "id": 1,
            "title": "Evaluasi Penggunaan Energi 2024"
          }
        }
      }
    }
  }
}
```

**Error 422:**

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "indicatorId": ["The indicator id field is required."],
    "questionText": ["The question text field is required."]
  }
}
```

---

## 14.4 Get Question Detail

**Endpoint:** `GET /admin/questions/{id}`

**Response 200:**

```json
{
  "status": true,
  "message": "Question retrieved successfully",
  "data": {
    "id": 1,
    "questionText": "Seberapa sering Anda mematikan peralatan listrik yang tidak digunakan di sekolah?",
    "weight": 0.8,
    "isActive": true,
    "orderNumber": 1,
    "createdAt": "2025-01-15 00:00:00",
    "updatedAt": "2025-03-22 00:00:00",
    "indicator": {
      "id": 1,
      "name": "Indikator A1 — Efisiensi Energi",
      "subComponent": {
        "id": 1,
        "name": "Pemakaian Listrik Kelas",
        "component": {
          "id": 1,
          "name": "Energi: Konsumsi Listrik",
          "questionnaire": {
            "id": 1,
            "title": "Evaluasi Penggunaan Energi 2024",
            "period": { "id": 1, "name": "Ganjil 2023/2024" }
          }
        }
      }
    }
  }
}
```

**Error 404:**

```json
{ "status": false, "message": "Question not found" }
```

---

## 14.5 Update Question

**Endpoint:** `PUT /admin/questions/{id}`

**Request** (sama dengan create):

```json
{
  "indicatorId": 1,
  "questionText": "Updated question text...",
  "weight": 0.9,
  "isActive": false
}
```

> **Catatan:** `orderNumber` tidak bisa diubah via update.

**Response 200:**

```json
{
  "status": true,
  "message": "Question updated successfully",
  "data": {
    "id": 1,
    "questionText": "Updated question text...",
    "weight": 0.9,
    "isActive": false,
    "orderNumber": 1,
    "createdAt": "2025-01-15 00:00:00",
    "updatedAt": "2026-07-24 11:00:00",
    "indicator": {
      "id": 1,
      "name": "Indikator A1 — Efisiensi Energi",
      "subComponent": {
        "id": 1,
        "name": "Pemakaian Listrik Kelas",
        "component": {
          "id": 1,
          "name": "Energi: Konsumsi Listrik",
          "questionnaire": {
            "id": 1,
            "title": "Evaluasi Penggunaan Energi 2024"
          }
        }
      }
    }
  }
}
```

---

## 14.6 Delete Question

**Endpoint:** `DELETE /admin/questions/{id}`

**Response 200:**

```json
{ "status": true, "message": "Question deleted successfully", "data": null }
```

**Response 404:**

```json
{ "status": false, "message": "Question not found" }
```

---

# 15. Authentication Flow

```
Respondent:
  POST /auth/register → [optional] buat akun
  POST /auth/login    → dapat token (via username + password)
  🔒 Semua endpoint evaluation

Admin:
  POST /auth/login-admin → dapat token (via email + password)
  🔒 Semua endpoint /admin/*
  🔒 GET /auth/profile, GET /auth/validate

Both:
  POST /auth/logout   → hapus token
  GET  /auth/profile  → lihat profil
  GET  /auth/validate → validasi token
```

---

# 16. Endpoint Summary

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/auth/register` | Public | Register respondent |
| POST | `/auth/login` | Public | Login (all roles) |
| POST | `/auth/login-admin` | Public | Login admin only |
| POST | `/auth/logout` | Sanctum | Revoke token |
| GET | `/auth/profile` | Sanctum | Get user profile |
| GET | `/auth/validate` | Sanctum | Validate token |
| GET | `/admin/dashboard` | Admin | Dashboard stats |
| CRUD | `/admin/periods` | Admin | Evaluation periods |
| CRUD | `/admin/questionnaires` | Admin | Questionnaires |
| POST | `/admin/questionnaires/{id}/publish` | Admin | Publish |
| CRUD | `/admin/components` | Admin | Components |
| CRUD | `/admin/sub-components` | Admin | Sub components |
| CRUD | `/admin/indicators` | Admin | Indicators |
| CRUD | `/admin/questions` | Admin | Questions |
| CRUD | `/admin/respondents` | Admin | Respondents |
| POST | `/admin/respondents/import` | Admin | Bulk import CSV |
| CRUD | `/admin/recommendations` | Admin | Recommendations |
| CRUD | `/admin/scoring-levels` | Admin | Scoring levels |
| GET/PUT | `/admin/settings` | Admin | System settings |
| GET | `/admin/sessions` | Admin | Monitoring list |
| GET | `/admin/sessions/{id}` | Admin | Session detail |
| GET | `/admin/reports` | Admin | Report summary |
| POST | `/admin/reports/export-excel` | Admin | Export to Excel |
| POST | `/admin/reports/export-pdf` | Admin | Export to PDF |
| GET | `/evaluations/active-questionnaire` | Sanctum | Active questionnaire |
| POST | `/evaluations/start` | Sanctum | Start session |
| GET | `/evaluations/{id}/component/{page}` | Sanctum | Resume/load page |
| POST | `/evaluations/{id}/answers` | Sanctum | Save answer |
| POST | `/evaluations/{id}/autosave` | Sanctum | Auto-save |
| POST | `/evaluations/{id}/submit` | Sanctum | Submit evaluation |
| GET | `/evaluations/{id}/results` | Sanctum | Get results |
