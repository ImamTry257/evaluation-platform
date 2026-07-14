# System Architecture
## Platform Evaluasi Kebijakan Lingkungan Sekolah

**Version:** 1.0.0

---

# 1. Overview

Platform Evaluasi Kebijakan Lingkungan Sekolah merupakan aplikasi berbasis web yang digunakan untuk melakukan evaluasi terhadap implementasi kebijakan lingkungan pada satuan pendidikan (SMA) melalui instrumen penelitian berbentuk angket.

Platform memungkinkan responden mengisi instrumen evaluasi dalam batas waktu tertentu. Seluruh jawaban akan diproses secara otomatis sehingga menghasilkan skor, kategori, kesimpulan, serta rekomendasi berdasarkan hasil evaluasi.

Sistem dibangun dengan konsep Single Page Application (SPA) menggunakan Vue.js sebagai Frontend dan Laravel sebagai Backend REST API.

Platform terdiri dari dua jenis pengguna:

- Admin
- Responden

---

# 2. Objectives

Platform ini bertujuan untuk:

- Mengelola periode evaluasi.
- Mengelola struktur instrumen penelitian.
- Mengelola kuesioner beserta komponennya.
- Mengelola data responden.
- Mengumpulkan jawaban responden.
- Menghitung hasil evaluasi secara otomatis.
- Menampilkan hasil evaluasi.
- Menampilkan rekomendasi berdasarkan hasil evaluasi.
- Menyediakan laporan dalam format Excel dan PDF.

---

# 3. Technology Stack

## Backend

- Laravel 12
- PHP 8.3+
- Laravel Sanctum
- RESTful API
- Eloquent ORM

## Frontend

- Vue.js 3
- Vite
- TypeScript
- Pinia
- Vue Router
- Axios
- Tailwind CSS

## Database

- MySQL

## Export

- Laravel Excel
- DomPDF

---

# 4. User Roles

## Admin

Administrator bertanggung jawab terhadap pengelolaan seluruh data sistem.

### Hak Akses

- Login (Email + Password)
- Dashboard
- Master Periode
- Master Kuesioner
- Master Komponen
- Master Sub Komponen
- Master Indikator
- Master Pertanyaan
- Master Responden
- Master Rekomendasi
- Monitoring Hasil
- Export Excel
- Export PDF
- Pengaturan Sistem

---

## Responden

Responden merupakan pengguna yang mengisi instrumen evaluasi.

### Hak Akses

- Login (Email only, auto-register jika belum terdaftar)
- Melihat Penjelasan Platform
- Memilih Evaluasi Aktif
- Mengisi Angket
- Auto Save
- Resume Pengisian
- Submit
- Melihat Hasil Evaluasi
- Melihat Rekomendasi

---

## Authentication

**Unified Endpoint:** `POST /auth/login` dengan parameter `type` (ADMIN/RESPONDENT)

**Admin Flow:**
- Email + Password verification
- Error: "Email not found" atau "Password salah"

**Responden Flow:**
- Email only verification
- Auto-register jika email belum ada di sistem
- Error: "Email not found" jika tidak eligible untuk evaluasi aktif

---

# 5. Evaluation Instrument Structure

Platform mengikuti struktur instrumen penelitian sebagai berikut.

```text
Periode Evaluasi
        │
        ▼
Kuesioner
        │
        ▼
Komponen / Aspek
        │
        ▼
Sub Komponen / Sub Aspek
        │
        ▼
Indikator
        │
        ▼
Pertanyaan
        │
        ▼
Jawaban Responden
```

Contoh implementasi:

```text
Evaluasi Kebijakan Lingkungan

└── Kebijakan Struktural
    │
    ├── Kerangka Organisasi
    │      │
    │      ├── Keberadaan Organisasi
    │      │      ├── Pertanyaan 1
    │      │      ├── Pertanyaan 2
    │      │      └── Pertanyaan 3
    │
    └── Infrastruktur Pendukung
           │
           ├── Fasilitas Ramah Lingkungan
                  ├── Pertanyaan 4
                  ├── Pertanyaan 5
                  └── Pertanyaan 6
```

---

# 6. High Level Architecture

```text
                    +--------------------+
                    |     Responden      |
                    +----------+---------+
                               |
                               |
                     Vue.js + TypeScript
                               |
                               |
                        Axios REST API
                               |
                               |
                    +----------v----------+
                    |     Laravel API     |
                    +----------+----------+
                               |
                     Business Service Layer
                               |
                               |
                    +----------v----------+
                    |       MySQL         |
                    +---------------------+

                               ▲

                    +----------+----------+
                    |        Admin        |
                    +---------------------+
```

---

# 7. Application Architecture

```text
Vue.js SPA

↓

Vue Router

↓

Pinia Store

↓

Axios

↓

Laravel Middleware

↓

Controller

↓

Service

↓

Repository

↓

Eloquent ORM

↓

MySQL
```

---

# 8. Backend Modules

## Authentication

- Login
- Logout
- Profile

