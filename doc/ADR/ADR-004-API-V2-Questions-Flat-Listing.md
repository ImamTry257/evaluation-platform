# ADR-004: API v2 — Flat Question Listing with Cascade Filters

**Status:** Proposed

**Date:** 2026-07-24

> **API Spec Reference:** Lihat `doc/API-SPECIFICATION.md` section 14 untuk endpoint reference lengkap dan terbaru.

---

## Context

Halaman pernyataan (list, add, edit, detail) yang sudah diprototype di `doc/html/v3/admin/statements/` membutuhkan endpoint API baru. Berbeda dengan halaman existing yang mengandalkan parameter `indicatorId` (nested di dalam hirarki), halaman baru ini adalah **flat view** — semua pernyataan dari seluruh instrumen bisa diakses langsung dari satu menu sidebar.

Kebutuhan untuk 4 halaman:

| Halaman | Kebutuhan Utama |
|---------|----------------|
| **List** | Flat paginated list, cascade filter (Instrument → Komponen → SubKomponen → Indikator), full-text search, tree endpoint untuk populate dropdown filter |
| **Add** | Form dengan cascade dropdown (pilih Instrument → Komponen → SubKomponen → Indikator), simpan pernyataan |
| **Edit** | Pre-populated form, cascade dropdown dengan nilai existing, update pernyataan |
| **Detail** | Menampilkan semua data pernyataan + hierarchy context read-only |

API versioning saat ini menggunakan prefix `/api/v1`. Agar tidak merusak endpoint yang sudah dipakai FE existing, endpoint baru akan menggunakan prefix `/api/v2`.

---

## Decision

### 1. Route Structure

```
GET    /api/v2/admin/questions             → index (list)
POST   /api/v2/admin/questions             → store (create)
GET    /api/v2/admin/questions/{id}         → show (detail)
PUT    /api/v2/admin/questions/{id}         → update
DELETE /api/v2/admin/questions/{id}         → destroy
GET    /api/v2/admin/questions/tree         → cascade tree data
```

Prefix `v2` dipisah di `routes/api.php` dalam group sendiri:

```php
Route::prefix('v2')->group(function () {
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        Route::get('/questions/tree', [PertanyaanV2Controller::class, 'tree']);
        Route::apiResource('questions', PertanyaanV2Controller::class);
    });
});
```

### 2. Controller & Resource

| File | Path |
|------|------|
| Controller | `app/Http/Controllers/Api/Admin/V2/PertanyaanV2Controller.php` |
| List/DTO | `app/Http/Resources/V2/QuestionListResource.php` |
| Detail (single) | `app/Http/Resources/V2/QuestionDetailResource.php` |

Pisah dari `PertanyaanController` V1 karena response shape berbeda — V2 menyertakan hierarchy context lengkap di tiap response.

---

### 3. Endpoint Details

---

#### 3.1. `GET /api/v2/admin/questions` — List

**Query Parameters:**

| Parameter | Tipe | Wajib | Default | Deskripsi |
|-----------|------|-------|---------|-----------|
| `search` | string | - | - | Full-text search by `question_text` |
| `instrumentId` | int | - | - | Filter by questionnaire/instrument |
| `componentId` | int | - | - | Filter by component |
| `subComponentId` | int | - | - | Filter by sub component |
| `indicatorId` | int | - | - | Filter by indicator |
| `page` | int | - | 1 | Halaman |
| `limit` | int | - | 10 | Per halaman (max 100) |
| `sortBy` | string | - | `order_number` | `order_number`, `created_at`, `question_text` |
| `sortOrder` | string | - | `asc` | `asc` / `desc` |

**Filter Logic — AND antar parameter:**

```php
if ($request->filled('search')) {
    $query->where('question_text', 'like', '%' . $request->search . '%');
}
if ($request->filled('indicatorId')) {
    $query->where('indicator_id', $request->indicatorId);
}
if ($request->filled('subComponentId')) {
    $query->whereHas('indicator', fn($q) => $q->where('sub_component_id', $request->subComponentId));
}
if ($request->filled('componentId')) {
    $query->whereHas('indicator.subComponent', fn($q) => $q->where('component_id', $request->componentId));
}
if ($request->filled('instrumentId')) {
    $query->whereHas('indicator.subComponent.component', fn($q) => $q->where('questionnaire_id', $request->instrumentId));
}
```

**Response 200:**

