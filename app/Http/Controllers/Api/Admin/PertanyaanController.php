<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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

        // Search by questionText
        if ($request->has('search') && $request->search) {
            $query->where('questionText', 'like', '%' . $request->search . '%');
        }

        // Filter by indicator
        if ($request->has('indicatorId') && $request->indicatorId) {
            $query->where('indicatorId', $request->indicatorId);
        }

        $limit = min($request->get('limit', 10), 100);
        $questions = $query->orderBy('orderNumber', 'asc')
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
                'contents' => $questions->items(),
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
            'is_active' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Question::where('indicatorId', $data['indicatorId'])
                ->max('orderNumber');
            $data['orderNumber'] = ($maxOrder ?? 0) + 1;
        }

        // Set default is_active if not provided
        if (!isset($data['is_active'])) {
            $data['is_active'] = 1;
        }

        $question = Question::create($data);

        return $this->successResponse(
            $question->load('indicator'),
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

        return $this->successResponse($question, 'Question retrieved successfully');
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
            'is_active' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Question::where('indicatorId', $data['indicatorId'])
                ->where('id', '!=', $id)
                ->max('orderNumber');
            $data['orderNumber'] = ($maxOrder ?? 0) + 1;
        }

        $question->update($data);

        return $this->successResponse(
            $question->load('indicator'),
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
