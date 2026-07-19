# Dokumentasi Business Process
## Platform Evaluasi Kebijakan Lingkungan Sekolah

**Version:** 1.0.0  
**Status:** Draft  
**Tanggal:** 13 Juli 2026

---

## 1. Ringkasan Eksekutif

Dokumentasi ini menjelaskan alur kerja dan proses bisnis dari Platform Evaluasi Kebijakan Lingkungan Sekolah dari sudut pandang pengguna. Platform ini dirancang untuk melakukan evaluasi terhadap implementasi kebijakan lingkungan pada satuan pendidikan (SMA) menggunakan instrumen penelitian berbentuk angket.

Dua peran utama dalam sistem:
1. **Admin** - Mengelola seluruh data dan konfigurasi sistem
2. **Responden** - Mengisi instrumen evaluasi dan melihat hasil

**Dokumen Terkait:**
- [Arsitektur Sistem](SYSTEM_ARCHITECTURE.md)
- [Entity Relationship Diagram](ERD.md)
- [Spesifikasi API](API-SPECIFICATION.md)

---

## 2. Proses Bisnis Admin

### 2.1 Pengelolaan Master Data

#### Proses 1: Manajemen Periode Evaluasi
**Tujuan:** Mengatur periode waktu pelaksanaan evaluasi

**Langkah-langkah:**
1. Admin login ke sistem dengan email dan password
2. Akses menu "Master Periode"
3. Buat periode baru dengan data:
   - Nama periode (contoh: "Evaluasi Kebijakan Lingkungan 2024")
   - Deskripsi
   - Tanggal mulai dan berakhir
   - Status aktif
4. Simpan periode

**Aturan Bisnis:**
- Hanya satu periode yang dapat aktif pada satu waktu
- Tanggal berakhir harus setelah tanggal mulai
- Periode yang sudah aktif dapat diubah statusnya

**Data yang Dihasilkan:**
```json
{
  "id": 1,
  "name": "Evaluasi 2024",
  "description": "Periode evaluasi tahun 2024",
  "startDate": "2024-01-01",
  "endDate": "2024-12-31",
  "isActive": true
}
```

---

#### Proses 2: Manajemen Kuesioner
**Tujuan:** Membuat dan mengelola instrumen evaluasi

**Langkah-langkah:**
1. Pilih periode evaluasi yang akan digunakan
2. Buat kuesioner baru dengan data:
   - Judul kuesioner
   - Deskripsi
   - Durasi waktu (dalam menit)
   - Status: draft
3. Tambah komponen/komponen kuesioner
4. Tambah sub-komponen untuk setiap komponen
5. Tambah indikator untuk setiap sub-komponen
6. Tambah pertanyaan untuk setiap indikator
7. Publish kuesioner saat sudah siap

**Struktur Hierarki:**
```
Periode Evaluasi
    └── Kuesioner
        ├── Komponen 1
        │   ├── Sub Komponen 1.1
        │   │   ├── Indikator 1.1.1
        │   │   │   ├── Pertanyaan 1
        │   │   │   ├── Pertanyaan 2
        │   │   │   └── Pertanyaan 3
        │   │   └── Indikator 1.1.2
        │   │       └── ...
        │   └── Sub Komponen 1.2
        └── Komponen 2
            └── ...
```

**Aturan Bisnis:**
- Kuesioner harus dalam status "draft" untuk diedit
- Kuesioner dapat dipublish setelah semua komponen dan pertanyaan ditambahkan
- Setelah dipublish, kuesioner tidak dapat diubah
- Kuesioner yang sudah ada respons tidak dapat dihapus

---

#### Proses 3: Manajemen Komponen, Sub Komponen, Indikator, dan Pertanyaan
**Tujuan:** Membangun struktur instrumen evaluasi

**Komponen:**
- Merupakan aspek-aspek utama evaluasi (contoh: Kebijakan Struktural, Program Utama)
- Setiap kuesioner dapat memiliki beberapa komponen
- Komponen memiliki urutan (orderNumber)

**Sub Komponen:**
- Merupakan bagian dari komponen (contoh: Kerangka Organisasi, Infrastruktur Pendukung)
- Setiap komponen dapat memiliki beberapa sub komponen

