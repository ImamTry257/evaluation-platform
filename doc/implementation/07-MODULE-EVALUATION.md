# Module: Evaluation Engine
## Implementasi Sesi Evaluasi, Pengisian Angket, dan Scoring

**Referensi:**
- [API Specification - Evaluation Endpoints](../API-SPECIFICATION.md)
- [Business Process - Proses 10-16](../BUSINESS-PROCESS.md)
- ADR-001 (Evaluation Logic)

---

## 1. Overview

Evaluation Engine adalah core module yang menangani:
1. Start evaluation session
2. Save answers (with auto-save)
3. Resume interrupted session
4. Submit evaluation
5. Calculate scores (Scoring Engine)
6. Determine categories (Category Engine)
7. Map recommendations (Recommendation Engine)

---

## 2. API Endpoints

| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| POST | `/evaluations/start` | Mulai sesi baru | Respondent |
| GET | `/evaluations/{sessionId}` | Get session data | Respondent |
| POST | `/evaluations/{sessionId}/answers` | Save jawaban | Respondent |
| POST | `/evaluations/{sessionId}/submit` | Submit evaluasi | Respondent |
| GET | `/evaluations/{sessionId}/results` | Get hasil | Respondent |

---

## 3. Evaluation Flow

```
┌─────────────────────────────────────────────────────────┐
│                    EVALUATION FLOW                       │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  1. Start Session                                       │
│     ├── Create ResponseSession                          │
│     ├── Set startedAt, remainingSeconds                 │
│     └── Return session + questions                      │
│                                                         │
│  2. Fill Questionnaire                                  │
│     ├── Show questions per indicator                    │
│     ├── Save answers (auto-save every 30s)              │
│     └── Update remainingSeconds                         │
│                                                         │
│  3. Submit                                              │
│     ├── Validate all questions answered                 │
│     ├── Change status to "submitted"                    │
│     ├── Run Scoring Engine                              │
│     ├── Run Category Engine                             │
│     ├── Run Recommendation Engine                       │
│     └── Create EvaluationResult + Details               │
│                                                         │
│  4. View Results                                        │
│     ├── Show overall score, percentage, category        │
│     ├── Show per-indicator results                      │
│     └── Show recommendations                            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 4. Backend Implementation

### 4.1 EvaluationController

```php
<?php

namespace App\Http\Controllers\Api\Respondent;

use App\Http\Controllers\Controller;
use App\Services\Evaluation\EvaluationService;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    use HasApiResponse;

    protected $service;

    public function __construct(EvaluationService $service)
    {
        $this->service = $service;
    }

    public function start(Request $request)
    {
        $session = $this->service->startSession($request->user());
        return $this->successResponse($session, 'Session started', 201);
    }

    public function show($sessionId, Request $request)
    {
        $session = $this->service->getSession($sessionId, $request->user());
        return $this->successResponse($session);
    }

    public function saveAnswer(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'questionId' => 'required|exists:questions,id',
            'score' => 'required|integer|min:1|max:7',
        ]);

        $answer = $this->service->saveAnswer(
            $sessionId,
            $validated['questionId'],
            $validated['score'],
            $request->user()
        );

        return $this->successResponse($answer, 'Answer saved');
    }

    public function submit($sessionId, Request $request)
    {
        $result = $this->service->submitSession($sessionId, $request->user());
        return $this->successResponse($result, 'Evaluation submitted');
    }

    public function results($sessionId, Request $request)
    {
        $results = $this->service->getResults($sessionId, $request->user());
        return $this->successResponse($results);
    }
}
```

### 4.2 EvaluationService

```php
<?php

namespace App\Services\Evaluation;

