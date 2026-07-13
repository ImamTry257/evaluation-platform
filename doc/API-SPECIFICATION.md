# API Specification
## Platform Evaluasi Kebijakan Lingkungan Sekolah

**Version:** 1.0.0  
**Base URL:** `/api/v1`  
**Authentication:** Laravel Sanctum (Bearer Token)

---

# 1. Overview

API mengikuti prinsip RESTful dan JSON sebagai format data.

Semua request dan response menggunakan format JSON dengan status HTTP standard.

---

# 2. Authentication

## Login
Authenticate user dan mendapatkan Bearer token.

**Endpoint:** `POST /auth/login`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
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
      "email": "user@example.com",
      "role": "admin"
    }
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Email atau password salah"
}
```

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
    "created_at": "2024-01-01T10:00:00Z"
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
- `per_page` (optional) - default: 15

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Evaluasi 2024",
      "description": "Periode evaluasi tahun 2024",
      "start_date": "2024-01-01",
      "end_date": "2024-12-31",
      "is_active": true,
      "created_at": "2024-01-01T10:00:00Z"
    }
  ],
  "pagination": {
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
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
  "start_date": "2024-01-01",
  "end_date": "2024-12-31",
  "is_active": true
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
    "start_date": "2024-01-01",
    "end_date": "2024-12-31",
    "is_active": true,
    "created_at": "2024-01-01T10:00:00Z"
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
    "start_date": "2024-01-01",
    "end_date": "2024-12-31",
    "is_active": true,
    "created_at": "2024-01-01T10:00:00Z",
    "updated_at": "2024-01-01T10:00:00Z"
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
  "is_active": false
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
- `evaluation_period_id` (optional) - filter by period
- `status` (optional) - draft, published, closed
- `page` (optional)
- `per_page` (optional)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "evaluation_period_id": 1,
      "title": "Kuesioner Kebijakan Lingkungan",
      "description": "Instrumen penelitian evaluasi kebijakan lingkungan",
      "duration_minutes": 60,
      "status": "published",
      "created_at": "2024-01-01T10:00:00Z"
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
  "evaluation_period_id": 1,
  "title": "Kuesioner Kebijakan Lingkungan",
  "description": "Instrumen penelitian evaluasi kebijakan lingkungan",
  "duration_minutes": 60,
  "status": "draft"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "evaluation_period_id": 1,
    "title": "Kuesioner Kebijakan Lingkungan",
    "description": "Instrumen penelitian evaluasi kebijakan lingkungan",
    "duration_minutes": 60,
    "status": "draft",
    "created_at": "2024-01-01T10:00:00Z"
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
**Endpoint:** `GET /questionnaires/{questionnaire_id}/components`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "questionnaire_id": 1,
      "name": "Kebijakan Struktural",
      "description": "Komponen kebijakan struktural",
      "order_number": 1,
      "created_at": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Component
**Endpoint:** `POST /questionnaires/{questionnaire_id}/components`

**Request:**
```json
{
  "name": "Kebijakan Struktural",
  "description": "Komponen kebijakan struktural",
  "order_number": 1
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
  "order_number": 2
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
**Endpoint:** `GET /components/{component_id}/sub-components`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "component_id": 1,
      "name": "Kerangka Organisasi",
      "description": "Sub komponen kerangka organisasi",
      "order_number": 1,
      "created_at": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Sub Component
**Endpoint:** `POST /components/{component_id}/sub-components`

**Request:**
```json
{
  "name": "Kerangka Organisasi",
  "description": "Sub komponen kerangka organisasi",
  "order_number": 1
}
```

**Response (201 Created):** Same structure as list

---

## 3.5 Indicators

### List Indicators
**Endpoint:** `GET /sub-components/{sub_component_id}/indicators`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sub_component_id": 1,
      "name": "Keberadaan organisasi, struktur dan tugas",
      "description": "Indicator keberadaan organisasi",
      "order_number": 1,
      "created_at": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Indicator
**Endpoint:** `POST /sub-components/{sub_component_id}/indicators`

**Request:**
```json
{
  "name": "Keberadaan organisasi, struktur dan tugas",
  "description": "Indicator keberadaan organisasi",
  "order_number": 1
}
```

**Response (201 Created):** Same structure as list

---

## 3.6 Questions

### List Questions
**Endpoint:** `GET /indicators/{indicator_id}/questions`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "indicator_id": 1,
      "question": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
      "weight": 1.5,
      "order_number": 1,
      "created_at": "2024-01-01T10:00:00Z"
    }
  ]
}
```

---

### Create Question
**Endpoint:** `POST /indicators/{indicator_id}/questions`

**Request:**
```json
{
  "question": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
  "weight": 1.5,
  "order_number": 1
}
```

**Response (201 Created):** Same structure as list

---

## 3.7 Respondents

### List Respondents
**Endpoint:** `GET /respondents`

**Query Parameters:**
- `page` (optional)
- `per_page` (optional)
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
      "is_active": true,
      "created_at": "2024-01-01T10:00:00Z"
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
  "is_active": true
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
- `indicator_id` (optional) - filter by indicator

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "indicator_id": 1,
      "min_score": 1.0,
      "max_score": 2.33,
      "category": "Kurang",
      "recommendation": "Perlu perbaikan dalam implementasi...",
      "created_at": "2024-01-01T10:00:00Z"
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
  "indicator_id": 1,
  "min_score": 1.0,
  "max_score": 2.33,
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
  "questionnaire_id": 1
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "session_id": 1,
    "questionnaire_id": 1,
    "status": "in_progress",
    "started_at": "2024-01-01T10:00:00Z",
    "remaining_seconds": 3600,
    "questions": [
      {
        "id": 1,
        "indicator_id": 1,
        "question": "Apakah ada bukti tertulis tentang keberadaan organisasi?",
        "order_number": 1
      }
    ]
  }
}
```