**Indikator:**
- Merupakan indikator penelitian dalam sub komponen
- Setiap sub komponen dapat memiliki beberapa indikator
- Indikator digunakan untuk menghitung skor dan memberikan rekomendasi

**Pertanyaan:**
- Merupakan pertanyaan yang harus dijawab responden
- Setiap pertanyaan memiliki:
  - Teks pertanyaan
  - Bobot (weight) untuk perhitungan skor
  - Urutan dalam indikator
- Skor jawaban menggunakan Likert scale 1-7

---

#### Proses 4: Manajemen Responden
**Tujuan:** Mengelola data responden yang akan mengikuti evaluasi

**Langkah-langkah:**
1. Akses menu "Master Responden"
2. Pilih metode penambahan responden:
   - Tambah manual satu per satu
   - Import bulk dari file CSV
3. Untuk import CSV:
   - Format: name,email,password
   - Contoh: "Budi Santoso,budi@example.com,password123"
4. Atur status aktif/non-aktif responden

**Aturan Bisnis:**
- Email responden harus unik dalam sistem
- Responden dapat diaktifkan/dinonaktifkan
- Responden yang sudah mengisi evaluasi tidak dapat dihapus

---

#### Proses 5: Manajemen Rekomendasi
**Tujuan:** Mengatur rekomendasi berdasarkan range skor indikator

**Langkah-langkah:**
1. Pilih indikator yang akan diberi rekomendasi
2. Tentukan range skor (min dan max)
3. Tentukan kategori (A-E)
4. Masukkan teks rekomendasi
5. Simpan rekomendasi

**Aturan Bisnis:**
- Range skor harus valid (minScore < maxScore)
- Range skor harus dalam rentang 1-7 (sesuai Likert scale)
- Setiap indikator dapat memiliki beberapa rekomendasi untuk range skor berbeda

---

### 2.2 Monitoring dan Evaluasi

#### Proses 6: Monitoring Sesi Evaluasi
**Tujuan:** Memantau progress pengisian angket oleh responden

**Langkah-langkah:**
1. Akses menu "Monitoring"
2. Filter berdasarkan:
   - Kuesioner tertentu
   - Status sesi (in_progress, submitted, timeout)
3. Lihat detail sesi:
   - Informasi responden
   - Waktu mulai dan selesai
   - Progress pengisian
   - Status terkini

**Informasi yang Ditampilkan:**
- ID Sesi
- Nama Responden
- Kuesioner yang diisi
- Status
- Waktu Mulai
- Waktu Submit
- Durasi (menit)
- Progress (%)

---

#### Proses 7: Melihat Hasil Evaluasi
**Tujuan:** Menganalisis hasil evaluasi yang sudah di-submit

**Langkah-langkah:**
1. Pilih sesi evaluasi yang sudah submitted
2. Lihat hasil evaluasi:
   - Skor keseluruhan
   - Persentase
   - Kategori (A-E)
   - Kesimpulan
3. Lihat detail per indikator:
   - Nama indikator
   - Skor indikator
   - Persentase
   - Kategori
   - Rekomendasi

**Kategori Penilaian:**
- A: Kesesuaian Sangat Tinggi (86-100%)
- B: Kesesuaian Tinggi (71-85%)
- C: Kesesuaian Sedang (56-70%)
- D: Kesesuaian Rendah (41-55%)
- E: Kesesuaian Sangat Rendah (0-40%)

---

#### Proses 8: Export Laporan
**Tujuan:** Menghasilkan laporan dalam format Excel atau PDF

**Export Excel:**
1. Pilih kuesioner atau periode evaluasi
2. Klik "Export Excel"
3. Sistem akan menghasilkan file Excel dengan data:
   - Ringkasan evaluasi
   - Detail per responden
   - Skor per indikator
   - Rekomendasi

**Export PDF:**
1. Pilih sesi evaluasi tertentu
2. Klik "Export PDF"
3. Sistem akan menghasilkan file PDF yang mencakup:
   - Informasi responden
   - Hasil evaluasi
   - Kesimpulan
   - Rekomendasi

---

### 2.3 Pengaturan Sistem

#### Proses 9: Konfigurasi Sistem
**Tujuan:** Mengatur pengaturan umum aplikasi

