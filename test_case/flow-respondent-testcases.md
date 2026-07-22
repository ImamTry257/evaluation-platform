# Test Cases — Flow Respondent

> CBT EcoPolicy — Respondent evaluation flow
> Generated: 2026-07-22

---

## 1. Login

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-01 | Login berhasil | `POST /api/v1/auth/login` | `{ username, password }` | 200, token + user data (role RESPONDENT) | |
| TC-02 | Login gagal — username salah | `POST /api/v1/auth/login` | `{ username: "salah", password: "xxx" }` | 401, "Username atau password salah" | |
| TC-03 | Login gagal — password salah | `POST /api/v1/auth/login` | `{ username: "valid", password: "wrong" }` | 401, "Username atau password salah" | |
| TC-04 | Login gagal — akun tidak aktif | `POST /api/v1/auth/login` | `{ username: "inactive_user", password: "xxx" }` | 403, "Akun tidak aktif" | |
| TC-05 | Login gagal — field kosong | `POST /api/v1/auth/login` | `{ username: "", password: "" }` | 422, validation errors | |
| TC-06 | Login via login-admin (role responden ditolak) | `POST /api/v1/auth/login-admin` | `{ email: "respondent@test.com", password: "xxx" }` | 403, "Akun ini tidak memiliki akses admin" | Hanya admin & superadmin |
| TC-07 | Logout | `POST /api/v1/auth/logout` | Header: `Authorization: Bearer ***` | 200, token revoked | Subsequent requests 401 |

---

## 2. Explanation Page (Landing)

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-08 | Ambil active questionnaire — ada published | `GET /api/v1/evaluations/active-questionnaire` | Auth: Bearer `<respondent_token>` | 200, data berisi questionnaire (title, evaluationPeriod, durationMinutes, components) | Response include `session.statementList` |
| TC-09 | Ambil active questionnaire — tidak ada published | `GET /api/v1/evaluations/active-questionnaire` | Auth: Bearer `<respondent_token>` | 404, "Tidak ada kuesioner yang tersedia" | |
| TC-10 | Akses tanpa auth | `GET /api/v1/evaluations/active-questionnaire` | — | 401 unauthenticated | |
| TC-11 | UI: checkbox harus di-check dulu | ExplanationView | — | Tombol "Mulai Evaluasi" disabled sampai `isChecked=true` | |
| TC-12 | UI: step header step 1 aktif | ExplanationView | — | Step 1 (Penjelasan) highlighted | |

---

## 3. Start Evaluasi

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-13 | Start — session baru | `POST /api/v1/evaluations/start` | `{ questionnaireId }` | 201, `session.evaluation.id`, `status="in_progress"`, `scoringLevels`, `isResumed=false` | |
| TC-14 | Start — resume session in_progress | `POST /api/v1/evaluations/start` | `{ questionnaireId }` (ada session in_progress) | 200, session sama dengan answers, `isResumed=true` | |
| TC-15 | Start — questionnaireId invalid | `POST /api/v1/evaluations/start` | `{ questionnaireId: 99999 }` | 422, validation error `exists:questionnaires` | |
| TC-16 | Start — questionnaire tidak published | `POST /api/v1/evaluations/start` | `{ questionnaireId: <draft_id> }` | 404, "Questionnaire not found or not published" | |
| TC-17 | Start — tidak ada published questionnaire | `POST /api/v1/evaluations/start` | `{ questionnaireId: <valid_id_tapi_draft> }` | 404 | |
| TC-18 | UI: redirect setelah start | — | — | Redirect ke `/respondent/evaluation/{sessionId}/component/1` | |

---

## 4. Fetch Session / Navigasi Component

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-19 | Fetch session component pertama | `GET /api/v1/evaluations/{sessionId}/component/1` | — | 200, `session.evaluation` (answers), `session.statements` (page, indicator, statementList, count) | |
| TC-20 | Fetch session component ke-n | `GET /api/v1/evaluations/{sessionId}/component/{n}` | — | 200, statements untuk komponen ke-n | Numbering benar |
| TC-21 | Fetch — sessionId bukan milik user | `GET /api/v1/evaluations/{otherSessionId}/component/1` | — | 404 | user_id check |
| TC-22 | Fetch — session sudah submitted | `GET /api/v1/evaluations/{sessionId}/component/1` | — | Session ditemukan | Backend tidak cek status di show |
| TC-23 | UI: tombol "Selanjutnya" ke comp berikutnya | InputAngketView | — | Navigasi ke `/component/{n+1}` | |
| TC-24 | UI: tombol "Sebelumnya" ke comp sebelumnya | InputAngketView | — | Navigasi ke `/component/{n-1}` | Hanya muncul jika bukan page 1 |
| TC-25 | UI: klik pagination dots ke component tertentu | InputAngketView | — | Navigasi ke component yang diklik | |
| TC-26 | UI: component terakhir tampilkan tombol Submit | InputAngketView | — | Tombol "Submit" muncul bukan "Selanjutnya" | `indicatorLength == currentPage` |

