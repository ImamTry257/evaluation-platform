<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Indicator;
use App\Models\Question;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PertanyaanController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/questions
     * List all questions with pagination.
     */
    public function index(Request $request)
    {
        $query = Question::with('indicator.subComponent.component.questionnaire');

        // Search by question_text
        if ($request->has('search') && $request->search) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        // Filter by indicator
        if ($request->has('indicatorId') && $request->indicatorId) {
            $query->where('indicator_id', $request->indicatorId);
        }

        $limit = min($request->get('limit', 10), 100);
        $questions = $query->orderBy('order_number', 'asc')
            ->paginate($limit);

        // Get breadcrumb data - always fetch from indicatorId
        $breadCrumbList = null;
        if ($request->has('indicatorId') && $request->indicatorId) {
            $indicator = Indicator::with('subComponent.component.questionnaire')->find($request->indicatorId);
            if ($indicator) {
                $breadCrumbList = [
                    'questionnaire' => $indicator->subComponent->component->questionnaire ?? null,
                    'component' => $indicator->subComponent->component ?? null,
                    'subComponent' => $indicator->subComponent ?? null,
                    'indicator' => $indicator,
                ];
            }
        } elseif ($questions->count() > 0) {
            $firstIndicator = $questions->first()->indicator;
            $breadCrumbList = [
                'questionnaire' => $firstIndicator->subComponent->component->questionnaire ?? null,
                'component' => $firstIndicator->subComponent->component ?? null,
                'subComponent' => $firstIndicator->subComponent ?? null,
                'indicator' => $firstIndicator,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Questions retrieved successfully',
            'data' => [
                'breadCrumbList' => $breadCrumbList,
                'contents' => QuestionResource::collection($questions),
                'meta' => [
                    'page' => $questions->currentPage(),
                    'limit' => $questions->perPage(),
                    'total' => $questions->total(),
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/questions
     * Create a new question.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'indicatorId' => 'required|exists:indicators,id',
            'questionText' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'orderNumber' => 'nullable|integer|min:0',
            'isActive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
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
            new QuestionResource($question->load('indicator')),
            'Question created successfully',
            201
        );
    }

    /**
     * GET /api/v1/admin/questions/{id}
     * Get a single question.
     */
    public function show($id)
    {
        $question = Question::with('indicator')->find($id);

        if (!$question) {
            return $this->errorResponse('Question not found', 404);
        }

        return $this->successResponse(new QuestionResource($question), 'Question retrieved successfully');
    }

    /**
     * PUT /api/v1/admin/questions/{id}
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
            'questionText' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'orderNumber' => 'nullable|integer|min:0',
            'isActive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'indicator_id' => $data['indicatorId'],
            'question_text' => $data['questionText'],
            'weight' => $data['weight'],
            'is_active' => $data['isActive'] ?? $question->is_active,
        ];

        // Auto-generate order_number if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Question::where('indicator_id', $dbData['indicator_id'])
                ->where('id', '!=', $id)
                ->max('order_number');
            $dbData['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $dbData['order_number'] = $data['orderNumber'];
        }

        $question->update($dbData);

        return $this->successResponse(
            new QuestionResource($question->load('indicator')),
            'Question updated successfully'
        );
    }

    /**
     * DELETE /api/v1/admin/questions/{id}
     * Delete a question.
     */
    public function destroy($id)
    {
        $question = Question::find($id);

        if (!$question) {
            return $this->errorResponse('Question not found', 404);
        }

        $question->delete();

        return $this->successResponse(null, 'Question deleted successfully');
    }
}
