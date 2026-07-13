# Entity Relationship Diagram
## Platform Evaluasi Kebijakan Lingkungan Sekolah

**Version:** 1.0.0

---

# 1. Overview

ERD menggambarkan hubungan antar entitas dalam database sistem evaluasi kebijakan lingkungan sekolah.

Diagram ini mengikuti struktur hierarki instrumen penelitian dan alur evaluasi responden.

---

# 2. ER Diagram

```
erDiagram
    USERS ||--o{ RESPONSE_SESSIONS : creates
    EVALUATION_PERIODS ||--o{ QUESTIONNAIRES : contains
    QUESTIONNAIRES ||--o{ COMPONENTS : has
    COMPONENTS ||--o{ SUB_COMPONENTS : contains
    SUB_COMPONENTS ||--o{ INDICATORS : has
    INDICATORS ||--o{ QUESTIONS : contains
    INDICATORS ||--o{ RECOMMENDATIONS : triggers
    QUESTIONS ||--o{ RESPONSE_ANSWERS : answered_by
    RESPONSE_SESSIONS ||--o{ RESPONSE_ANSWERS : records
    RESPONSE_SESSIONS ||--o| EVALUATION_RESULTS : generates
    EVALUATION_RESULTS ||--o{ EVALUATION_RESULT_DETAILS : details
    EVALUATION_RESULT_DETAILS ||--o| INDICATORS : for
    QUESTIONNAIRES ||--o{ RESPONSE_SESSIONS : evaluates

    USERS {
        bigint id PK
        enum role "admin, respondent"
        varchar name
        varchar email
        varchar password
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    EVALUATION_PERIODS {
        bigint id PK
        varchar name
        text description
        datetime start_date
        datetime end_date
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    QUESTIONNAIRES {
        bigint id PK
        bigint evaluation_period_id FK
        varchar title
        text description
        integer duration_minutes
        enum status "draft, published, closed"
        timestamp created_at
        timestamp updated_at
    }

    COMPONENTS {
        bigint id PK
        bigint questionnaire_id FK
        varchar name
        text description
        integer order_number
        timestamp created_at
        timestamp updated_at
    }

    SUB_COMPONENTS {
        bigint id PK
        bigint component_id FK
        varchar name
        text description
        integer order_number
        timestamp created_at
        timestamp updated_at
    }

    INDICATORS {
        bigint id PK
        bigint sub_component_id FK
        varchar name
        text description
        integer order_number
        timestamp created_at
        timestamp updated_at
    }

    QUESTIONS {
        bigint id PK
        bigint indicator_id FK
        text question
        decimal weight "5,2"
        integer order_number
        timestamp created_at
        timestamp updated_at
    }

    RESPONSE_SESSIONS {
        bigint id PK
        bigint user_id FK
        bigint questionnaire_id FK
        bigint evaluation_period_id FK
        datetime started_at
        datetime submitted_at
        integer remaining_seconds
        enum status "in_progress, submitted, timeout"
        timestamp created_at
        timestamp updated_at
    }

    RESPONSE_ANSWERS {
        bigint id PK
        bigint response_session_id FK
        bigint question_id FK
        tinyint score "1-7 Likert Scale"
        timestamp created_at
        timestamp updated_at
    }

    EVALUATION_RESULTS {
        bigint id PK
        bigint response_session_id FK
        decimal total_score "10,2"
        decimal percentage "5,2"
        varchar category
        text conclusion
        timestamp created_at
        timestamp updated_at
    }

    EVALUATION_RESULT_DETAILS {
        bigint id PK
        bigint evaluation_result_id FK
        bigint indicator_id FK
        decimal score "10,2"
        decimal percentage "5,2"
        varchar category
        timestamp created_at
        timestamp updated_at
    }

    RECOMMENDATIONS {
        bigint id PK
        bigint indicator_id FK
        decimal min_score "5,2"
        decimal max_score "5,2"
        varchar category
        text recommendation
        timestamp created_at
        timestamp updated_at
    }

    SETTINGS {
        bigint id PK
        varchar key
        text value
        timestamp created_at
        timestamp updated_at
    }
```

---

# 3. Entity Details

## Users
Menyimpan informasi pengguna sistem (admin & responden).

**Relationships:**
- 1 : N dengan Response Sessions (satu user dapat membuat banyak sesi)

---

## Evaluation Periods
Periode pelaksanaan evaluasi.

**Relationships:**
- 1 : N dengan Questionnaires (satu periode dapat memiliki banyak kuesioner)
- 1 : N dengan Response Sessions (satu periode dapat memiliki banyak sesi respons)

---

## Questionnaires
Master data kuesioner evaluasi.

**Relationships:**
- N : 1 dengan Evaluation Periods (banyak kuesioner dalam satu periode)
- 1 : N dengan Components (satu kuesioner memiliki banyak komponen)
- 1 : N dengan Response Sessions (satu kuesioner dapat diisi oleh banyak responden)

---

## Components
Komponen / Aspek dalam kuesioner.

Contoh: Kebijakan Struktural, Program Utama, Operasional

**Relationships:**
- N : 1 dengan Questionnaires
- 1 : N dengan Sub Components