**Pengaturan yang Tersedia:**
1. **Periode Aktif:** Menentukan periode evaluasi yang aktif
2. **Durasi Evaluasi:** Waktu maksimal pengisian angket (dalam menit)
3. **Interval Auto Save:** Frekuensi penyimpanan otomatis
4. **Allow Resume:** Mengizinkan/melarang melanjutkan sesi yang terputus
5. **Timeout:** Waktu tunggu sebelum sesi dianggap timeout

**Aturan Bisnis:**
- Periode aktif menentukan kuesioner mana yang tersedia untuk responden
- Durasi evaluasi mempengaruhi penghitungan remainingSeconds
- Interval auto save mempengaruhi frekuensi penyimpanan jawaban

---

## 3. Proses Bisnis Responden

### 3.1 Autentikasi

#### Proses 10: Login Responden
**Tujuan:** Mengakses sistem sebagai responden

**Langkah-langkah:**
1. Buka halaman login
2. Pilih tipe: Responden
3. Masukkan email (hanya email, tidak perlu password)
4. Sistem akan:
   - Jika email sudah terdaftar: langsung login
   - Jika email belum terdaftar: auto-register sebagai responden baru
5. Jika tidak ada periode evaluasi aktif: tampilkan error "Email not found"

**Aturan Bisnis:**
- Responden hanya perlu email untuk login
- Auto-register dilakukan jika email belum ada di sistem
- Login hanya berhasil jika ada periode evaluasi aktif

---

### 3.2 Pengisian Evaluasi

#### Proses 11: Memulai Evaluasi
**Tujuan:** Memulai sesi evaluasi baru

**Langkah-langkah:**
1. Setelah login, responden melihat dashboard
2. Lihat penjelasan platform
3. Pilih evaluasi aktif yang tersedia
4. Klik "Mulai Evaluasi"
5. Sistem akan:
   - Membuat sesi baru (Response Session)
   - Menampilkan daftar pertanyaan
   - Menghitung waktu mulai dan sisa waktu

**Data yang Dibuat:**
```json
{
  "sessionId": 1,
  "questionnaireId": 1,
  "status": "in_progress",
  "startedAt": "2024-01-01T10:00:00Z",
  "remainingSeconds": 3600,
  "questions": [...]
}
```

---

#### Proses 12: Mengisi Angket
**Tujuan:** Menjawab pertanyaan evaluasi

**Langkah-langkah:**
1. Lihat daftar pertanyaan
2. Jawab pertanyaan dengan skor 1-7 (Likert scale)
3. Sistem akan:
   - Auto save jawaban secara periodik
   - Menyimpan progress pengisian
   - Menampilkan sisa waktu

**Mekanisme Auto Save:**
- Sistem akan otomatis menyimpan jawaban setiap interval tertentu
- Responden dapat melanjutkan pengisian kapan saja
- Jawaban tersimpan di database meskipun sesi terputus

**Aturan Bisnis:**
- Skor jawaban harus antara 1-7
- Semua pertanyaan harus dijawab
- Sisa waktu terus berkurang selama sesi aktif

---

#### Proses 13: Resume Sesi
**Tujuan:** Melanjutkan sesi evaluasi yang terputus

**Langkah-langkah:**
1. Jika sesi belum selesai dan waktu masih tersisa:
   - Responden dapat melanjutkan dari terakhir kali
   - Sistem akan menampilkan:
     - Pertanyaan yang sudah dijawab
     - Pertanyaan yang belum dijawab
     - Sisa waktu

**Aturan Bisnis:**
- Resume hanya dapat dilakukan jika:
  - Sesi masih dalam status "in_progress"
  - Waktu masih tersisa
  - Pengaturan "Allow Resume" aktif

---

#### Proses 14: Submit Evaluasi
**Tujuan:** Mengirimkan jawaban evaluasi secara final

**Langkah-langkah:**
1. Pastikan semua pertanyaan sudah dijawab
2. Klik "Submit"
3. Sistem akan:
   - Mengubah status sesi menjadi "submitted"
   - Mencatat waktu submit
   - Menghitung skor dan hasil evaluasi
   - Menampilkan konfirmasi

**Aturan Bisnis:**
- Evaluasi hanya dapat di-submit sekali
- Setelah submit, sesi tidak dapat diubah lagi
- Semua pertanyaan harus dijawab sebelum submit

---

### 3.3 Melihat Hasil

