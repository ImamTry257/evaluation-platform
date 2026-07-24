<?php

namespace App\Http\Controllers\Api\Admin\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\QuestionDetailResource;
use App\Http\Resources\V2\QuestionListResource;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PertanyaanV2Controller extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v2/admin/questions/tree
     * Return cascade hierarchy for filter dropdowns.
     */
    public function tree()
    {
        $data = Questionnaire::with([
            'evaluationPeriod',
            'components.subComponents.indicators' => function ($q) {
                $q->orderBy('name');
            },
        ])
            ->where('status', 'published')
            ->orderBy('title')
            ->get()
            ->map(fn($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'period' => $q->evaluationPeriod?->name,
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

    /**
     * GET /api/v2/admin/questions
     * List questions with cascade filter, search, sort, and pagination.
     */
    public function index(Request $request)
    {
        $query = Question::with('indicator.subComponent.component.questionnaire.evaluationPeriod');

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

    /**
     * POST /api/v2/admin/questions
     * Create a new question.
     */
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

        // Auto-generate order_number if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Question::where('indicator_id', $dbData['indicator_id'])
                ->max('order_number');
            $dbData['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $dbData['order_number'] = $data['orderNumber'];
        }

        $question = Question::create($dbData);

        return $this->successResponse(
            new QuestionDetailResource($question->load('indicator.subComponent.component.questionnaire.evaluationPeriod')),
            'Question created successfully',
            201
        );
    }

    /**
     * GET /api/v2/admin/questions/{id}
     * Get a single question detail.
     */
    public function show($id)
    {
        $question = Question::with('indicator.subComponent.component.questionnaire.evaluationPeriod')->find($id);

        if (!$question) {
            return $this->errorResponse('Question not found', 404);
        }

        return $this->successResponse(
            new QuestionDetailResource($question),
            'Question retrieved successfully'
        );
    }

    /**
     * PUT /api/v2/admin/questions/{id}
     * Update a question.
     */
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
            new QuestionDetailResource($question->fresh()->load('indicator.subComponent.component.questionnaire.evaluationPeriod')),
            'Question updated successfully'
        );
    }

    /**
     * DELETE /api/v2/admin/questions/{id}
     * Soft delete a question.
     */
    public function destroy($id)
    {
        $question = Question::find($id);

        if (!$question) {
            return $this->errorResponse('Question not found', 404);
        }

        $question->delete(); // Soft delete (sets deleted_at)

        return $this->successResponse(null, 'Question deleted successfully');
    }
}