---

## Master Data

- Periode
- Kuesioner
- Komponen
- Sub Komponen
- Indikator
- Pertanyaan
- Responden
- Rekomendasi

---

## Evaluation

- Start Evaluation
- Resume Evaluation
- Auto Save
- Submit Evaluation

---

## Result

- Question Score
- Indicator Score
- Sub Component Score
- Component Score
- Overall Score
- Category
- Conclusion
- Recommendation

---

## Report

- Dashboard
- Monitoring
- Export Excel
- Export PDF

---

## Setting

- Active Period
- Evaluation Duration
- Resume Session
- Auto Save
- General Configuration

---

# 9. Frontend Modules

## Responden

- Login
- Dashboard
- Penjelasan Platform
- Daftar Evaluasi
- Pengisian Angket
- Resume Pengisian
- Hasil Evaluasi
- Rekomendasi

---

## Admin

- Dashboard
- Master Periode
- Master Kuesioner
- Master Komponen
- Master Sub Komponen
- Master Indikator
- Master Pertanyaan
- Master Responden
- Master Rekomendasi
- Monitoring
- Laporan
- Setting

---

# 10. Business Flow

```text
Login

↓

Dashboard

↓

Pilih Periode Evaluasi

↓

Mulai Evaluasi

↓

Create Session

↓

Isi Pertanyaan

↓

Auto Save

↓

Resume (Jika Belum Selesai)

↓

Submit

↓

Generate Score

↓

Generate Category

↓

Generate Conclusion

↓

Generate Recommendation

↓

Tampilkan Hasil
```

---

# 11. Admin Flow

```text
Login

↓

Dashboard

↓

Buat Periode

↓

Buat Kuesioner

↓

Tambah Komponen

↓

Tambah Sub Komponen

↓

Tambah Indikator

↓

Tambah Pertanyaan

↓

Publish

↓

Monitoring Pengisian

↓

Melihat Hasil

↓

Export Excel / PDF
```

---

# 12. Core Components

```text
Authentication

↓

Questionnaire Management

↓

Response Engine

↓

Evaluation Engine

↓

Recommendation Engine

↓

Reporting Engine
```

---

# 13. Evaluation Pipeline

```text
Response

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

# 14. Features

## Admin

- Authentication
- Dashboard
- CRUD Periode
- CRUD Kuesioner
- CRUD Komponen
- CRUD Sub Komponen
- CRUD Indikator
- CRUD Pertanyaan
- CRUD Responden
- CRUD Rekomendasi
- Monitoring Evaluasi
- Export Excel
- Export PDF
- Pengaturan Sistem

---

## Responden

- Login
- Dashboard
- Melihat Penjelasan Platform
- Memulai Evaluasi
- Auto Save
- Resume Session
- Submit Evaluasi
- Melihat Hasil
- Melihat Kesimpulan
- Melihat Rekomendasi

---

# 15. Non Functional Requirements

## Performance

- Responsive Web Application
- RESTful API
- Fast Page Load
- Lazy Loading
- Pagination

## Security

- Laravel Sanctum Authentication
- Role Based Access Control (RBAC)
- CSRF Protection
- Validation
- Secure Session

## Reliability

- Auto Save Response
- Resume Session
- Time Limit Evaluation
- Soft Delete Master Data
- Transaction Database

## Compatibility

- Desktop
- Tablet
- Mobile Browser

---

# 16. Future Enhancements

- Multiple Questionnaire
- Multiple Evaluation Period
- Dashboard Analytics
- Comparative Analysis Between Periods
- Multi Institution
- Multi School Level (SD, SMP, SMA)
- Email Notification
- REST API Versioning
- AI Assisted Recommendation

---

# 17. Development Roadmap

```text
✅ Requirement Analysis
        │
        ▼
✅ System Architecture
        │
        ▼
✅ Business Process
        │
        ▼
🔄 UI / UX Design
        │
        ▼
✅ Entity Relationship Diagram (ERD)
        │
        ▼
✅ API Specification
        │
        ▼
🔄 Folder Structure
        │
        ▼
🔄 Development
        │
        ▼
🔄 Testing
        │
        ▼
🔄 Deployment
```

**Keterangan:**
- ✅ = Selesai
- 🔄 = Dalam Progres / Belum Dikerjakan

**Status Terkini:**
1. ✅ Requirement Analysis - Selesai
2. ✅ System Architecture - Selesai (SYSTEM_ARCHITECTURE.md)
3. ✅ Business Process - Selesai (BUSINESS-PROCESS.md)
4. 🔄 UI / UX Design - Belum Dikerjakan
5. ✅ Entity Relationship Diagram (ERD) - Selesai (ERD.md)
6. ✅ API Specification - Selesai (API-SPECIFICATION.md)
7. 🔄 Folder Structure - Belum Dikerjakan
8. 🔄 Development - Belum Dikerjakan
9. 🔄 Testing - Belum Dikerjakan
10. 🔄 Deployment - Belum Dikerjakan