#### Proses 15: Melihat Hasil Evaluasi
**Tujuan:** Melihat hasil evaluasi yang sudah di-submit

**Langkah-langkah:**
1. Setelah submit, sistem akan menampilkan hasil
2. Lihat:
   - Skor keseluruhan
   - Persentase
   - Kategori (A-E)
   - Kesimpulan
3. Lihat detail per indikator:
   - Nama indikator
   - Skor indikator
   - Persentase
   - Kategori
   - Rekomendasi

**Informasi yang Ditampilkan:**
```json
{
  "evaluationResultId": 1,
  "sessionId": 1,
  "overallScore": 312.5,
  "overallPercentage": 75.5,
  "overallCategory": "B",
  "conclusion": "Implementasi kebijakan lingkungan sudah baik...",
  "indicatorResults": [
    {
      "indicatorId": 1,
      "indicatorName": "Keberadaan organisasi, struktur dan tugas",
      "score": 6.5,
      "percentage": 92.86,
      "category": "A",
      "recommendation": "Pertahankan implementasi saat ini..."
    }
  ]
}
```

---

#### Proses 16: Melihat Rekomendasi
**Tujuan:** Mendapatkan rekomendasi berdasarkan hasil evaluasi

**Langkah-langkah:**
1. Dari halaman hasil evaluasi
2. Klik "Lihat Rekomendasi"
3. Sistem akan menampilkan rekomendasi per indikator berdasarkan:
   - Skor indikator
   - Range skor yang ditentukan admin

**Aturan Bisnis:**
- Rekomendasi ditampilkan berdasarkan skor indikator
- Jika skor masuk dalam range tertentu, rekomendasi yang sesuai akan ditampilkan
- Rekomendasi membantu responden memahami area yang perlu perbaikan

---

## 4. Diagram Alur Proses Bisnis

### 4.1 Alur Admin

```
┌─────────────────────────────────────────────────────────┐
│                    ALUR ADMIN                           │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Login → Dashboard                                      │
│    │                                                    │
│    ├── Buat Periode Evaluasi                            │
│    │   ├── Input Data Periode                           │
│    │   ├── Atur Tanggal Mulai/Berakhir                  │
│    │   └── Aktifkan Periode                             │
│    │                                                    │
│    ├── Buat Kuesioner                                   │
│    │   ├── Input Judul & Deskripsi                      │
│    │   ├── Atur Durasi Waktu                            │
│    │   ├── Tambah Komponen                              │
│    │   │   ├── Tambah Sub Komponen                      │
│    │   │   │   ├── Tambah Indikator                     │
│    │   │   │   │   └── Tambah Pertanyaan                │
│    │   │   │   └── ...                                  │
│    │   │   └── ...                                      │
│    │   └── Publish Kuesioner                            │
│    │                                                    │
│    ├── Kelola Responden                                 │
│    │   ├── Tambah Manual                                │
│    │   └── Import CSV                                   │
│    │                                                    │
│    ├── Kelola Rekomendasi                               │
│    │   └── Tambah Rekomendasi per Indikator             │
│    │                                                    │
│    ├── Monitoring                                       │
│    │   ├── Lihat Sesi Evaluasi                          │
│    │   └── Lihat Progress Responden                     │
│    │                                                    │
│    ├── Lihat Hasil                                      │
│    │   ├── Analisis Skor                                │
│    │   └── Lihat Rekomendasi                            │
│    │                                                    │
│    ├── Export                                           │
│    │   ├── Export Excel                                 │
│    │   └── Export PDF                                   │
│    │                                                    │
│    └── Pengaturan Sistem                                │
│        ├── Atur Periode Aktif                           │
│        ├── Atur Durasi Evaluasi                         │
│        └── Konfigurasi Lainnya                          │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### 4.2 Alur Responden

```
┌─────────────────────────────────────────────────────────┐
│                   ALUR RESPONDEN                        │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Login (Email Only)                                     │
│    │                                                    │
│    ├── Jika Email Sudah Ada                             │
│    │   └── Langsung Login                               │
│    │                                                    │
│    └── Jika Email Belum Ada                             │
│        └── Auto-Register                                │
│                                                         │
│  Dashboard                                              │
│    │                                                    │
│    ├── Lihat Penjelasan Platform                        │
│    │                                                    │
│    ├── Pilih Evaluasi Aktif                             │
│    │                                                    │
│    └── Mulai Evaluasi                                   │
│        │                                                │
│        ├── Buat Sesi Baru                               │
│        │   ├── Waktu Mulai                              │
│        │   └── Sisa Waktu                               │
│        │                                                │
│        ├── Isi Angket                                   │
│        │   ├── Jawab Pertanyaan (Skor 1-7)              │
│        │   ├── Auto Save Periodik                       │
│        │   └── Tampilkan Progress                       │
│        │                                                │
│        ├── Resume (Jika Terputus)                       │
│        │   ├── Lanjut dari Terakhir                     │
│        │   └── Tampilkan Pertanyaan yang Belum Diisi    │
│        │                                                │
│        ├── Submit Evaluasi                              │
│        │   ├── Konfirmasi Submit                        │
│        │   ├── Ubah Status ke Submitted                 │
│        │   └── Hitung Skor & Hasil                      │
│        │                                                │
│        └── Lihat Hasil                                  │
│            ├── Skor Keseluruhan                         │
│            ├── Kategori (A-E)                           │
│            ├── Kesimpulan                               │
│            ├── Detail per Indikator                     │
│            └── Rekomendasi                              │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 5. Aturan Bisnis dan Validasi

