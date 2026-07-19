# Database Schema
## Platform Evaluasi Kebijakan Lingkungan Sekolah

Version : 1.0.0

---

# 1. Overview

Database dirancang menggunakan MySQL dengan pendekatan relasional.

Struktur mengikuti hierarki instrumen penelitian sehingga mudah dikembangkan apabila terdapat perubahan instrumen di masa mendatang.

---

# 2. Database Hierarchy

```text
Evaluation Period
        │
        ▼
Questionnaire
        │
        ▼
Component
        │
        ▼
Sub Component
        │
        ▼
Indicator
        │
        ▼
Question
        │
        ▼
Response
```

---

# 3. Entity Relationship

```text
User
 │
 ├──────────────┐
 │              │
 ▼              ▼
Response Session
 │
 ▼
Response Answer
 │
 ▼
Question
 │
 ▼
Indicator
 │
 ▼
Sub Component
 │
 ▼
Component
 │
 ▼
Questionnaire
 │
 ▼
Evaluation Period

Recommendation
 │
 └────────► Indicator
```

---

# 4. Tables

---

## users

Menyimpan seluruh akun pengguna (admin dan responden).

**Authentication:**
- **Admin:** Email + Password required
- **Responden:** Email only (password nullable, auto-registered jika belum ada)

| Field | Type | Notes |
|--------|------|-------|
| id | bigint | |
| role | enum(admin, respondent) | Menentukan tipe authentikasi |
| name | varchar | Nullable untuk responden (auto-generated dari email jika kosong) |
| email | varchar | Unique, case-insensitive |
| password | varchar | **Nullable** - hanya untuk admin, responden login email-only |
| isActive | boolean | Default: true |
| createdAt | timestamp | |
| updatedAt | timestamp | |

**Admin User Requirements:**
- Email, password harus terisi
- role = 'admin'
- isActive = true

**Responden User (Auto-registered):**
- Email terisi (unique, case-insensitive)
- Password nullable (tidak digunakan)
- role = 'respondent'
- isActive = true (default)

---

## evaluation_periods

Periode pelaksanaan evaluasi.

| Field | Type |
|--------|------|
| id | bigint |
| name | varchar |
| description | text |
| startDate | datetime |
| endDate | datetime |
| isActive | boolean |
| createdAt | timestamp |
| updatedAt | timestamp |

---

## questionnaires

Master Kuesioner.

| Field | Type |
|--------|------|
| id | bigint |
| evaluationPeriodId | FK |
| title | varchar |
| description | text |
| durationMinutes | integer |
| status | enum(draft,published,closed) |
| createdAt | timestamp |
| updatedAt | timestamp |
| deletedAt | timestamp (nullable) |

---

## components

Komponen / Aspek.

Contoh:

- Kebijakan Struktural
- Program Utama
- Operasional

| Field | Type |
|--------|------|
| id | bigint |
| questionnaireId | FK |
| name | varchar |
| description | text |
| orderNumber | integer |
| is_active | integer |
| createdAt | timestamp |
| updatedAt | timestamp |
| deletedAt | timestamp |

---

## sub_components

Sub Komponen.

Contoh:

- Kerangka Organisasi
- Infrastruktur Pendukung

| Field | Type |
|--------|------|
| id | bigint |
| componentId | FK |
| name | varchar |
| description | text |
| orderNumber | integer |
| is_active | integer |
| createdAt | timestamp |
| updatedAt | timestamp |
| deletedAt | timestamp |

---

## indicators

Indikator penelitian.

Contoh:

- Keberadaan organisasi, struktur dan tugas

| Field | Type |
|--------|------|
| id | bigint |
| subComponentId | FK |
| name | varchar |
| description | text |
| orderNumber | integer |
| is_active | integer |
| createdAt | timestamp |
| updatedAt | timestamp |
| deletedAt | timestamp |

---

## questions

Master Pertanyaan.

| Field | Type |
|--------|------|
| id | bigint |
| indicatorId | FK |
| question | text |
| weight | decimal(5,2) |
| orderNumber | integer |
| is_active | integer |
| createdAt | timestamp |
| updatedAt | timestamp |
| deletedAt | timestamp |

---

## response_sessions

Satu sesi pengisian responden.

| Field | Type |
|--------|------|
| id | bigint |
| userId | FK |
| questionnaireId | FK |
| evaluationPeriodId | FK |
| startedAt | datetime |
| submittedAt | datetime |
| remainingSeconds | integer |
| status | enum(in_progress,submitted,timeout) |
| createdAt | timestamp |
| updatedAt | timestamp |

---

## response_answers

Jawaban responden.

| Field | Type |
|--------|------|
| id | bigint |
| responseSessionId | FK |
| questionId | FK |
| score | tinyint |
| createdAt | timestamp |
| updatedAt | timestamp |

Nilai menggunakan skala Likert 1 - 7.

---

## evaluation_results

Ringkasan hasil evaluasi.

| Field | Type |
|--------|------|
| id | bigint |
| responseSessionId | FK |
| totalScore | decimal(10,2) |
| percentage | decimal(5,2) |
| category | varchar |
| conclusion | text |
| createdAt | timestamp |
| updatedAt | timestamp |

---

## evaluation_result_details

Hasil evaluasi per indikator.

| Field | Type |
|--------|------|
| id | bigint |
| evaluationResultId | FK |
| indicatorId | FK |
| score | decimal(10,2) |
| percentage | decimal(5,2) |
| category | varchar |
| createdAt | timestamp |
| updatedAt | timestamp |

---

## recommendations

Master rekomendasi.

| Field | Type |
|--------|------|
| id | bigint |
| indicatorId | FK |
| minScore | decimal(5,2) |
| maxScore | decimal(5,2) |
| category | varchar |
| recommendation | text |
| createdAt | timestamp |
| updatedAt | timestamp |

---

## settings

Konfigurasi aplikasi.

| Field | Type |
|--------|------|
| id | bigint |
| key | varchar |
| value | text |
| createdAt | timestamp |
| updatedAt | timestamp |

---

# 5. Relationship

```text
evaluation_periods
        │ 1
        │
        │ N
questionnaires
        │
        │1
        │
        │N
components
        │
        │1
        │
        │N
sub_components
        │
        │1
        │
        │N
indicators
        │
        │1
        │
        │N
questions

users
        │1
        │
        │N
response_sessions
        │
        │1
        │
        │N
response_answers
        │
        └────────────► questions

response_sessions
        │1
        │
        │1
        ▼
evaluation_results
        │
        │1
        │
        │N
evaluation_result_details
        │
        └────────────► indicators

recommendations
        │
        └────────────► indicators
```

---

# 6. Evaluation Flow

```text
Respondent

↓

Response Session

↓

Response Answers

↓

Question Score

↓

Indicator Score

↓

Sub Component Score

↓

Component Score

↓

Overall Score

↓

Category

↓

Conclusion

↓

Recommendation

↓

Evaluation Result
```

---

# 7. Naming Convention

Semua nama tabel menggunakan format plural.

Contoh:

- users
- questionnaires
- components
- indicators
- questions

Seluruh Foreign Key menggunakan format:

tableId

Contoh:

questionnaireId

indicatorId

questionId

responseSessionId

---

# 8. Future Ready

Database telah dirancang agar mendukung:

- Multiple Evaluation Period
- Multiple Questionnaire
- Multiple Institution
- Multiple School Level
- Dynamic Questionnaire Structure
- Dynamic Recommendation
- Future Dashboard Analytics