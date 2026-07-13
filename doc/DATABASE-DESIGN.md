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

Menyimpan seluruh akun pengguna.

| Field | Type |
|--------|------|
| id | bigint |
| role | enum(admin, respondent) |
| name | varchar |
| email | varchar |
| password | varchar |
| is_active | boolean |
| created_at | timestamp |
| updated_at | timestamp |

---

## evaluation_periods

Periode pelaksanaan evaluasi.

| Field | Type |
|--------|------|
| id | bigint |
| name | varchar |
| description | text |
| start_date | datetime |
| end_date | datetime |
| is_active | boolean |
| created_at | timestamp |
| updated_at | timestamp |

---

## questionnaires

Master Kuesioner.

| Field | Type |
|--------|------|
| id | bigint |
| evaluation_period_id | FK |
| title | varchar |
| description | text |
| duration_minutes | integer |
| status | enum(draft,published,closed) |
| created_at | timestamp |
| updated_at | timestamp |

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
| questionnaire_id | FK |
| name | varchar |
| description | text |
| order_number | integer |
| created_at | timestamp |
| updated_at | timestamp |

---

## sub_components

Sub Komponen.

Contoh:

- Kerangka Organisasi
- Infrastruktur Pendukung

| Field | Type |
|--------|------|
| id | bigint |
| component_id | FK |
| name | varchar |
| description | text |
| order_number | integer |
| created_at | timestamp |
| updated_at | timestamp |

---

## indicators

Indikator penelitian.

Contoh:

- Keberadaan organisasi, struktur dan tugas

| Field | Type |
|--------|------|
| id | bigint |
| sub_component_id | FK |
| name | varchar |
| description | text |
| order_number | integer |
| created_at | timestamp |
| updated_at | timestamp |

---

## questions

Master Pertanyaan.

| Field | Type |
|--------|------|
| id | bigint |
| indicator_id | FK |
| question | text |
| weight | decimal(5,2) |
| order_number | integer |
| created_at | timestamp |
| updated_at | timestamp |

---

## response_sessions

Satu sesi pengisian responden.

| Field | Type |
|--------|------|
| id | bigint |
| user_id | FK |
| questionnaire_id | FK |
| evaluation_period_id | FK |
| started_at | datetime |
| submitted_at | datetime |
| remaining_seconds | integer |
| status | enum(in_progress,submitted,timeout) |
| created_at | timestamp |
| updated_at | timestamp |

---

## response_answers

Jawaban responden.

| Field | Type |
|--------|------|
| id | bigint |
| response_session_id | FK |
| question_id | FK |
| score | tinyint |
| created_at | timestamp |
| updated_at | timestamp |

Nilai menggunakan skala Likert 1 - 7.

---

## evaluation_results

Ringkasan hasil evaluasi.

| Field | Type |
|--------|------|
| id | bigint |
| response_session_id | FK |
| total_score | decimal(10,2) |
| percentage | decimal(5,2) |
| category | varchar |
| conclusion | text |
| created_at | timestamp |
| updated_at | timestamp |

---

## evaluation_result_details

Hasil evaluasi per indikator.

| Field | Type |
|--------|------|
| id | bigint |
| evaluation_result_id | FK |
| indicator_id | FK |
| score | decimal(10,2) |
| percentage | decimal(5,2) |
| category | varchar |
| created_at | timestamp |
| updated_at | timestamp |

---

## recommendations

Master rekomendasi.

| Field | Type |
|--------|------|
| id | bigint |
| indicator_id | FK |
| min_score | decimal(5,2) |
| max_score | decimal(5,2) |
| category | varchar |
| recommendation | text |
| created_at | timestamp |
| updated_at | timestamp |

---

## settings

Konfigurasi aplikasi.

| Field | Type |
|--------|------|
| id | bigint |
| key | varchar |
| value | text |
| created_at | timestamp |
| updated_at | timestamp |

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

table_id

Contoh:

questionnaire_id

indicator_id

question_id

response_session_id

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