---

## 5. Save Answer (Auto-Save Per Radio)

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-27 | Simpan jawaban — skor valid (1-7) | `POST /api/v1/evaluations/{sessionId}/answers` | `{ questionId, score: 5 }` | 200, ResponseAnswer data | Upsert — kalau sudah ada update |
| TC-28 | Update jawaban — ubah skor | `POST /api/v1/evaluations/{sessionId}/answers` | `{ questionId, score: 7 }` | 200, score ter-update | Bukan duplicate row |
| TC-29 | Jawaban invalid — score < 1 | `POST /api/v1/evaluations/{sessionId}/answers` | `{ questionId, score: 0 }` | 422, validation error | |
| TC-30 | Jawaban invalid — score > 7 | `POST /api/v1/evaluations/{sessionId}/answers` | `{ questionId, score: 8 }` | 422, validation error | |
| TC-31 | Jawaban invalid — questionId tidak ada | `POST /api/v1/evaluations/{sessionId}/answers` | `{ questionId: 99999, score: 3 }` | 422, validation error | |
| TC-32 | Jawaban — question bukan milik questionnaire ini | `POST /api/v1/evaluations/{sessionId}/answers` | `{ questionId: <question_from_other_questionnaire>, score: 5 }` | 422, "Question does not belong to this questionnaire" | |
| TC-33 | Jawaban — session sudah submitted | `POST /api/v1/evaluations/{sessionId}/answers` | `{ questionId, score: 5 }` | 404, "Session not found or not in progress" | Status=submitted |
| TC-34 | UI: toast "Jawaban tersimpan" muncul | InputAngketView | — | Toast muncul 1.8 detik setelah klik radio | |
| TC-35 | UI: progress bar ter-update | InputAngketView | — | Persentase naik setelah jawab | `answeredCount / statementCount * 100` |

---

## 6. Autosave (Timer + Bulk Answers)

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-36 | Autosave — hanya save remaining time | `POST /api/v1/evaluations/{sessionId}/autosave` | `{ remainingSeconds: 1200 }` | 200, `remainingSeconds` ter-update | |
| TC-37 | Autosave — save time + answers array | `POST /api/v1/evaluations/{sessionId}/autosave` | `{ remainingSeconds: 1100, answers: [{questionId, score}] }` | 200, `savedAnswers` array | |
| TC-38 | Autosave — questionId invalid di-skip | `POST /api/v1/evaluations/{sessionId}/autosave` | `{ remainingSeconds: 1000, answers: [{questionId: 99999, score: 3}] }` | 200, `skippedAnswers` + `skippedReason` | |
| TC-39 | Autosave — remainingSeconds < 0 | `POST /api/v1/evaluations/{sessionId}/autosave` | `{ remainingSeconds: -1 }` | 422, validation error (min:0) | |
| TC-40 | Autosave — session sudah submitted | `POST /api/v1/evaluations/{sessionId}/autosave` | `{ remainingSeconds: 500 }` | 404, "Session not found or not in progress" | |
| TC-41 | UI: autosave ke backend tiap 30 detik | InputAngketView | — | `autoSave()` dipanggil setiap 30s | |
| TC-42 | UI: autosave ke localStorage tiap 5 detik | InputAngketView | — | `saveTimerToLocal()` setiap 5s | |
| TC-43 | UI: timer sync prioritaskan localStorage | InputAngketView | — | `timeLeft = min(localRemaining, backendRemaining)` | |

---

## 7. Timer & Timeout

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-44 | Timer hitung mundur dari duration_minutes | InputAngketView | — | Countdown dimulai dari `durationMinutes * 60` | |
| TC-45 | Timer mencapai 0 → timeout modal | InputAngketView | — | Modal "Waktu Habis!" muncul | |
| TC-46 | Timeout — tombol "Submit Otomatis" | InputAngketView | — | Submit jawaban + redirect ke `/result/{sessionId}` | |
| TC-47 | Timeout — tombol "Kembali ke Beranda" | InputAngketView | — | Clear timer + redirect `/respondent` | |
| TC-48 | Timer color merah saat <= 60 detik | InputAngketView | — | Class `text-error` diterapkan | |

---