use App\Models\{ResponseSession, ResponseAnswer, Question, EvaluationResult, EvaluationResultDetail};
use App\Services\Evaluation\ScoringEngine;
use App\Services\Evaluation\CategoryEngine;
use App\Services\Evaluation\RecommendationEngine;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    protected $scoringEngine;
    protected $categoryEngine;
    protected $recommendationEngine;

    public function __construct(
        ScoringEngine $scoringEngine,
        CategoryEngine $categoryEngine,
        RecommendationEngine $recommendationEngine
    ) {
        $this->scoringEngine = $scoringEngine;
        $this->categoryEngine = $categoryEngine;
        $this->recommendationEngine = $recommendationEngine;
    }

    public function startSession($user)
    {
        // Check for existing active session
        $existingSession = ResponseSession::where('userId', $user->id)
            ->where('status', 'inProgress')
            ->first();

        if ($existingSession) {
            return $this->getSessionData($existingSession);
        }

        // Get active questionnaire
        $questionnaire = $this->getActiveQuestionnaire();

        // Create new session
        $session = ResponseSession::create([
            'userId' => $user->id,
            'questionnaireId' => $questionnaire->id,
            'status' => 'inProgress',
            'startedAt' => now(),
            'remainingSeconds' => $questionnaire->durationMinutes * 60,
        ]);

        return $this->getSessionData($session);
    }

    public function getSession($sessionId, $user)
    {
        $session = ResponseSession::where('id', $sessionId)
            ->where('userId', $user->id)
            ->firstOrFail();

        return $this->getSessionData($session);
    }

    public function saveAnswer($sessionId, $questionId, $score, $user)
    {
        $session = $this->validateSession($sessionId, $user);

        // Upsert answer
        $answer = ResponseAnswer::updateOrCreate(
            [
                'responseSessionId' => $session->id,
                'questionId' => $questionId,
            ],
            [
                'score' => $score,
            ]
        );

        // Update remaining seconds
        $this->updateRemainingSeconds($session);

        return $answer;
    }

    public function submitSession($sessionId, $user)
    {
        $session = $this->validateSession($sessionId, $user);

        // Check all questions answered
        $totalQuestions = Question::whereHas('indicator.subComponent.component.questionnaire', fn($q) => $q->where('id', $session->questionnaireId))->count();

        $answeredQuestions = ResponseAnswer::where('responseSessionId', $session->id)->count();

        if ($answeredQuestions < $totalQuestions) {
            throw new \Exception('All questions must be answered before submitting');
        }

        return DB::transaction(function () use ($session) {
            // Update session status
            $session->update([
                'status' => 'submitted',
                'submittedAt' => now(),
            ]);

            // Calculate results
            $result = $this->calculateResults($session);

            return $result;
        });
    }

    public function getResults($sessionId, $user)
    {
        $session = ResponseSession::where('id', $sessionId)
            ->where('userId', $user->id)
            ->where('status', 'submitted')
            ->firstOrFail();

        return EvaluationResult::where('responseSessionId', $session->id)
            ->with('details.indicator')
            ->first();
    }

    private function calculateResults($session)
    {
        // 1. Calculate scores per indicator
        $indicatorScores = $this->scoringEngine->calculateIndicatorScores($session);

        // 2. Determine categories
        $indicatorCategories = $this->categoryEngine->determineCategories($indicatorScores);

        // 3. Map recommendations
        $recommendations = $this->recommendationEngine->mapRecommendations($indicatorCategories);

        // 4. Calculate overall score
        $overallScore = collect($indicatorScores)->sum('score');
        $maxPossibleScore = $this->getMaxPossibleScore($session);
        $overallPercentage = ($overallScore / $maxPossibleScore) * 100;
        $overallCategory = $this->categoryEngine->getCategory($overallPercentage);

        // 5. Create result
        $result = EvaluationResult::create([
            'responseSessionId' => $session->id,
            'overallScore' => $overallScore,
            'overallPercentage' => $overallPercentage,
            'overallCategory' => $overallCategory,
            'conclusion' => $this->generateConclusion($overallCategory),
        ]);

        // 6. Create result details
        foreach ($indicatorCategories as $indicatorId => $data) {
            EvaluationResultDetail::create([
                'evaluationResultId' => $result->id,
                'indicatorId' => $indicatorId,
                'score' => $data['score'],
                'percentage' => $data['percentage'],
                'category' => $data['category'],
                'recommendation' => $recommendations[$indicatorId] ?? null,
            ]);
        }

        return $result;
    }

    private function validateSession($sessionId, $user)
    {
        $session = ResponseSession::where('id', $sessionId)
            ->where('userId', $user->id)
            ->where('status', 'inProgress')
            ->firstOrFail();

        // Check if session has timed out
        if ($session->remainingSeconds <= 0) {
            $session->update(['status' => 'timeout']);
            throw new \Exception('Session has timed out');
        }

        return $session;
    }

    private function updateRemainingSeconds($session)
    {
        $elapsed = now()->diffInSeconds($session->startedAt);
        $remaining = max(0, ($session->questionnaire->durationMinutes * 60) - $elapsed);
        $session->update(['remainingSeconds' => $remaining]);
    }

    private function getActiveQuestionnaire()
    {
        return \App\Models\Questionnaire::whereHas('evaluationPeriod', fn($q) => $q->where('isActive', true))
            ->where('status', 'published')
            ->firstOrFail();
    }

    private function getSessionData($session)
    {
        return [
            'sessionId' => $session->id,
            'questionnaireId' => $session->questionnaireId,
            'status' => $session->status,
            'startedAt' => $session->startedAt,
            'remainingSeconds' => $session->remainingSeconds,
            'questions' => $this->getQuestions($session->questionnaireId),
            'answers' => $this->getAnswers($session->id),
        ];
    }

    private function getQuestions($questionnaireId)
    {
        return Question::whereHas('indicator.subComponent.component.questionnaire', fn($q) => $q->where('id', $questionnaireId))
            ->with('indicator.subComponent.component')
            ->orderBy('indicator.subComponent.component.orderNumber')
            ->orderBy('indicator.subComponent.orderNumber')
            ->orderBy('indicator.orderNumber')
            ->orderBy('questions.orderNumber')
            ->get();
    }

    private function getAnswers($sessionId)
    {
        return ResponseAnswer::where('responseSessionId', $sessionId)
            ->pluck('score', 'questionId');
    }

    private function getMaxPossibleScore($session)
    {
        return Question::whereHas('indicator.subComponent.component.questionnaire', fn($q) => $q->where('id', $session->questionnaireId))
            ->sum('weight') * 7; // Max Likert score is 7
    }

    private function generateConclusion($category)
    {
        $conclusions = [
            'A' => 'Implementasi kebijakan lingkungan sudah sangat baik.',
            'B' => 'Implementasi kebijakan lingkungan sudah baik.',
            'C' => 'Implementasi kebijakan lingkungan cukup baik.',
            'D' => 'Implementasi kebijakan lingkungan perlu perbaikan.',
            'E' => 'Implementasi kebijakan lingkungan perlu perbaikan signifikan.',
        ];

        return $conclusions[$category] ?? 'Tidak ada kesimpulan.';
    }
}
```

---

## 5. Scoring Engine

```php
<?php

