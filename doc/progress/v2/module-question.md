# Progress — Module: Questions V2

## Flat Listing with Cascade Filter

**Versi:** 1.0.0 — 24 Juli 2026

**Referensi:**
- [ADR-004 — API V2 Questions Flat Listing](../ADR/ADR-004-API-V2-Questions-Flat-Listing.md)
- [API-SPEC — Section 14 V2 Questions](../API-SPECIFICATION.md#-14-v2--questions-flatcascade-api)
- [Implementation Module — 10-MODULE-QUESTION-V2](../implementation/10-MODULE-QUESTION-V2.md)
- [HTML Prototypes — List](../html/v3/admin/statements/pernyataan-list.html)
- [HTML Prototypes — Add](../html/v3/admin/statements/pernyataan-add.html)
- [HTML Prototypes — Edit](../html/v3/admin/statements/pernyataan-edit.html)
- [HTML Prototypes — Detail](../html/v3/admin/statements/pernyataan-detail.html)

---

## Documentation Status

| Dokumen | Status | Notes |
|---------|--------|-------|
| ADR-004 | ✅ Accepted | Keputusan arsitektur V2 |
| API-SPEC section 14 | ✅ Done | Endpoint reference lengkap |
| 10-MODULE-QUESTION-V2.md | ✅ Done | Implementasi guide |
| PROGRESS.md (ini) | ✅ Done | Tracker progress V2 |

---

## Implementation Checklist

### Files to Create

| # | File | Status | Notes |
|---|------|--------|-------|
| 1 | `app/Http/Controllers/Api/Admin/V2/PertanyaanV2Controller.php` | ✅ Done | 2 methods (tree, index) |
| 2 | `app/Http/Resources/V2/QuestionListResource.php` | ✅ Done | Response list with hierarchy |
| 3 | `app/Http/Resources/V2/QuestionDetailResource.php` | ✅ Done | Extends list |

### Files to Modify

| # | File | Status | Notes |
|---|------|--------|-------|
| 4 | `routes/api.php` — tambah prefix v2 | ✅ Done | Route::prefix('v2') outside v1 |

### Endpoint — Backend

| # | Method | Endpoint | Status | Notes |
|---|--------|----------|--------|-------|
| 5 | GET | `/api/v2/admin/questions/tree` | ✅ Done | Tree data cascade |
| 6 | GET | `/api/v2/admin/questions` | ✅ Done | List + cascade filter |
| 7 | POST | `/api/v2/admin/questions` | ✅ Done | Create + validasi |
| 8 | GET | `/api/v2/admin/questions/{id}` | ✅ Done | Detail + 404 |
| 9 | PUT | `/api/v2/admin/questions/{id}` | ✅ Done | Update |
| 10 | DELETE | `/api/v2/admin/questions/{id}` | ✅ Done | Soft delete |

### Testing

| # | Skenario | Status | Notes |
|---|----------|--------|-------|
| 11 | Tree endpoint | ✅ Pass | |
| 12 | List cascade filter (AND) | ✅ Pass | instrumentId + componentId + subComponentId |
| 13 | List search | ✅ Pass | |
| 14 | List sort + pagination | ✅ Pass | Falls back ke order_number |
| 15 | Empty result (invalid filter) | ✅ Pass | `contents: []`, `total: 0` |
| 16 | No token → 401 | ✅ Fixed | |
| 17 | Multiple param filter (AND) | ✅ Pass | |
| 18 | Create → 201 + hierarchy context | ✅ Pass | |
| 19 | Create validation → 422 | ✅ Pass | |
| 20 | Detail → 200 | ✅ Pass | |
| 21 | Detail 404 | ✅ Pass | |
| 22 | Update → 200 + persist | ✅ Pass | |
| 23 | Update validation → 422 | ✅ Pass | |
| 24 | Update 404 | ✅ Pass | |
| 25 | Delete → 200 (soft) | ✅ Pass | |
| 26 | Get deleted → 404 | ✅ Pass | |
| 27 | List deleted → 0 | ✅ Pass | |
| 28 | Delete 404 | ✅ Pass | |

### Edge Case Testing

| # | Skenario | Expect | Result | Bug? |
|---|----------|--------|--------|------|
| 29 | Create — empty questionText | 422 | ✅ 422 `field is required` | ✖️ |
| 30 | Create — weight > 1 | 422 | ✅ 422 `must not be greater than 1` | ✖️ |
| 31 | Create — weight < 0 | 422 | ✅ 422 `must be at least 0` | ✖️ |
| 32 | Create — text > 1000 chars | 422 | ✅ 422 `must not be greater than 1000` | ✖️ |
| 33 | Create — indicatorId not exist | 422 | ✅ 422 `invalid` | ✖️ |
| 34 | Create — isActive = false | false bool | ✅ `False` (type: bool) | ✖️ |
| 35 | Create — weight = 0 (boundary) | 200 | ✅ accepted | ✖️ |
| 36 | Create — special chars (<>"') | stored | ✅ stored correctly | ✖️ |
| 37 | Create — isActive = 1 (int) | cast bool | ✅ `True` (bool) | ✖️ |
| 38 | Create — weight = 1.001 (boundary) | 422 | ✅ 422 | ✖️ |
| 39 | order_number auto-increment | +1 | ✅ 8 → 9 | ✖️ |
| 40 | Update — isActive not sent | preserved | ✅ `True` unchanged | ✖️ |
| 41 | Update — same data (no-op) | 200 | ✅ 200 OK | ✖️ |
| 42 | List — filter by indicatorId | correct | ✅ 11 items | ✖️ |

**Total: 42/42 test pass — 0 bug found.**

---

## Total Progress

| Kategori | Total | Selesai | Pending | Progress |
|----------|-------|---------|---------|----------|
| Backend files | 4 | 0 | 4 | 0% |
| Endpoints | 6 | 0 | 6 | 0% |
| Testing | 10 | 0 | 10 | 0% |
| **Total** | **20** | **0** | **20** | **0%** |