## 8. Submit Evaluasi

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-49 | Submit berhasil — semua dijawab | `POST /api/v1/evaluations/{sessionId}/submit` | — | 200, result (overallScore, overallPercentage, overallCategory, conclusion, details) | DB transaksi |
| TC-50 | Submit gagal — belum semua dijawab | `POST /api/v1/evaluations/{sessionId}/submit` | — | 422, `{ answered: N, total: M }` | |
| TC-51 | Submit — session tidak ditemukan | `POST /api/v1/evaluations/{sessionId}/submit` | — | 404 | |
| TC-52 | Submit — session sudah submitted (double submit) | `POST /api/v1/evaluations/{sessionId}/submit` | — | 404, "Session not found or not in progress" | |
| TC-53 | Submit — scoring: semua score=7 | `POST /api/v1/evaluations/{sessionId}/submit` | — | overallScore ~7.0, percentage ~100%, category A | Weighted average per indicator |
| TC-54 | Submit — scoring: semua score=4 | `POST /api/v1/evaluations/{sessionId}/submit` | — | overallScore ~4.0, percentage ~57%, category C | |
| TC-55 | UI: submit modal tampilkan X dari Y terjawab | InputAngketView | — | Modal info jumlah terjawab | |
| TC-56 | UI: submit sukses redirect ke result | InputAngketView | — | Redirect `/respondent/result/{sessionId}` | |

---

## 9. Hasil Evaluasi

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-57 | Lihat hasil — session submitted | `GET /api/v1/evaluations/{sessionId}/results` | — | 200, result (overallScore, overallPercentage, overallCategory, conclusion, details) | |
| TC-58 | Lihat hasil — session belum submitted | `GET /api/v1/evaluations/{sessionId}/results` | — | 422, "Session not yet submitted" | |
| TC-59 | Lihat hasil — session tidak ditemukan | `GET /api/v1/evaluations/{sessionId}/results` | — | 404 | |
| TC-60 | Lihat hasil — result null (edge case) | `GET /api/v1/evaluations/{sessionId}/results` | — | 404, "Results not available" | |
| TC-61 | UI: ringkasan skor (percentage + category) | ResultRecommendationView | — | Tampil percentage + category label | |
| TC-62 | UI: detail per komponen | ResultRecommendationView | — | Indicator name, percentage, progress bar | |
| TC-63 | UI: tombol "Kembali ke Beranda" | ResultRecommendationView | — | Redirect `/respondent` | |

---

## 10. Navigation Guard & Role

| No | Test Case | Route | Expect | Catatan |
|----|-----------|-------|--------|---------|
| TC-64 | Responden akses /admin | `/admin` | Redirect ke `/respondent` | Router meta: `roles: ['ADMIN', 'SUPERADMIN']` |
| TC-65 | Admin akses /respondent | `/respondent` | Redirect ke `/admin` | Router meta: `role: 'RESPONDENT'` |
| TC-66 | Unauthenticated akses /respondent | `/respondent` | Redirect ke `/login` | |
| TC-67 | Authenticated guest akses /login | `/login` | Redirect ke dashboard sesuai role | |

---

## 11. Reset / Kembali ke Penjelasan

| No | Test Case | Endpoint | Input | Expect | Catatan |
|----|-----------|----------|-------|--------|---------|
| TC-68 | Klik step "1 Penjelasan" di InputAngketView | — | — | Modal reset muncul | |
| TC-69 | Modal reset — "Ya, Mulai Ulang" | — | — | Clear timers + redirect `/respondent` | |
| TC-70 | Modal reset — "Batal" | — | — | Tetap di halaman angket | |
| TC-71 | Session lama tetap in_progress di backend | `POST /api/v1/evaluations/start` | `{ questionnaireId }` | Resume session lama | Start ulang = resume |

---

## 12. Edge Cases & Security

| No | Test Case | Scenario | Expect | Catatan |
|----|-----------|----------|--------|---------|
| TC-72 | Session dari user lain | Akses session orang lain | 404 | user_id check di controller |
| TC-73 | Token expired | Request dengan token expired | 401, redirect ke login | Sanctum token TTL |
| TC-74 | Autosave remainingSeconds=0 | — | Session ter-save 0, timer expired | |
| TC-75 | Concurrent tab — dua tab buka sesi sama | — | Jawaban upsert, data konsisten | `updateOrCreate` di backend |
| TC-76 | Submit dengan jawaban partial | — | Backend hitung skor dari yang terjawab | Cek `answeredQuestions < totalQuestions` |

---

## Ringkasan

| Area | Jumlah TC |
|------|-----------|
| 1. Login | 7 |
| 2. Explanation Page | 5 |
| 3. Start Evaluasi | 6 |
| 4. Fetch Session / Navigasi | 8 |
| 5. Save Answer | 9 |
| 6. Autosave | 8 |
| 7. Timer & Timeout | 5 |
| 8. Submit Evaluasi | 8 |
| 9. Hasil Evaluasi | 7 |
| 10. Nav Guard & Role | 4 |
| 11. Reset | 4 |
| 12. Edge Cases & Security | 5 |
| **Total** | **76** |