namespace App\Services\Evaluation;

use App\Models\{ResponseSession, ResponseAnswer, Question};

class ScoringEngine
{
    public function calculateIndicatorScores(ResponseSession $session): array
    {
        $answers = ResponseAnswer::where('responseSessionId', $session->id)->get();

        $questions = Question::whereHas('indicator.subComponent.component.questionnaire', fn($q) => $q->where('id', $session->questionnaireId))
            ->get()
            ->keyBy('id');

        $indicatorScores = [];

        foreach ($answers as $answer) {
            $question = $questions[$answer->questionId];
            $indicatorId = $question->indicatorId;

            if (!isset($indicatorScores[$indicatorId])) {
                $indicatorScores[$indicatorId] = [
                    'indicatorId' => $indicatorId,
                    'totalWeightedScore' => 0,
                    'totalWeight' => 0,
                    'score' => 0,
                ];
            }

            $weightedScore = $answer->score * $question->weight;
            $indicatorScores[$indicatorId]['totalWeightedScore'] += $weightedScore;
            $indicatorScores[$indicatorId]['totalWeight'] += $question->weight;
        }

        // Calculate final score per indicator
        foreach ($indicatorScores as &$data) {
            $data['score'] = $data['totalWeightedScore'] / $data['totalWeight'];
        }

        return $indicatorScores;
    }
}
```

---

## 6. Category Engine

```php
<?php

namespace App\Services\Evaluation;

