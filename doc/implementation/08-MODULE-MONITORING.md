# Module: Monitoring & Reports
## Implementasi Monitoring Sesi dan Export Laporan

**Referensi:**
- [API Specification - Monitoring & Report Endpoints](../API-SPECIFICATION.md)
- [Business Process - Proses 6-8](../BUSINESS-PROCESS.md)

---

## 1. Overview

Module ini menangani:
1. Monitoring sesi evaluasi oleh responden
2. Viewing hasil evaluasi
3. Export laporan Excel dan PDF

---

## 2. API Endpoints

| Method | Endpoint | Deskripsi | Auth | Status |
|--------|----------|-----------|------|--------|
| GET | `/admin/sessions` | List all sessions | Admin | ✅ Implemented |
| GET | `/admin/sessions/{sessionId}` | Get session detail | Admin | ✅ Implemented |
| GET | `/admin/reports` | Get report summary | Admin | ❌ Belum |
| POST | `/admin/reports/export-excel` | Export Excel | Admin | ❌ Belum |
| POST | `/admin/reports/export-pdf` | Export PDF | Admin | ❌ Belum |

> **Note:** Monitoring endpoints sudah diimplementasi di `MonitoringController`. Reports endpoints masih dalam pengembangan.

---

## 3. Backend Implementation

### 3.1 MonitoringController

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\MonitoringService;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    use HasApiResponse;

    protected $service;

    public function __construct(MonitoringService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['questionnaireId', 'status', 'search']);
        $sessions = $this->service->getAllSessions($filters);
        return $this->successResponse($sessions);
    }

    public function show($sessionId)
    {
        $session = $this->service->getSessionDetail($sessionId);
        return $this->successResponse($session);
    }
}
```

### 3.2 ReportController

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    use HasApiResponse;

    protected $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['questionnaireId', 'periodId']);
        $report = $this->service->getReportSummary($filters);
        return $this->successResponse($report);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['questionnaireId', 'periodId']);
        $data = $this->service->getExportData($filters);

        return Excel::download(new ReportsExport($data), 'evaluasi-report.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $sessionId = $request->input('sessionId');
        $data = $this->service->getSessionReport($sessionId);

        $pdf = Pdf::loadView('reports.session-pdf', $data);
        return $pdf->download('evaluasi-report.pdf');
    }
}
```

### 3.3 MonitoringService

```php
<?php

namespace App\Services\Report;

use App\Models\{ResponseSession, EvaluationResult};

class MonitoringService
{
    public function getAllSessions(array $filters = [])
    {
        $query = ResponseSession::with(['user', 'questionnaire']);

        if (isset($filters['questionnaireId'])) {
            $query->where('questionnaireId', $filters['questionnaireId']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate(15);
    }

    public function getSessionDetail($sessionId)
    {
        return ResponseSession::with(['user', 'questionnaire', 'answers.question.indicator', 'result.details.indicator'])
            ->findOrFail($sessionId);
    }
}
```

### 3.4 ReportService

```php
<?php

namespace App\Services\Report;

use App\Models\{ResponseSession, EvaluationResult, EvaluationResultDetail};

class ReportService
{
    public function getReportSummary(array $filters = [])
    {
        $query = ResponseSession::where('status', 'submitted');

        if (isset($filters['questionnaireId'])) {
            $query->where('questionnaireId', $filters['questionnaireId']);
        }

        $sessions = $query->with('result')->get();

        return [
            'totalSessions' => $sessions->count(),
            'averageScore' => $sessions->avg('result.overallPercentage'),
            'categoryDistribution' => $sessions->pluck('result.overallCategory')
                ->countBy()
                ->toArray(),
        ];
    }

    public function getExportData(array $filters = [])
    {
        $query = ResponseSession::with(['user', 'questionnaire', 'result.details.indicator'])
            ->where('status', 'submitted');

        if (isset($filters['questionnaireId'])) {
            $query->where('questionnaireId', $filters['questionnaireId']);
        }

        return $query->get();
    }

    public function getSessionReport($sessionId)
    {
        return ResponseSession::with(['user', 'questionnaire', 'result.details.indicator', 'result.details.recommendation'])
            ->where('status', 'submitted')
            ->findOrFail($sessionId);
    }
}
```

---

## 4. Excel Export

### 4.1 Create Export Class

```php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Responden',
            'Email',
            'Kuesioner',
            'Skor',
            'Persentase',
            'Kategori',
            'Tanggal Submit',
        ];
    }

    public function map($session): array
    {
        return [
            $session->id,
            $session->user->name,
            $session->user->email,
            $session->questionnaire->title,
            $session->result?->overallScore,
            $session->result?->overallPercentage . '%',
            $session->result?->overallCategory,
            $session->submittedAt->format('d/m/Y H:i'),
        ];
    }
}
```