---

## Resume Evaluation
Resume sesi evaluasi yang belum selesai.

**Endpoint:** `GET /evaluations/{session_id}/resume`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "session_id": 1,
    "status": "in_progress",
    "remaining_seconds": 2800,
    "answered_count": 15,
    "total_questions": 50,
    "questions": [
      {
        "id": 1,
        "question": "Apakah ada bukti tertulis?",
        "answered_at": "2024-01-01T10:05:00Z",
        "score": 5
      }
    ]
  }
}
```

---

## Auto Save Answer
Simpan jawaban otomatis (periodic save).

**Endpoint:** `POST /evaluations/{session_id}/auto-save`

**Request:**
```json
{
  "answers": [
    {
      "question_id": 1,
      "score": 5
    },
    {
      "question_id": 2,
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
    "saved_at": "2024-01-01T10:05:00Z",
    "answers_saved": 2
  }
}
```

---

## Save Single Answer
Simpan satu jawaban (as respondent fills).

**Endpoint:** `POST /evaluations/{session_id}/answers`

**Request:**
```json
{
  "question_id": 1,
  "score": 5
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "response_answer_id": 1,
    "question_id": 1,
    "score": 5,
    "saved_at": "2024-01-01T10:05:00Z"
  }
}
```

---

## Submit Evaluation
Submit sesi evaluasi (final submission).

**Endpoint:** `POST /evaluations/{session_id}/submit`

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
    "session_id": 1,
    "status": "submitted",
    "submitted_at": "2024-01-01T10:30:00Z"
  }
}
```

---

# 5. Results

## Get Evaluation Results
Dapatkan hasil evaluasi setelah submit.

Evaluation dihitung per Indicator dengan kategori A-E berdasarkan Standar Baku Ideal dan Rerata Ideal.

**Endpoint:** `GET /evaluations/{session_id}/results`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "evaluation_result_id": 1,
    "session_id": 1,
    "overall_score": 312.5,
    "overall_percentage": 75.5,
    "overall_category": "B",
    "conclusion": "Implementasi kebijakan lingkungan sudah baik...",
    "submitted_at": "2024-01-01T10:30:00Z",
    "indicator_results": [
      {
        "indicator_id": 1,
        "indicator_name": "Keberadaan organisasi, struktur dan tugas",
        "score": 6.5,
        "percentage": 92.86,
        "category": "A",
        "recommendation": "Pertahankan implementasi saat ini..."
      },
      {
        "indicator_id": 2,
        "indicator_name": "Peran dan fungsi organisasi",
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
- `questionnaire_id` (optional)
- `status` (optional) - in_progress, submitted, timeout
- `page` (optional)
- `per_page` (optional)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "user_name": "Responden 1",
      "questionnaire_id": 1,
      "status": "submitted",
      "started_at": "2024-01-01T10:00:00Z",
      "submitted_at": "2024-01-01T10:30:00Z",
      "duration_minutes": 30,
      "progress": 100
    }
  ],
  "pagination": {}
}
```

---

## Get Session Detail
**Endpoint:** `GET /admin/sessions/{session_id}`

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
    "questionnaire_id": 1,
    "status": "submitted",
    "started_at": "2024-01-01T10:00:00Z",
    "submitted_at": "2024-01-01T10:30:00Z",
    "answers_count": 50,
    "evaluation_result": {
      "total_score": 312.5,
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
- `questionnaire_id` (required)
- `evaluation_period_id` (optional)

**Response:** Excel file download

---

## Export Results to PDF
**Endpoint:** `POST /admin/reports/export-pdf`

**Query Parameters:**
- `session_id` (required)

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
    "active_period_id": 1,
    "evaluation_duration_minutes": 60,
    "auto_save_interval_seconds": 30,
    "allow_resume_session": true,
    "timeout_after_minutes": 120
  }
}
```

---

## Update Settings
**Endpoint:** `PUT /settings`

**Request:**
```json
{
  "active_period_id": 1,
  "evaluation_duration_minutes": 60,
  "auto_save_interval_seconds": 30,
  "allow_resume_session": true,
  "timeout_after_minutes": 120
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
    "field_name": ["Error detail 1", "Error detail 2"]
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
  "per_page": 15,
  "current_page": 1,
  "last_page": 7,
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
