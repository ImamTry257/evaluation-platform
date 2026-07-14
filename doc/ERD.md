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
        boolean isActive
        timestamp createdAt
        timestamp updatedAt
    }

    EVALUATION_PERIODS {
        bigint id PK
        varchar name
        text description
        datetime startDate
        datetime endDate
        boolean isActive
        timestamp createdAt
        timestamp updatedAt
    }

    QUESTIONNAIRES {
        bigint id PK
        bigint evaluationPeriodId FK
        varchar title
        text description
        integer durationMinutes
        enum status "draft, published, closed"
        timestamp createdAt
        timestamp updatedAt
    }

    COMPONENTS {
        bigint id PK
        bigint questionnaireId FK
        varchar name
        text description
        integer orderNumber
        timestamp createdAt
        timestamp updatedAt
    }

    SUB_COMPONENTS {
        bigint id PK
        bigint componentId FK
        varchar name
        text description
        integer orderNumber
        timestamp createdAt
        timestamp updatedAt
    }

    INDICATORS {
        bigint id PK
        bigint subComponentId FK
        varchar name
        text description
        integer orderNumber
        timestamp createdAt
        timestamp updatedAt
    }

    QUESTIONS {
        bigint id PK
        bigint indicatorId FK
        text question
        decimal weight "5,2"
        integer orderNumber
        timestamp createdAt
        timestamp updatedAt
    }

    RESPONSE_SESSIONS {
        bigint id PK
        bigint userId FK
        bigint questionnaireId FK
        bigint evaluationPeriodId FK
        datetime startedAt
        datetime submittedAt
        integer remainingSeconds
        enum status "inProgress, submitted, timeout"
        timestamp createdAt
        timestamp updatedAt
    }

    RESPONSE_ANSWERS {
        bigint id PK
        bigint responseSessionId FK
        bigint questionId FK
        tinyint score "1-7 Likert Scale"
        timestamp createdAt
        timestamp updatedAt
    }

    EVALUATION_RESULTS {
        bigint id PK
        bigint responseSessionId FK
        decimal totalScore "10,2"
        decimal percentage "5,2"
        varchar category
        text conclusion
        timestamp createdAt
        timestamp updatedAt
    }

    EVALUATION_RESULT_DETAILS {
        bigint id PK
        bigint evaluationResultId FK
        bigint indicatorId FK
        decimal score "10,2"
        decimal percentage "5,2"
        varchar category
        timestamp createdAt
        timestamp updatedAt
    }

    RECOMMENDATIONS {
        bigint id PK
        bigint indicatorId FK
        decimal minScore "5,2"
        decimal maxScore "5,2"
        varchar category
        text recommendation
        timestamp createdAt
        timestamp updatedAt
    }

    SETTINGS {
        bigint id PK
        varchar key
        text value
        timestamp createdAt
        timestamp updatedAt
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

Menyimpan status pengerjaan (inProgress, submitted, timeout) dan waktu sisa.

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
Semua foreign keys mengikuti format `tableId`.

Contoh:
- `userId` referensi ke `users.id`
- `questionnaireId` referensi ke `questionnaires.id`
- `indicatorId` referensi ke `indicators.id`

## Unique Constraints
- `users.email` harus unik
- `settings.key` harus unik

## Enum Fields
- `users.role` → ('admin', 'respondent')
- `questionnaires.status` → ('draft', 'published', 'closed')
- `response_sessions.status` → ('inProgress', 'submitted', 'timeout')

---

# 7. Indexing Strategy

Rekomendasi index untuk performa:

```
users
  - id (PRIMARY)
  - email (UNIQUE)

questionnaires
  - id (PRIMARY)
  - evaluationPeriodId (FOREIGN)
  - status

response_sessions
  - id (PRIMARY)
  - userId (FOREIGN)
  - questionnaireId (FOREIGN)
  - status

response_answers
  - id (PRIMARY)
  - responseSessionId (FOREIGN)
  - questionId (FOREIGN)

evaluation_results
  - id (PRIMARY)
  - responseSessionId (UNIQUE FOREIGN)

recommendations
  - id (PRIMARY)
  - indicatorId (FOREIGN)
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
