# Module: Questions V2 — Flat Listing with Cascade Filter

## Implementasi API V2 untuk Halaman Pernyataan Flat

**Referensi:**
- [ADR-004 — API V2 Questions Flat Listing](../ADR/ADR-004-API-V2-Questions-Flat-Listing.md)
- [API Specification — Section 14 V2 Questions](../API-SPECIFICATION.md#-14-v2--questions-flatcascade-api)
- [HTML Prototypes — List](../html/v3/admin/statements/pernyataan-list.html)
- [HTML Prototypes — Add](../html/v3/admin/statements/pernyataan-add.html)
- [HTML Prototypes — Edit](../html/v3/admin/statements/pernyataan-edit.html)
- [HTML Prototypes — Detail](../html/v3/admin/statements/pernyataan-detail.html)
- [Existing V1 Controller](../API-SPECIFICATION.md#-14-v2--questions-flatcascade-api) `app/Http/Controllers/Api/Admin/PertanyaanController.php`

---

## 1. Overview

Module ini mengimplementasikan API V2 untuk halaman pernyataan flat (list, add, edit, detail, delete) yang sudah diprototype. Berbeda dengan V1 yang membutuhkan `indicatorId` (nested dalam hirarki), V2 menyediakan akses **flat** ke semua pernyataan dengan cascade filter dan hierarchy context di response.

| Aspek | V1 (Existing) | V2 (Baru) |
|-------|---------------|-----------|
| Route prefix | `/api/v1/admin/questions` | `/api/v2/admin/questions` |
| Filter | `indicatorId` saja | `instrumentId`, `componentId`, `subComponentId`, `indicatorId` + cascade |
| Response | Flat `indicatorId` | Hierarchy context (indicator → subComponent → component → questionnaire) |
| Controller | `PertanyaanController` | `PertanyaanV2Controller` |
| Resource | `QuestionResource` | `QuestionListResource`, `QuestionDetailResource` |
| Search | `search` → `question_text` | Sama |
| Tree endpoint | Tidak ada | `/questions/tree` untuk populate cascade dropdown |

**Tidak ada perubahan struktur database.** Module ini hanya menambah:
- 1 controller baru
- 2 resource baru
- 6 route baru (index, store, show, update, destroy, tree)

---

## 2. Files to Create / Modify

```
CREATE  app/Http/Controllers/Api/Admin/V2/PertanyaanV2Controller.php
CREATE  app/Http/Resources/V2/QuestionListResource.php
CREATE  app/Http/Resources/V2/QuestionDetailResource.php
MODIFY  routes/api.php                             → tambah prefix v2
```

---

## 3. Route

File: `routes/api.php`

```php
use App\Http\Controllers\Api\Admin\V2\PertanyaanV2Controller;

Route::prefix('v2')->group(function () {
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        Route::get('/questions/tree', [PertanyaanV2Controller::class, 'tree']);
        Route::apiResource('questions', PertanyaanV2Controller::class);
    });
});
```

Route `apiResource` otomatis generate:

| Method | URI | Action | Route Name |
|--------|-----|--------|------------|
| GET | `/api/v2/admin/questions` | index | `v2.admin.questions.index` |
| POST | `/api/v2/admin/questions` | store | `v2.admin.questions.store` |
| GET | `/api/v2/admin/questions/{question}` | show | `v2.admin.questions.show` |
| PUT/PATCH | `/api/v2/admin/questions/{question}` | update | `v2.admin.questions.update` |
| DELETE | `/api/v2/admin/questions/{question}` | destroy | `v2.admin.questions.destroy` |
| GET | `/api/v2/admin/questions/tree` | tree | `v2.admin.questions.tree` |

---

## 4. Resources

### 4.1 QuestionListResource — untuk koleksi (index)

`app/Http/Resources/V2/QuestionListResource.php`

```php
<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $indicator = $this->indicator;
        $subComponent = $indicator?->subComponent;
        $component = $subComponent?->component;
        $questionnaire = $component?->questionnaire;

        return [
            'id' => $this->id,
            'questionText' => $this->question_text,
            'weight' => (float) $this->weight,
            'isActive' => (bool) $this->is_active,
            'orderNumber' => $this->order_number,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'indicator' => $indicator ? [
                'id' => $indicator->id,
                'name' => $indicator->name,
                'subComponent' => $subComponent ? [
                    'id' => $subComponent->id,
                    'name' => $subComponent->name,
                    'component' => $component ? [
                        'id' => $component->id,
                        'name' => $component->name,
                        'questionnaire' => $questionnaire ? [
                            'id' => $questionnaire->id,
                            'title' => $questionnaire->title,
                            'period' => $questionnaire->period ? [
                                'id' => $questionnaire->period->id,
                                'name' => $questionnaire->period->name,
                            ] : null,
                        ] : null,
                    ] : null,
                ] : null,
            ] : null,
        ];
    }
}
```

### 4.2 QuestionDetailResource — untuk single item (show, store, update)

`app/Http/Resources/V2/QuestionDetailResource.php`

Sama struktur dengan `QuestionListResource` — bisa extends atau duplikasi. Dipisah untuk antisipasi jika response detail nanti perlu field tambahan.

```php
<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return (new QuestionListResource($this))->toArray($request);
    }
}
```

> Jika di masa depan halaman detail perlu field tambahan (misal `responseAnswers` count), tinggal tambah di `QuestionDetailResource` tanpa mempengaruhi response list.

---

## 5. Controller

`app/Http/Controllers/Api/Admin/V2/PertanyaanV2Controller.php`

### 5.1 Traits & Dependencies

```php
<?php

namespace App\Http\Controllers\Api\Admin\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\QuestionListResource;
use App\Http\Resources\V2\QuestionDetailResource;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
```

### 5.2 Tree Endpoint

Mengembalikan data cascade untuk dropdown filter dan form.

```php
public function tree()
{
    $data = Questionnaire::with([
        'period',
        'components.subComponents.indicators' => fn($q) => $q->orderBy('name')
    ])
        ->where('is_active', true)
        ->orderBy('title')
        ->get()
        ->map(fn($q) => [
            'id' => $q->id,
            'title' => $q->title,
            'period' => $q->period?->name,
            'components' => $q->components->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'subComponents' => $c->subComponents->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'indicators' => $s->indicators->map(fn($i) => [
                        'id' => $i->id,
                        'name' => $i->name,
                    ]),
                ]),
            ]),
        ]);

    return $this->successResponse($data, 'Tree data retrieved successfully');
}
```

### 5.3 Index — List with Cascade Filter

```php
public function index(Request $request)
{
    $query = Question::with('indicator.subComponent.component.questionnaire.period');

    // Cascade filter — AND conditions
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

    // Sort
    $sortBy = in_array($request->sortBy, ['order_number', 'created_at', 'question_text']) 
        ? $request->sortBy 
        : 'order_number';
    $sortOrder = $request->sortOrder === 'desc' ? 'desc' : 'asc';
    $query->orderBy($sortBy, $sortOrder);

    // Paginate
    $limit = min((int) $request->get('limit', 10), 100);
    $questions = $query->paginate($limit);

    return response()->json([
        'status' => true,
        'message' => 'Questions retrieved successfully',
        'data' => [
            'contents' => QuestionListResource::collection($questions),
            'meta' => [
                'page' => $questions->currentPage(),
                'limit' => $questions->perPage(),
                'total' => $questions->total(),
                'totalPages' => $questions->lastPage(),
            ],
        ],
    ]);
}
```

> **Performance note:** `whereHas` bertingkat bisa lambat. Pastikan kolom foreign key ter-index: `questions.indicator_id`, `indicators.sub_component_id`, `sub_components.component_id`, `components.questionnaire_id`.

### 5.4 Store — Create

```php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'indicatorId' => 'required|exists:indicators,id',
        'questionText' => 'required|string|max:1000',
        'weight' => 'required|numeric|min:0|max:1',
        'isActive' => 'nullable|boolean',
        'orderNumber' => 'nullable|integer|min:0',
    ]);

    if ($validator->fails()) {
        return $this->errorResponse('Validation failed', 422, $validator->errors());
    }

    $data = $validator->validated();

    $dbData = [
        'indicator_id' => $data['indicatorId'],
        'question_text' => $data['questionText'],
        'weight' => $data['weight'],
        'is_active' => $data['isActive'] ?? 1,
    ];

    if (!isset($data['orderNumber'])) {
        $maxOrder = Question::where('indicator_id', $dbData['indicator_id'])
            ->max('order_number');
        $dbData['order_number'] = ($maxOrder ?? 0) + 1;
    } else {
        $dbData['order_number'] = $data['orderNumber'];
    }

    $question = Question::create($dbData);

    return $this->successResponse(
        new QuestionDetailResource($question->load('indicator.subComponent.component.questionnaire')),
        'Question created successfully',
        201
    );
}
```

### 5.5 Show — Detail

```php
public function show($id)
{
    $question = Question::with('indicator.subComponent.component.questionnaire.period')->find($id);

    if (!$question) {
        return $this->errorResponse('Question not found', 404);
    }

    return $this->successResponse(
        new QuestionDetailResource($question),
        'Question retrieved successfully'
    );
}
```

### 5.6 Update

```php
public function update(Request $request, $id)
{
    $question = Question::find($id);

    if (!$question) {
        return $this->errorResponse('Question not found', 404);
    }

    $validator = Validator::make($request->all(), [
        'indicatorId' => 'required|exists:indicators,id',
        'questionText' => 'required|string|max:1000',
        'weight' => 'required|numeric|min:0|max:1',
        'isActive' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return $this->errorResponse('Validation failed', 422, $validator->errors());
    }

    $data = $validator->validated();

    $question->update([
        'indicator_id' => $data['indicatorId'],
        'question_text' => $data['questionText'],
        'weight' => $data['weight'],
        'is_active' => $data['isActive'] ?? $question->is_active,
    ]);

    return $this->successResponse(
        new QuestionDetailResource($question->fresh()->load('indicator.subComponent.component.questionnaire')),
        'Question updated successfully'
    );
}
```

> **Catatan:** `orderNumber` tidak bisa diubah via update endpoint ini. Jika diperlukan reorder, buat endpoint khusus `/api/v2/admin/questions/reorder`.

### 5.7 Destroy

```php
public function destroy($id)
{
    $question = Question::find($id);

    if (!$question) {
        return $this->errorResponse('Question not found', 404);
    }

    $question->delete(); // Soft Delete

    return $this->successResponse(null, 'Question deleted successfully');
}
```

---

## 6. Implementation Order

| Step | Task | File | Notes |
|------|------|------|-------|
| 1 | Buat folder `V2/` di Controllers & Resources | `mkdir -p` | - |
| 2 | Buat `QuestionListResource` | `app/Http/Resources/V2/QuestionListResource.php` | Base resource |
| 3 | Buat `QuestionDetailResource` | `app/Http/Resources/V2/QuestionDetailResource.php` | Extends list |
| 4 | Buat `PertanyaanV2Controller` | `app/Http/Controllers/Api/Admin/V2/PertanyaanV2Controller.php` | Semua method |
| 5 | Tambah route v2 | `routes/api.php` | Prefix v2 |
| 6 | Test semua endpoint | curl / Postman | Lihat testing plan |

---

## 7. Testing Plan

### 7.1 Tree Endpoint

```bash
# Pastikan return data hirarki lengkap
curl -s http://localhost:8000/api/v2/admin/questions/tree \
  -H "Authorization: Bearer $TOKEN" | jq '.data | length'

# Validasi struktur
curl -s http://localhost:8000/api/v2/admin/questions/tree \
  -H "Authorization: Bearer $TOKEN" | jq '.data[0].components[0].subComponents[0].indicators[0].name'
```

### 7.2 List with Cascade Filter

```bash
# List all (paginate)
curl -s "http://localhost:8000/api/v2/admin/questions?page=1&limit=5" \
  -H "Authorization: Bearer $TOKEN" | jq '.data.meta'

# Filter by instrument
curl -s "http://localhost:8000/api/v2/admin/questions?instrumentId=1" \
  -H "Authorization: Bearer $TOKEN" | jq '.data.contents | length'

# Cascade filter: instrument + component
curl -s "http://localhost:8000/api/v2/admin/questions?instrumentId=1&componentId=1" \
  -H "Authorization: Bearer $TOKEN"

# Search
curl -s "http://localhost:8000/api/v2/admin/questions?search=energi" \
  -H "Authorization: Bearer $TOKEN" | jq '.data.contents[].questionText'

# Sort by created_at descending
curl -s "http://localhost:8000/api/v2/admin/questions?sortBy=created_at&sortOrder=desc" \
  -H "Authorization: Bearer $TOKEN"
```

### 7.3 CRUD

```bash
# Create
curl -s -X POST http://localhost:8000/api/v2/admin/questions \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"indicatorId":1,"questionText":"Test question?","weight":0.5,"isActive":true}' \
  | jq '.data.id'

# Detail
curl -s http://localhost:8000/api/v2/admin/questions/1 \
  -H "Authorization: Bearer $TOKEN" | jq '.data.indicator.questionnaire.title'

# Update
curl -s -X PUT http://localhost:8000/api/v2/admin/questions/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"indicatorId":1,"questionText":"Updated?","weight":0.9,"isActive":false}'

# Delete
curl -s -X DELETE http://localhost:8000/api/v2/admin/questions/1 \
  -H "Authorization: Bearer $TOKEN" | jq '.message'
```

---

## 8. Edge Cases & Notes

1. **Filter combination:** Cascade filter bersifat AND — jika FE kirim `instrumentId=1&componentId=2`, backend return hanya questions yang memenuhi KEDUA kondisi. Jika kombinasi tidak valid (misal componentId milik instrument lain), hasilnya kosong (bukan error).

2. **Empty result:** Jika filter/search tidak ketemu, return `contents: []` dengan `meta.total: 0` — bukan error.

3. **Tree cache:** Data tree jarang berubah. Pertimbangkan cache (Redis / Laravel Cache) dengan TTL 1 jam untuk mengurangi query.

4. **Pagination:** Default `limit=10`, maksimal `100`. FE wajib handle pagination untuk dataset besar.

5. **Soft delete:** Questions yang sudah di-soft-delete otomatis tidak muncul di index atau show. Destroy menggunakan soft delete.

6. **Order number:** Auto-generated saat create (`max(order_number) + 1` per indicator). Tidak bisa diupdate via endpoint biasa.