### 5.1 Aturan Autentikasi
1. Admin: Email + Password
2. Responden: Email saja (auto-register jika belum ada)
3. Token berlaku selama sesi aktif
4. Logout akan revoke token

### 5.2 Aturan Data
1. **Email:** Harus unik untuk semua pengguna
2. **Skor:** Jawaban harus 1-7 (Likert scale)
3. **Persentase:** 0-100%
4. **Skor Total:** ≥ 0
5. **Range Rekomendasi:** minScore < maxScore

### 5.3 Aturan Sesi
1. Sesi hanya dapat di-submit sekali
2. Setelah submit, status tidak dapat diubah
3. Waktu sisa terus berkurang
4. Auto save dilakukan secara periodik
5. Resume hanya jika sesi masih "in_progress" dan waktu tersisa

### 5.4 Aturan Kuesioner / Instrument Penelitian
1. Kuesioner harus dalam status "draft" untuk diedit
2. Setelah dipublish, kuesioner tidak dapat diubah
3. Kuesioner yang sudah ada respons tidak dapat dihapus
4. Struktur hierarki: Komponen → Sub Komponen → Indikator → Pertanyaan

### 5.5 Aturan Status Data

**Instrument Penelitian:**
- Draft: Admin bebas menambah, mengedit, menghapus, dan mengubah status semua data
- Published: Semua perubahan struktur terkunci (tidak dapat tambah/edit/hapus Component s.d. Pertanyaan)
- Closed: Semua perubahan struktur terkunci, status data tidak berubah

**Component s.d. Pertanyaan:**
- Status: Active / Inactive
- Mengubah status Component ke Inactive tidak cascade ke Sub Component, Indicator, atau Pertanyaan

### 5.6 Aturan Navigasi Admin

**Sidebar Navigation:**
- Sidebar hanya menampilkan: Period, Instrument Penelitian, Responden, Monitoring, Reports, Settings
- Component, Sub Component, Indicator, Pertanyaan diakses melalui **drill-down table** dari halaman Instrument Penelitian

**Drill-Down Flow:**
- Instrument Penelitian → Component → Sub Component → Indikator → Pertanyaan
- Setiap halaman drill-down memiliki tombol "← Kembali" dan breadcrumbs klikable
- Breadcrumbs berfungsi sebagai navigasi utama antar instrument

**Context-Aware:**
- Component table selalu menampilkan data dari instrument yang dipilih
- Untuk melihat component instrument lain, klik "Instrument Penelitian" di breadcrumb → pilih instrument lain
- Tidak ada dropdown filter untuk menghindari misleading navigation

**Aksi CRUD:**
- More menu (⋮) dengan hover dropdown untuk akses cepat ke View, Edit, Toggle Status, Hapus
- Link drill-down terpisah dari more menu
- Component tidak dapat di-set Inactive ketika Instrument Penelitian status Published
- Item inactive tetap muncul di tabel dengan grey-out dan badge "Inactive"
- Semua operasi hapus menggunakan soft delete (deleted_at timestamp). Record tidak benar-benar dihapus dari database.

