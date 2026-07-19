# Changelog

## v1.3.0

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

## v1.2.0

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

## v1.1.0

### Changed

- Evaluation result is calculated per Indicator.
- Recommendation is mapped by Indicator + Category.
- Database schema remains unchanged.
- Instrument hierarchy remains:
  Component → Sub Component → Indicator → Question.