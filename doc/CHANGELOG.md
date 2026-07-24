# Changelog

## v1.6.0 — 24 Juli 2026

### Added
- Session number (`session_number`) di tabel `response_sessions` untuk tracking sesi
- Echo check read parameter untuk debugging koneksi

### Fixed
- Migrasi kompatibel untuk **fresh install** — urutan dan dependency diperbaiki
- **Kompatibilitas MySQL 5.7 / MariaDB** — ganti `ROW_NUMBER()` jadi user variables
- Hapus **duplicate `deleted_at` field** di migration action
- Set default MySQL database config di compose

---

## v1.5.0 — 22-23 Juli 2026

### Added
- **Export Excel** via Laravel Excel — merge kolom indikator, file `.xlsx` siap download
- **Export PDF** hasil evaluasi per responden
- **Dashboard Admin** — summary (totalRespondent, submitted, inProgress, notStarted, completionPercent), weeklyProgress chart, activeSessions
- **Monitoring Sessions** — lihat semua sesi evaluasi dengan filter status & pagination
- **Docker CI/CD** — Dockerfile + compose fix untuk deployment
- **Filter button** instrumen penelitian di halaman admin
- **401 auto-redirect** ke halaman login jika token expired
- Icon PolicyEval dipindahkan ke header kiri

### Changed
- Kolom **status + started_at** ditambahkan di tabel laporan
- Nama kuesioner dipakai sebagai **filename export** Excel
- Respons API dashboard & monitoring standardisasi pakai `{status, message, data}`

### Fixed
- HeaderRowIndex + 1 blank row antara metadata dan tabel di export Excel
- Perbaiki validasi error field login di halaman respondent

---

## v1.4.0 — 20-21 Juli 2026

### Added
- **Evaluation Engine** — endpoint respondent:
  - `GET /evaluations/active-questionnaire` — ambil kuesioner aktif
  - `POST /evaluations/start` — mulai sesi baru
  - `POST /evaluations/{sessionId}/answers` — simpan jawaban
  - `POST /evaluations/{sessionId}/autosave` — auto-save periodik
  - `POST /evaluations/{sessionId}/submit` — submit final
  - `GET /evaluations/{sessionId}/results` — lihat hasil
  - `GET /evaluations/{sessionId}/component/{questionnaireId}` — resume sesi
- **Countdown timer persistent** via localStorage + sync ke backend
- **Auto-save backend** setiap 30 detik
- **Halaman profil respondent** — navigasi dari user menu dropdown
- **Modal profil terkunci** saat sedang mengisi angket
- **StepHeader component** — extract + implement di semua halaman respondent
- Extract **useLogin** dan **useLoginAdmin** hooks

### Changed
- Split `InputAngketView` jadi 3 komponen terpisah (StepIndicator, QuestionCard, NavigationButtons)
- User menu dropdown — avatar + nama, navigasi ke profile & logout
- Penanganan error login — validasi field spesifik ditampilkan

### Fixed
- Validasi error field login tidak tampil di frontend — sekarang pesan error dari backend ditampilkan

---

## v1.3.0 — 19 Juli 2026

### Changed
- Standardisasi DB columns ke snake_case: `questionnaire_id`, `order_number`, `is_active`, `indicator_id`, `question_text`, `component_id`, `sub_component_id`
- Semua API request field `isActive` (camelCase) — sebelumnya ada yang `is_active` (snake_case)
- Buat API Resource untuk convert snake_case DB → camelCase response: `ComponentResource`, `SubComponentResource`, `IndicatorResource`, `QuestionResource`, `QuestionnaireResource`
- breadCrumbList tetap ditampilkan di response API (tidak berubah)

### Fixed
- breadCrumbList NULL saat component soft-deleted — sekarang pakai `withTrashed()`
- breadCrumbList crash saat `componentId` tidak dikirim — tambah null check
- Indicator model: relasi `questions()`, `recommendations()`, `evaluationResultDetails()` FK diubah dari `indicatorId` ke `indicator_id`
- KomponenController: tambah validasi dan save field `isActive`

---

## v1.2.0 — 18 Juli 2026

### Added

- Unified authentication endpoint `/auth/login` with `type` parameter for forward-compatibility with future roles
- Email-only authentication for responden users (no password required)
- Auto-registration for responden users on first login

### Changed

- Responden login simplified to email only
- User password field is now nullable in database (responden users don't have passwords)
- Authentication flow split by role: Admin requires email+password, Responden requires email only
- Error messages unified: "Email not found" for both admin and responden flows

### Security

- Added rate limiting for login attempts (5 attempts per 15 minutes)
- Case-insensitive email lookup to prevent enumeration

---

## v1.1.0 — 17 Juli 2026

### Changed

- Evaluation result is calculated per Indicator.
- Recommendation is mapped by Indicator + Category.
- Database schema remains unchanged.
- Instrument hierarchy remains:
  Component → Sub Component → Indicator → Question.