```json
{
  "status": true,
  "message": "Questions retrieved successfully",
  "data": {
    "contents": [
      {
        "id": 1,
        "questionText": "Seberapa sering Anda mematikan peralatan listrik...",
        "weight": 0.8,
        "isActive": true,
        "orderNumber": 1,
        "createdAt": "2025-01-15 00:00:00",
        "updatedAt": "2025-03-22 00:00:00",
        "indicator": {
          "id": 1,
          "name": "Indikator A1 — Efisiensi Energi",
          "subComponent": {
            "id": 1,
            "name": "Pemakaian Listrik Kelas",
            "component": {
              "id": 1,
              "name": "Energi: Konsumsi Listrik",
              "questionnaire": {
                "id": 1,
                "title": "Evaluasi Penggunaan Energi 2024",
                "period": {
                  "id": 1,
                  "name": "Ganjil 2023/2024"
                }
              }
            }
          }
        }
      }
    ],
    "meta": {
      "page": 1,
      "limit": 10,
      "total": 45,
      "totalPages": 5
    }
  }
}
```

---

#### 3.2. `GET /api/v2/admin/questions/tree` — Cascade Tree

Mengembalikan data hirarki lengkap untuk populate cascade dropdown di filter dan form.

**Response 200:**

```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "title": "Evaluasi Penggunaan Energi 2024",
      "period": "Ganjil 2023/2024",
      "components": [
        {
          "id": 1,
          "name": "Energi: Konsumsi Listrik",
          "subComponents": [
            {
              "id": 1,
              "name": "Pemakaian Listrik Kelas",
              "indicators": [
                { "id": 1, "name": "Indikator A1 — Efisiensi Energi" },
                { "id": 2, "name": "Indikator A2 — Penggunaan LED" }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

**Query:**

```php
Questionnaire::with(['period', 'components.subComponents.indicators' => fn($q) => $q->orderBy('name')])
    ->where('is_active', true)
    ->orderBy('title')
    ->get();
```

Data ini relatif statis — bisa di-cache di FE atau pake HTTP cache headers.

---

#### 3.3. `POST /api/v2/admin/questions` — Create

**Request Body:**

```json
{
  "indicatorId": 1,
  "questionText": "Apakah sekolah Anda menggunakan lampu LED hemat energi?",
  "weight": 0.6,
  "isActive": true
}
```

| Field | Tipe | Wajib | Validasi |
|-------|------|-------|----------|
| `indicatorId` | int | yes | `required|exists:indicators,id` |
| `questionText` | string | yes | `required|string|max:1000` |
| `weight` | float | yes | `required|numeric|min:0|max:1` |
| `isActive` | boolean | no | `nullable|boolean` default `true` |
| `orderNumber` | int | no | `nullable|integer|min:0` — auto jika tidak dikirim |

**Auto-generate `orderNumber`:**

```php
if (!isset($data['orderNumber'])) {
    $maxOrder = Question::where('indicator_id', $data['indicatorId'])
        ->max('order_number');
    $dbData['order_number'] = ($maxOrder ?? 0) + 1;
}
```

**Response 201:**

```json
{
  "status": true,
  "message": "Question created successfully",
  "data": {
    "id": 46,
    "questionText": "Apakah sekolah Anda menggunakan lampu LED hemat energi?",
    "weight": 0.6,
    "isActive": true,
    "orderNumber": 1,
    "createdAt": "2026-07-24 10:30:00",
    "updatedAt": "2026-07-24 10:30:00",
    "indicator": {
      "id": 1,
      "name": "Indikator A2 — Penggunaan LED",
      "subComponent": {
        "id": 1,
        "name": "Pemakaian Listrik Kelas",
        "component": {
          "id": 1,
          "name": "Energi: Konsumsi Listrik",
          "questionnaire": {
            "id": 1,
            "title": "Evaluasi Penggunaan Energi 2024"
          }
        }
      }
    }
  }
}
```

**Error 422:**

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "indicatorId": ["The indicator id field is required."],
    "questionText": ["The question text field is required."]
  }
}
```

---

#### 3.4. `GET /api/v2/admin/questions/{id}` — Show (Detail)

**Response 200:**

```json
{
  "status": true,
  "message": "Question retrieved successfully",
  "data": {
    "id": 1,
    "questionText": "Seberapa sering Anda mematikan peralatan listrik yang tidak digunakan di sekolah?",
    "weight": 0.8,
    "isActive": true,
    "orderNumber": 1,
    "createdAt": "2025-01-15 00:00:00",
    "updatedAt": "2025-03-22 00:00:00",
    "indicator": {
      "id": 1,
      "name": "Indikator A1 — Efisiensi Energi",
      "subComponent": {
        "id": 1,
        "name": "Pemakaian Listrik Kelas",
        "component": {
          "id": 1,
          "name": "Energi: Konsumsi Listrik",
          "questionnaire": {
            "id": 1,
            "title": "Evaluasi Penggunaan Energi 2024",
            "period": { "id": 1, "name": "Ganjil 2023/2024" }
          }
        }
      }
    }
  }
}
```

**Error 404:**