class CategoryEngine
{
    // Category thresholds based on research methodology
    // Using Standar Baku Ideal and Rerata Ideal formulas
    private $thresholds = [
        'A' => ['min' => 86, 'max' => 100], // Kesesuaian Sangat Tinggi
        'B' => ['min' => 71, 'max' => 85],  // Kesesuaian Tinggi
        'C' => ['min' => 56, 'max' => 70],  // Kesesuaian Sedang
        'D' => ['min' => 41, 'max' => 55],  // Kesesuaian Rendah
        'E' => ['min' => 0,  'max' => 40],  // Kesesuaian Sangat Rendah
    ];

    public function determineCategories(array $indicatorScores): array
    {
        $results = [];

        foreach ($indicatorScores as $indicatorId => $data) {
            $maxPossible = $data['totalWeight'] * 7;
            $percentage = ($data['score'] / $maxPossible) * 100;
            $category = $this->getCategory($percentage);

            $results[$indicatorId] = [
                'score' => $data['score'],
                'percentage' => $percentage,
                'category' => $category,
            ];
        }

        return $results;
    }

    public function getCategory($percentage): string
    {
        foreach ($this->thresholds as $category => $range) {
            if ($percentage >= $range['min'] && $percentage <= $range['max']) {
                return $category;
            }
        }

        return 'E'; // Default
    }
}
```

---

## 7. Recommendation Engine

```php
<?php

namespace App\Services\Evaluation;

use App\Models\Recommendation;

class RecommendationEngine
{
    public function mapRecommendations(array $indicatorCategories): array
    {
        $recommendations = [];

        foreach ($indicatorCategories as $indicatorId => $data) {
            $recommendation = Recommendation::where('indicatorId', $indicatorId)
                ->where('category', $data['category'])
                ->first();

            $recommendations[$indicatorId] = $recommendation?->recommendationText;
        }

        return $recommendations;
    }
}
```

---

## 8. Frontend Implementation

### 8.1 Evaluation Store

```typescript
// src/stores/evaluation.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useEvaluationStore = defineStore('evaluation', () => {
  const session = ref<any>(null)
  const answers = ref<Record<number, number>>({})
  const remainingSeconds = ref(0)
  const isLoading = ref(false)

  async function startSession() {
    isLoading.value = true
    try {
      const response = await api.post('/evaluations/start')
      session.value = response.data.data
      answers.value = response.data.data.answers || {}
      remainingSeconds.value = response.data.data.remainingSeconds
      return session.value
    } finally {
      isLoading.value = false
    }
  }

  async function saveAnswer(questionId: number, score: number) {
    answers.value[questionId] = score
    await api.post(`/evaluations/${session.value.sessionId}/answers`, {
      questionId,
      score,
    })
  }

  async function submitSession() {
    isLoading.value = true
    try {
      const response = await api.post(`/evaluations/${session.value.sessionId}/submit`)
      return response.data.data
    } finally {
      isLoading.value = false
    }
  }

  return { session, answers, remainingSeconds, isLoading, startSession, saveAnswer, submitSession }
})
```

### 8.2 Evaluation Form Page

Convert from `doc/html/reponden/input-angket.html`:

```vue
<!-- src/views/respondent/EvaluationFormView.vue -->
<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useEvaluationStore } from '@/stores/evaluation'

const store = useEvaluationStore()
const timer = ref<any>(null)

onMounted(async () => {
  await store.startSession()
  startTimer()
})

onUnmounted(() => {
  clearInterval(timer.value)
})

function startTimer() {
  timer.value = setInterval(() => {
    if (store.remainingSeconds > 0) {
      store.remainingSeconds--
    } else {
      clearInterval(timer.value)
      // Handle timeout
    }
  }, 1000)
}

function formatTime(seconds: number) {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

async function handleSubmit() {
  if (confirm('Yakin ingin submit evaluasi?')) {
    const result = await store.submitSession()
    // Navigate to results page
  }
}
</script>
```

---

## 9. Auto-Save

```typescript
// In EvaluationFormView.vue
let autoSaveTimer: any = null

onMounted(() => {
  autoSaveTimer = setInterval(() => {
    // Save current answers
    Object.entries(store.answers).forEach(([questionId, score]) => {
      store.saveAnswer(Number(questionId), score as number)
    })
  }, 30000) // Every 30 seconds
})

onUnmounted(() => {
  clearInterval(autoSaveTimer)
})
```

---

**Lanjut ke:** [08-MODULE-MONITORING](08-MODULE-MONITORING.md)
