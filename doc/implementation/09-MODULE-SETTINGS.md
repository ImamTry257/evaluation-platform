# Module: Settings
## Implementasi Pengaturan Sistem

**Referensi:**
- [API Specification - Settings Endpoints](../API-SPECIFICATION.md)
- [Business Process - Proses 9](../BUSINESS-PROCESS.md)

---

## 1. Overview

Module ini menangani pengaturan umum aplikasi:
1. Periode Aktif
2. Durasi Evaluasi
3. Auto-save Interval
4. Allow Resume
5. Timeout

---

## 2. API Endpoints

| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| GET | `/admin/settings` | Get all settings | Admin |
| PUT | `/admin/settings` | Update settings | Admin |

---

## 3. Backend Implementation

### 3.1 SettingController

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use HasApiResponse;

    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return $this->successResponse($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'activePeriodId' => 'nullable|exists:evaluation_periods,id',
            'evaluationDuration' => 'required|integer|min:1',
            'autoSaveInterval' => 'required|integer|min:5',
            'allowResume' => 'required|boolean',
            'timeoutMinutes' => 'required|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // If activePeriodId changed, update the period's isActive flag
        if (isset($validated['activePeriodId'])) {
            $this->updateActivePeriod($validated['activePeriodId']);
        }

        return $this->successResponse(null, 'Settings updated');
    }

    private function updateActivePeriod($periodId)
    {
        // Deactivate all periods
        \App\Models\EvaluationPeriod::where('isActive', true)
            ->update(['isActive' => false]);

        // Activate selected period
        if ($periodId) {
            \App\Models\EvaluationPeriod::where('id', $periodId)
                ->update(['isActive' => true]);
        }
    }
}
```

### 3.2 Setting Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    // Helper methods
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }

    // Typed getters
    public static function getInt($key, $default = 0)
    {
        return (int) static::get($key, $default);
    }

    public static function getBoolean($key, $default = false)
    {
        $value = static::get($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
```

---

## 4. Settings List

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `activePeriodId` | integer | null | ID periode evaluasi yang aktif |
| `evaluationDuration` | integer | 60 | Durasi evaluasi dalam menit |
| `autoSaveInterval` | integer | 30 | Interval auto save dalam detik |
| `allowResume` | boolean | true | Izinkan resume sesi terputus |
| `timeoutMinutes` | integer | 5 | Timeout sebelum sesi dianggap selesai |

---

## 5. Frontend Implementation

### 5.1 Settings Page

Convert from `doc/html/admin/setting.html`:

```vue
<!-- src/views/admin/SettingView.vue -->
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const settings = ref({
  activePeriodId: null,
  evaluationDuration: 60,
  autoSaveInterval: 30,
  allowResume: true,
  timeoutMinutes: 5,
})

const periods = ref([])
const isLoading = ref(false)
const successMessage = ref('')

async function fetchSettings() {
  const response = await api.get('/admin/settings')
  settings.value = {
    activePeriodId: response.data.data.activePeriodId?.value || null,
    evaluationDuration: Number(response.data.data.evaluationDuration?.value) || 60,
    autoSaveInterval: Number(response.data.data.autoSaveInterval?.value) || 30,
    allowResume: response.data.data.allowResume?.value === 'true',
    timeoutMinutes: Number(response.data.data.timeoutMinutes?.value) || 5,
  }
}

async function fetchPeriods() {
  const response = await api.get('/admin/periods')
  periods.value = response.data.data.data
}

async function saveSettings() {
  isLoading.value = true
  try {
    await api.put('/admin/settings', settings.value)
    successMessage.value = 'Pengaturan berhasil disimpan!'
    setTimeout(() => successMessage.value = '', 3000)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchSettings()
  fetchPeriods()
})
</script>

<template>
  <div class="p-6 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Pengaturan Sistem</h1>

    <!-- Success Message -->
    <div v-if="successMessage" class="bg-green-100 text-green-700 p-4 rounded mb-4">
      {{ successMessage }}
    </div>

    <form @submit.prevent="saveSettings" class="space-y-6">
      <!-- Active Period -->
      <div>
        <label class="block font-medium mb-2">Periode Aktif</label>
        <select v-model="settings.activePeriodId" class="w-full input">
          <option :value="null">Tidak Ada Periode Aktif</option>
          <option v-for="period in periods" :key="period.id" :value="period.id">
            {{ period.name }}
          </option>
        </select>
        <p class="text-sm text-gray-500 mt-1">
          Periode aktif menentukan kuesioner yang tersedia untuk responden
        </p>
      </div>

      <!-- Evaluation Duration -->
      <div>
        <label class="block font-medium mb-2">Durasi Evaluasi (menit)</label>
        <input v-model.number="settings.evaluationDuration" type="number" min="1" class="w-full input" />
      </div>

      <!-- Auto-save Interval -->
      <div>
        <label class="block font-medium mb-2">Interval Auto Save (detik)</label>
        <input v-model.number="settings.autoSaveInterval" type="number" min="5" class="w-full input" />
      </div>

      <!-- Allow Resume -->
      <div>
        <label class="flex items-center gap-2">
          <input v-model="settings.allowResume" type="checkbox" class="checkbox" />
          <span>Izinkan Resume Sesi Terputus</span>
        </label>
        <p class="text-sm text-gray-500 mt-1">
          Jika dinonaktifkan, sesi yang terputus tidak dapat dilanjutkan
        </p>
      </div>

      <!-- Timeout -->
      <div>
        <label class="block font-medium mb-2">Timeout (menit)</label>
        <input v-model.number="settings.timeoutMinutes" type="number" min="1" class="w-full input" />
        <p class="text-sm text-gray-500 mt-1">
          Waktu tunggu sebelum sesi dianggap timeout
        </p>
      </div>

      <!-- Submit -->
      <button type="submit" :disabled="isLoading" class="w-full btn-primary">
        {{ isLoading ? 'Menyimpan...' : 'Simpan Pengaturan' }}
      </button>
    </form>
  </div>
</template>
```

---

## 6. Routes

```php
// routes/api.php
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);
});
```

---

## 7. Usage in Other Modules

```php
// In EvaluationService
$duration = Setting::getInt('evaluationDuration', 60);
$allowResume = Setting::getBoolean('allowResume', true);
$timeout = Setting::getInt('timeoutMinutes', 5);
```

---

**Selesai!** Semua module sudah terdokumentasi.