```json
{
  "status": false,
  "message": "Question not found"
}
```

---

#### 3.5. `PUT /api/v2/admin/questions/{id}` — Update

**Request Body** (sama dengan store):

```json
{
  "indicatorId": 1,
  "questionText": "Updated question text...",
  "weight": 0.9,
  "isActive": false
}
```

| Field | Tipe | Wajib | Validasi |
|-------|------|-------|----------|
| `indicatorId` | int | yes | `required|exists:indicators,id` |
| `questionText` | string | yes | `required|string|max:1000` |
| `weight` | float | yes | `required|numeric|min:0|max:1` |
| `isActive` | boolean | no | `nullable|boolean` |

> **Catatan:** `orderNumber` tidak bisa diubah via update — jika perlu reorder, akan ada endpoint terpisah.

**Response 200:**

```json
{
  "status": true,
  "message": "Question updated successfully",
  "data": {
    "id": 1,
    "questionText": "Updated question text...",
    "weight": 0.9,
    "isActive": false,
    "orderNumber": 1,
    "createdAt": "2025-01-15 00:00:00",
    "updatedAt": "2026-07-24 11:00:00",
    "indicator": {
      "id": 1,
      "name": "Indikator A1 — Efisiensi Energi",
      "subComponent": {
        "id": 1,
        "name": "Pemakaian Listrik Kelas",
        "component": {
          "id": 1,
          "name": "Energi: Konsumsi Listrik",
          "questionnaire": {
            "id": 1,
            "title": "Evaluasi Penggunaan Energi 2024"
          }
        }
      }
    }
  }
}
```

---

#### 3.6. `DELETE /api/v2/admin/questions/{id}` — Destroy

**Response 200:**

```json
{
  "status": true,
  "message": "Question deleted successfully",
  "data": null
}
```

**Response 404:**

```json
{
  "status": false,
  "message": "Question not found"
}
```

---

### 4. Database — No Structural Changes

Tidak ada perubahan struktur table untuk V2. Semua endpoint menggunakan table dan kolom yang sama dengan V1:

| Table | Penggunaan | Status |
|-------|-----------|--------|
| `questions` | Kolom `id`, `indicator_id`, `question_text`, `weight`, `order_number`, `is_active`, `created_at`, `updated_at` | Sama, tidak ada migrasi baru |
| `indicators` | Relasi `indicator.subComponent.component.questionnaire` | Sama |
| `sub_components` | Relasi untuk cascade filter | Sama |
| `components` | Relasi untuk cascade filter | Sama |
| `questionnaires` | Relasi + filter `instrumentId` | Sama |

Yang berubah hanya **cara query** (pake `whereHas` cascade filter) dan **response shape** (hierarchy context dibungkus dalam nested object), bukan struktur data di database.

### 5. File Locations

```
app/Http/Controllers/Api/Admin/V2/
  └── PertanyaanV2Controller.php

app/Http/Resources/V2/
  ├── QuestionListResource.php       — untuk koleksi (index)
  └── QuestionDetailResource.php     — untuk single item (show, store, update)

routes/api.php
  — tambah Route::prefix('v2') ...
```

### 5. Naming Convention

| Di V1 (existing) | Di V2 (baru) | Alasan |
|------------------|-------------|--------|
| `PertanyaanController` | `PertanyaanV2Controller` | Namespace `V2` tidak cukup — nama class perlu suffix biar jelas di route list |
| `QuestionResource` | `QuestionListResource` / `QuestionDetailResource` | Response shape berbeda |
| `indicatorId` query | `indicatorId` + `subComponentId` + `componentId` + `instrumentId` | Cascade filter |

---

## Consequences

### Positive
- FE bisa fetching data pernyataan secara flat tanpa perlu tahu `indicatorId`
- Cascade filter handle di backend — FE cukup kirim parameter ID, backend urus join via `whereHas`
- Endpoint `/tree` dedicated untuk populate cascade dropdown, menghindari N+1 di FE
- V1 endpoint tidak terganggu sama sekali
- Response shape konsisten antar semua action (list/show/create/update)

### Negative
- Duplikasi kode dengan `PertanyaanController` V1 untuk store/update/destroy
- Perlu maintain 2 versi controller untuk entitas yang sama
- Query `whereHas` bertingkat (4 level) bisa lambat di dataset besar — perlu indexing

### Mitigation
- Indexing: pastikan kolom `indicator_id`, `sub_component_id`, `component_id`, `questionnaire_id` ter-index
- Untuk store/update/destroy, V2 bisa panggil service class yang sama dengan V1 jika logic-nya identik
- `/tree` bisa di-cache (redis atau cache laravel) karena jarang berubah

---

## Status

Proposed — implementasi dimulai dari halaman list (endpoint index + tree).

