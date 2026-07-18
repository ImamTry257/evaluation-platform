# Module: Master Data
## Implementasi CRUD Master Data

**Referensi:**
- [API Specification - Admin Endpoints](../API-SPECIFICATION.md)
- [Business Process - Proses 1-5](../BUSINESS-PROCESS.md)

---

## 1. Overview

Module ini menangani CRUD operations untuk semua master data:
1. Period (Periode Evaluasi)
2. Questionnaire (Kuesioner)
3. Component (Komponen)
4. Sub Component (Sub Komponen)
5. Indicator (Indikator)
6. Question (Pertanyaan)
7. Respondent (Responden)
8. Recommendation (Rekomendasi)

> **Note:** Evaluation Periods, Components, Sub-Components, Indicators, dan Questions menggunakan soft delete. Lihat section 1.1 untuk detail.

---

## 1.1 Soft Delete Implementation

**Models yang menggunakan soft delete:**
- EvaluationPeriods
- Components
- SubComponents
- Indicators
- Questions
- Tambah field `deleted_at` via migration
- Model gunakan `SoftDeletes` trait
- `DELETE /periods/{id}` mengatur `deleted_at = now()` (bukan hard delete)
- `GET /periods` dan `GET /periods/{id}` hanya return record yang `deleted_at IS NULL`

```php
// Model
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationPeriod extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}

// Controller - tidak perlu ubah, Laravel handle otomatis
$period->delete(); // Set deleted_at = now()
EvaluationPeriod::find($id); // Exclude soft-deleted
EvaluationPeriod::withTrashed()->find($id); // Include soft-deleted
```

---

## 2. Architecture Pattern

```
Controller → Service → Repository → Eloquent Model → Database
```

---

## 3. Repository Pattern

### 3.1 Interface

```php
<?php

namespace App\Repositories\Interfaces;

interface PeriodRepositoryInterface
{
    public function getAll(array $filters = []);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getActive();
}
```

### 3.2 Eloquent Implementation

```php
<?php

namespace App\Repositories\Eloquent;

use App\Models\EvaluationPeriod;
use App\Repositories\Interfaces\PeriodRepositoryInterface;

class PeriodRepository implements PeriodRepositoryInterface
{
    protected $model;

    public function __construct(EvaluationPeriod $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters = [])
    {
        $query = $this->model->query();

        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        return $query->latest()->paginate(10);
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $period = $this->findById($id);
        $period->update($data);
        return $period;
    }

    public function delete($id)
    {
        $period = $this->findById($id);
        return $period->delete();
    }

    public function getActive()
    {
        return $this->model->where('isActive', true)->first();
    }
}
```

---

## 4. Service Layer

### 4.1 PeriodService

```php
<?php

namespace App\Services\Master;

use App\Repositories\Interfaces\PeriodRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PeriodService
{
    protected $repository;

    public function __construct(PeriodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function getById($id)
    {
        return $this->repository->findById($id);
    }

    public function create(array $data)
    {
        // Only one period can be active at a time
        if ($data['isActive'] ?? false) {
            $this->deactivateAll();
        }

        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        if ($data['isActive'] ?? false) {
            $this->deactivateAll();
        }

        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        $period = $this->repository->findById($id);

        // Cannot delete if has questionnaires
        if ($period->questionnaires()->count() > 0) {
            throw new \Exception('Cannot delete period with existing questionnaires');
        }

        return $this->repository->delete($id);
    }

    private function deactivateAll()
    {
        \App\Models\EvaluationPeriod::where('isActive', true)
            ->update(['isActive' => false]);
    }
}
```

---

## 5. Controllers

### 5.1 PeriodController

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Master\PeriodService;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    use HasApiResponse;

    protected $service;

    public function __construct(PeriodService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'isActive']);
        $periods = $this->service->getAll($filters);
        return $this->successResponse($periods);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'isActive' => 'boolean',
        ]);

        $period = $this->service->create($validated);
        return $this->successResponse($period, 'Period created', 201);
    }

    public function show($id)
    {
        $period = $this->service->getById($id);
        return $this->successResponse($period);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'isActive' => 'boolean',
        ]);

        $period = $this->service->update($id, $validated);
        return $this->successResponse($period, 'Period updated');
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return $this->successResponse(null, 'Period deleted');
    }
}
```

---

## 6. CRUD Matrix

| Module | Index | Store | Show | Update | Destroy | Special |
|--------|-------|-------|------|--------|---------|---------|
| Period | ✅ | ✅ | ✅ | ✅ | ✅ | Only one active |
| Questionnaire | ✅ | ✅ | ✅ | ✅ | ✅ | Publish, Close |
| Component | ✅ | ✅ | ✅ | ✅ | ✅ | Order |
| Sub Component | ✅ | ✅ | ✅ | ✅ | ✅ | Order |
| Indicator | ✅ | ✅ | ✅ | ✅ | ✅ | Order |
| Question | ✅ | ✅ | ✅ | ✅ | ✅ | Weight, Order |
| Respondent | ✅ | ✅ | ✅ | ✅ | ✅ | Import CSV |
| Recommendation | ✅ | ✅ | ✅ | ✅ | ✅ | Score range |

---

## 7. Frontend Views

### 7.1 Conversion from HTML Prototypes

| HTML Prototype | Vue View |
|----------------|----------|
| admin/period.html | admin/PeriodListView.vue |
| admin/master-questionnaires.html | admin/QuestionnaireListView.vue |
| admin/master-component.html | admin/ComponentListView.vue |
| admin/master-sub-component.html | admin/SubComponentListView.vue |
| admin/master-indicator.html | admin/IndicatorListView.vue |
| admin/master-question.html | admin/QuestionListView.vue |
| admin/master-responden.html | admin/RespondentListView.vue |
| admin/master-recomendation.html | admin/RecommendationListView.vue |

### 7.2 List View Pattern

```vue
<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Master Periode</h1>
      <button @click="showCreateModal = true" class="btn-primary">
        + Tambah Periode
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="mb-4">
      <input v-model="search" @input="debouncedSearch" placeholder="Cari..." class="input" />
    </div>

    <!-- Table -->
    <table class="w-full">
      <thead>...</thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td>{{ item.name }}</td>
          <td>{{ item.startDate }}</td>
          <td>
            <span :class="item.isActive ? 'badge-success' : 'badge-secondary'">
              {{ item.isActive ? 'Aktif' : 'Inactive' }}
            </span>
          </td>
          <td>
            <button @click="edit(item)">Edit</button>
            <button @click="delete(item.id)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <Pagination :meta="pagination" @page-change="fetchData" />
  </div>
</template>
```

---

## 8. Service Provider Registration

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(
        \App\Repositories\Interfaces\PeriodRepositoryInterface::class,
        \App\Repositories\Eloquent\PeriodRepository::class
    );
    // ... bind other repositories
}
```

---

**Lanjut ke:** [07-MODULE-EVALUATION](07-MODULE-EVALUATION.md)
