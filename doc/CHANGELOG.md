# Changelog

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