---

## 5. PDF Export

### 5.1 Create PDF View

```blade
<!-- resources/views/reports/session-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Evaluasi</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        .section h3 { border-bottom: 2px solid #006c49; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background-color: #006c49; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Evaluasi Kebijakan Lingkungan</h1>
        <p>{{ $user->name }} - {{ $questionnaire->title }}</p>
    </div>

    <div class="section">
        <h3>Informasi Responden</h3>
        <p><strong>Nama:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Tanggal Submit:</strong> {{ $submittedAt->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section">
        <h3>Hasil Evaluasi</h3>
        <p><strong>Skor Keseluruhan:</strong> {{ $result->overallScore }}</p>
        <p><strong>Persentase:</strong> {{ $result->overallPercentage }}%</p>
        <p><strong>Kategori:</strong> {{ $result->overallCategory }}</p>
        <p><strong>Kesimpulan:</strong> {{ $result->conclusion }}</p>
    </div>

    <div class="section">
        <h3>Detail per Indikator</h3>
        <table>
            <thead>
                <tr>
                    <th>Indikator</th>
                    <th>Skor</th>
                    <th>Persentase</th>
                    <th>Kategori</th>
                    <th>Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result->details as $detail)
                <tr>
                    <td>{{ $detail->indicator->name }}</td>
                    <td>{{ $detail->score }}</td>
                    <td>{{ $detail->percentage }}%</td>
                    <td>{{ $detail->category }}</td>
                    <td>{{ $detail->recommendation }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
```

---

## 6. Frontend Implementation

### 6.1 Monitoring Page

Convert from `doc/html/admin/dashboard.html` (monitoring section):

```vue
<!-- src/views/admin/MonitoringView.vue -->
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const sessions = ref([])
const filters = ref({
  questionnaireId: null,
  status: null,
  search: '',
})
const pagination = ref({})

async function fetchSessions(page = 1) {
  const response = await api.get('/admin/sessions', {
    params: { ...filters.value, page }
  })
  sessions.value = response.data.data.data
  pagination.value = response.data.data
}

onMounted(() => fetchSessions())
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Monitoring Sesi Evaluasi</h1>

    <!-- Filters -->
    <div class="flex gap-4 mb-6">
      <select v-model="filters.questionnaireId" @change="fetchSessions">
        <option :value="null">Semua Kuesioner</option>
        <!-- options -->
      </select>
      <select v-model="filters.status" @change="fetchSessions">
        <option :value="null">Semua Status</option>
        <option value="in_progress">In Progress</option>
        <option value="submitted">Submitted</option>
        <option value="timeout">Timeout</option>
      </select>
      <input v-model="filters.search" @input="fetchSessions" placeholder="Cari responden..." />
    </div>

    <!-- Table -->
    <table class="w-full">
      <thead>
        <tr>
          <th>ID</th>
          <th>Responden</th>
          <th>Kuesioner</th>
          <th>Status</th>
          <th>Waktu Mulai</th>
          <th>Waktu Submit</th>
          <th>Progress</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="session in sessions" :key="session.id">
          <td>{{ session.id }}</td>
          <td>{{ session.user.name }}</td>
          <td>{{ session.questionnaire.title }}</td>
          <td>
            <span :class="'badge-' + session.status">{{ session.status }}</span>
          </td>
          <td>{{ formatDate(session.startedAt) }}</td>
          <td>{{ session.submittedAt ? formatDate(session.submittedAt) : '-' }}</td>
          <td>{{ calculateProgress(session) }}%</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
```

### 6.2 Report Page

Convert from `doc/html/admin/report-responden.html`:

```vue
<!-- src/views/admin/ReportView.vue -->
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const report = ref({})

async function fetchReport() {
  const response = await api.get('/admin/reports')
  report.value = response.data.data
}

async function exportExcel() {
  const response = await api.post('/admin/reports/export-excel', {}, { responseType: 'blob' })
  downloadBlob(response.data, 'evaluasi-report.xlsx')
}

async function exportPdf(sessionId: number) {
  const response = await api.post('/admin/reports/export-pdf', { sessionId }, { responseType: 'blob' })
  downloadBlob(response.data, 'evaluasi-report.pdf')
}

onMounted(() => fetchReport())
</script>
```

---

## 7. Routes

```php
// routes/api.php
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Monitoring
    Route::get('/sessions', [MonitoringController::class, 'index']);
    Route::get('/sessions/{sessionId}', [MonitoringController::class, 'show']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports/export-excel', [ReportController::class, 'exportExcel']);
    Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf']);
});
```

---

**Lanjut ke:** [09-MODULE-SETTINGS](09-MODULE-SETTINGS.md)