### 5.5 Aturan Periode
1. Hanya satu periode yang aktif pada satu waktu
2. Periode menentukan kuesioner yang tersedia
3. Periode yang sudah aktif dapat diubah statusnya

---

## 6. Konsistensi dengan Dokumen Teknis

### 6.1 Konsistensi dengan ERD
Semua proses bisnis di atas sesuai dengan model data dalam ERD:
- **Users:** Menyimpan data admin dan responden
- **Response Sessions:** Mencatat sesi evaluasi
- **Response Answers:** Menyimpan jawaban responden
- **Evaluation Results:** Menyimpan hasil evaluasi
- **Evaluation Result Details:** Detail per indikator

### 6.2 Konsistensi dengan API
Setiap proses bisnis memiliki endpoint API yang sesuai:
- **Login:** `POST /auth/login`
- **Manajemen Periode:** `GET/POST/PUT/DELETE /periods`
- **Manajemen Kuesioner:** `GET/POST /questionnaires`
- **Manajemen Komponen:** `GET/POST /questionnaires/{id}/components`
- **Evaluasi:** `POST /evaluations/start`, `POST /evaluations/{id}/submit`
- **Hasil:** `GET /evaluations/{id}/results`
- **Monitoring:** `GET /admin/sessions`
- **Export:** `POST /admin/reports/export-excel`, `POST /admin/reports/export-pdf`

### 6.3 Konsistensi dengan Arsitektur
Proses bisnis sesuai dengan arsitektur aplikasi:
- **Frontend (Vue.js):** Menampilkan antarmuka pengguna
- **API (Laravel):** Memproses request dan response
- **Database (MySQL):** Menyimpan data sesuai ERD
- **Business Service Layer:** Memproses logika bisnis

---

## 7. Skenario Penggunaan

### Skenario 1: Admin Membuat Evaluasi Baru
1. Admin login
2. Buat periode "Evaluasi 2024"
3. Buat kuesioner "Kuesioner Kebijakan Lingkungan"
4. Tambah komponen "Kebijakan Struktural"
5. Tambah sub komponen "Kerangka Organisasi"
6. Tambah indikator "Keberadaan Organisasi"
7. Tambah pertanyaan dengan bobot
8. Publish kuesioner
9. Tambah responden
10. Monitor pengisian

### Skenario 2: Responden Mengisi Evaluasi
1. Responden login dengan email
2. Lihat penjelasan platform
3. Pilih evaluasi aktif
4. Mulai evaluasi
5. Jawab pertanyaan satu per satu
6. Auto save dilakukan secara otomatis
7. Jika terputus, resume sesi
8. Submit evaluasi
9. Lihat hasil dan rekomendasi

### Skenario 3: Admin Melihat Hasil
1. Admin login
2. Akses menu monitoring
3. Filter sesi yang sudah submitted
4. Lihat detail sesi
5. Analisis skor per indikator
6. Export laporan Excel/PDF

---

## 8. Glossary

| Istilah | Definisi |
|---------|----------|
| **Periode Evaluasi** | Waktu pelaksanaan evaluasi |
| **Kuesioner** | Instrumen penelitian berisi pertanyaan |
| **Komponen** | Aspek utama evaluasi |
| **Sub Komponen** | Bagian dari komponen |
| **Indikator** | Indikator penelitian |
| **Pertanyaan** | Item yang harus dijawab responden |
| **Skor** | Jawaban Likert scale 1-7 |
| **Kategori** | Klasifikasi hasil evaluasi (A-E) |
| **Sesi** | Waktu pengisian angket oleh responden |
| **Auto Save** | Penyimpanan otomatis jawaban |
| **Resume** | Melanjutkan sesi yang terputus |

---

## 9. Daftar Referensi

1. [Arsitektur Sistem](SYSTEM_ARCHITECTURE.md) - Dokumentasi arsitektur lengkap
2. [Entity Relationship Diagram](ERD.md) - Model data dan hubungan
3. [Spesifikasi API](API-SPECIFICATION.md) - Dokumentasi endpoint API

---

**Catatan:** Dokumentasi ini merupakan panduan untuk memahami alur kerja dan proses bisnis platform. Untuk detail teknis implementasi, silakan merujuk ke dokumen arsitektur dan spesifikasi API.