---

## Sub Components
Sub Komponen / Sub Aspek dalam komponen.

Contoh: Kerangka Organisasi, Infrastruktur Pendukung

**Relationships:**
- N : 1 dengan Components
- 1 : N dengan Indicators

---

## Indicators
Indikator penelitian.

Contoh: Keberadaan organisasi, struktur dan tugas

**Relationships:**
- N : 1 dengan Sub Components
- 1 : N dengan Questions
- 1 : N dengan Recommendations
- 1 : N dengan Evaluation Result Details

---

## Questions
Pertanyaan dalam kuesioner.

Menggunakan sistem weight untuk pembobotan pertanyaan.

**Relationships:**
- N : 1 dengan Indicators
- 1 : N dengan Response Answers

---

## Response Sessions
Sesi pengisian kuesioner oleh responden.

Menyimpan status pengerjaan (in_progress, submitted, timeout) dan waktu sisa.

**Relationships:**
- N : 1 dengan Users
- N : 1 dengan Questionnaires
- N : 1 dengan Evaluation Periods
- 1 : N dengan Response Answers
- 1 : 1 dengan Evaluation Results

---

## Response Answers
Jawaban responden terhadap pertanyaan.

Menggunakan Likert Scale 1-7.

**Relationships:**
- N : 1 dengan Response Sessions
- N : 1 dengan Questions

---

## Evaluation Results
Ringkasan hasil evaluasi.

Menyimpan skor total, persentase, kategori, dan kesimpulan.

**Relationships:**
- 1 : 1 dengan Response Sessions
- 1 : N dengan Evaluation Result Details

---

## Evaluation Result Details
Detail hasil evaluasi per indikator.

**Relationships:**
- N : 1 dengan Evaluation Results
- N : 1 dengan Indicators

---

## Recommendations
Master rekomendasi berdasarkan range skor indikator.

**Relationships:**
- N : 1 dengan Indicators

---

## Settings
Konfigurasi aplikasi (key-value pairs).

---

# 4. Cardinality Notation

```
|o  → Zero or one
||  → One and only one
o{  → Zero or many
|{  → One or many
```

**Example:**
- `1 : N` = One to Many
- `N : 1` = Many to One
- `1 : 1` = One to One (unique relationship)

---

# 5. Data Flow

```
Responden Mulai Evaluasi
        ↓
Create Response Session
        ↓
Fill Response Answers
  (Likert 1-7 per Question)
        ↓
Auto Save ke Database
        ↓
Submit Response Session
        ↓
Calculate Scores:
  - Question Score
  - Indicator Score
  - Sub Component Score
  - Component Score
  - Overall Score
        ↓
Generate Evaluation Results
        ↓
Fetch Recommendations
  (based on Indicator Scores)
        ↓
Display Results & Recommendations
```

---

# 6. Key Constraints

## Primary Keys
Semua tabel menggunakan `id` sebagai primary key (bigint).

## Foreign Keys
Semua foreign keys mengikuti format `table_id`.

Contoh:
- `user_id` referensi ke `users.id`
- `questionnaire_id` referensi ke `questionnaires.id`
- `indicator_id` referensi ke `indicators.id`

## Unique Constraints
- `users.email` harus unik
- `settings.key` harus unik

## Enum Fields
- `users.role` → ('admin', 'respondent')
- `questionnaires.status` → ('draft', 'published', 'closed')
- `response_sessions.status` → ('in_progress', 'submitted', 'timeout')

---

# 7. Indexing Strategy

Rekomendasi index untuk performa:

```
users
  - id (PRIMARY)
  - email (UNIQUE)

questionnaires
  - id (PRIMARY)
  - evaluation_period_id (FOREIGN)
  - status

response_sessions
  - id (PRIMARY)
  - user_id (FOREIGN)
  - questionnaire_id (FOREIGN)
  - status

response_answers
  - id (PRIMARY)
  - response_session_id (FOREIGN)
  - question_id (FOREIGN)

evaluation_results
  - id (PRIMARY)
  - response_session_id (UNIQUE FOREIGN)

recommendations
  - id (PRIMARY)
  - indicator_id (FOREIGN)
  - category
```

---

# 8. Integrity Rules

## Referential Integrity
- Jika Response Session dihapus, Response Answers harus dihapus terlebih dahulu
- Jika Questionnaire dihapus, Response Sessions terkait harus ditangani
- Jika Indicator dihapus, Recommendations terkait harus dihapus

## Data Validation
- Score dalam Response Answers harus 1-7 (Likert Scale)
- Total Score dalam Evaluation Results harus >= 0
- Percentage dalam Evaluation Results harus 0-100

## Business Rules
- Satu Response Session hanya dapat di-submit sekali
- Setelah di-submit, Response Session status tidak dapat diubah
- Evaluation Results hanya dibuat setelah Response Session di-submit

---

# 9. Future Enhancements

Database design sudah siap untuk:

- Multiple Questionnaires per Periode
- Multiple Institutions
- Multiple School Levels (SD, SMP, SMA)
- Dynamic Recommendation Logic
- Audit Trail (created_by, updated_by)
- Soft Delete (deleted_at timestamp)
