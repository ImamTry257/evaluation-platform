# ADR-002: Responden Email-Only Authentication with Auto-Registration

Status: Accepted

Date: 2026-07-13

---

# Context

Platform memiliki dua jenis pengguna dengan flow authentikasi yang berbeda:
- **Admin:** Memiliki kredensial tetap (email + password) untuk akses berkala ke sistem
- **Responden:** Mengisi angket sekali per periode, tidak memerlukan password management

Awal desain mengasumsikan semua user memerlukan password, namun feedback dari product menunjukkan responden hanya perlu email verification.

---

# Decision

## Unified Authentication Endpoint

Menggunakan satu endpoint `/auth/login` dengan parameter `type` untuk mendukung multiple roles di masa depan.

```
POST /auth/login
{
  "email": "user@example.com",
  "password": "password123",  // optional, hanya untuk ADMIN
  "type": "ADMIN" | "RESPONDENT"
}
```

## Admin Authentication Flow

Admin login memverifikasi email + password terhadap database.

Error responses:
- "Email not found" - jika email tidak ada di DB
- "Password salah" - jika password tidak cocok

## Responden Authentication Flow

Responden login hanya memerlukan email, tanpa password.

**Verification Logic:**
1. Jika email terdaftar sebagai responden → generate token, login langsung
2. Jika email TIDAK terdaftar → auto-register sebagai responden → generate token
3. Jika email terdaftar sebagai admin/role lain → error "Email not found"

**Auto-registration behavior:**
- Sistem membuat user baru dengan role=respondent
- Password di-set nullable atau random hash (lihat Database Decision)
- User langsung mendapat akses ke active evaluation period

## Database Adjustment

Kolom `password` dalam tabel `users` menjadi **nullable** untuk responden.

```sql
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL;
```

Alasan:
- Semantic clarity: password nullable secara eksplisit menunjukkan responden tidak memiliki password
- Alternatif (random hash) bekerja tapi kurang clear secara semantic

---

# Consequences

## Advantages

- **Scalable:** Type parameter memungkinkan menambah role baru (teacher, principal, etc) di masa depan
- **User-friendly:** Responden tidak perlu password management
- **Simplified flow:** Auto-registration mengurangi friction onboarding responden
- **Security:** Email-only flow mengurangi attack surface untuk responden

## Disadvantages

- **Enumeration risk:** Email-only login bisa di-enumerate untuk cari responden
  - Mitigation: Rate limiting + consistent "Email not found" error message
- **Account takeover:** Email yang salah bisa di-register sebagai responden
  - Mitigation: Email list harus di-maintain admin dengan benar di periode setup
- **Complexity:** Handler berbeda untuk admin vs responden di login service

---

# Implementation Notes

## Login Service Logic

```
function login(email, password, type):
  if type == ADMIN:
    user = findUserByEmail(email)
    if !user:
      throw "Email not found"
    if !verifyPassword(password, user.password):
      throw "Password salah"
    if user.role != ADMIN:
      throw "Email not found"  // prevent admin endpoints access
    return generateToken(user)
    
  if type == RESPONDENT:
    user = findUserByEmail(email)
    if user:
      if user.role != RESPONDENT:
        throw "Email not found"  // email used by admin/other role
      return generateToken(user)
    else:
      // Auto-register
      user = createUser({
        email: email,
        role: RESPONDENT,
        password: null
      })
      return generateToken(user)
```

## Email Validation

- Email tetap di-validate format (RFC 5322)
- Case-insensitive email lookup (emails are case-insensitive per RFC 5321)

## Rate Limiting

Implementasi rate limiting untuk prevent brute force:
- Max 5 login attempts per email per 15 minutes
- Applied to both admin dan responden flows

---

# Related ADRs

- [[ADR-001-Evaluation-Logic]] - Evaluation calculation per indicator

---

# References

- API-SPECIFICATION.md - Login endpoint documentation
- DATABASE-DESIGN.md - Users table schema
