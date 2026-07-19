<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionnaireResource;
use App\Models\Questionnaire;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KuesionerController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/questionnaires
     * List all questionnaires with pagination.
     */
    public function index(Request $request)
    {
        $query = Questionnaire::with('evaluationPeriod');

        // Search by title
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by evaluation period
        if ($request->has('evaluationPeriodId') && $request->evaluationPeriodId) {
            $query->where('evaluation_period_id', $request->evaluationPeriodId);
        }

        $limit = min($request->get('limit', 10), 100);

        $questionnaires = $query->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'status' => true,
            'message' => 'Questionnaires retrieved successfully',
            'data' => [
                'contents' => QuestionnaireResource::collection($questionnaires),
                'meta' => [
                    'page' => $questionnaires->currentPage(),
                    'limit' => $questionnaires->perPage(),
                    'total' => $questionnaires->total(),
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/questionnaires
     * Create a new questionnaire.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'evaluationPeriodId' => 'required|exists:evaluation_periods,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'durationMinutes' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,closed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'evaluation_period_id' => $data['evaluationPeriodId'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['durationMinutes'],
            'status' => $data['status'],
        ];

        $questionnaire = Questionnaire::create($dbData);

        return $this->successResponse(
            new QuestionnaireResource($questionnaire->load('evaluationPeriod')),
            'Questionnaire created successfully',
            201
        );
    }

    /**
     * GET /api/v1/admin/questionnaires/{id}
     * Get a single questionnaire.
     */
    public function show($id)
    {
        $questionnaire = Questionnaire::with('evaluationPeriod')->find($id);

        if (!$questionnaire) {
            return $this->errorResponse('Questionnaire not found', 404);
        }

        return $this->successResponse(
            new QuestionnaireResource($questionnaire),
            'Questionnaire retrieved successfully'
        );
    }

    /**
     * PUT /api/v1/admin/questionnaires/{id}
     * Update a questionnaire.
     */
    public function update(Request $request, $id)
    {
        $questionnaire = Questionnaire::find($id);

        if (!$questionnaire) {
            return $this->errorResponse('Questionnaire not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'evaluationPeriodId' => 'required|exists:evaluation_periods,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'durationMinutes' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,closed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'evaluation_period_id' => $data['evaluationPeriodId'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['durationMinutes'],
            'status' => $data['status'],
        ];

        $questionnaire->update($dbData);

        return $this->successResponse(
            new QuestionnaireResource($questionnaire->load('evaluationPeriod')),
            'Questionnaire updated successfully'
        );
    }

    /**
     * DELETE /api/v1/admin/questionnaires/{id}
     * Soft delete a questionnaire.
     */
    public function destroy($id)
    {
        $questionnaire = Questionnaire::find($id);

        if (!$questionnaire) {
            return $this->errorResponse('Questionnaire not found', 404);
        }

        $questionnaire->delete(); // Soft delete

        return $this->successResponse(null, 'Questionnaire deleted successfully');
    }

    /**
     * POST /api/v1/admin/questionnaires/{id}/publish
     * Publish a questionnaire (change status from draft to published).
     */
    public function publish($id)
    {
        $questionnaire = Questionnaire::find($id);

        if (!$questionnaire) {
            return $this->errorResponse('Questionnaire not found', 404);
        }

        if ($questionnaire->status === 'published') {
            return $this->errorResponse('Questionnaire is already published', 422);
        }

        if ($questionnaire->status === 'closed') {
            return $this->errorResponse('Cannot publish a closed questionnaire', 422);
        }

        // Validate: must have at least one component with questions
        $hasContent = $questionnaire->components()
            ->whereHas('subComponents.indicators.questions')
            ->exists();

        if (!$hasContent) {
            return $this->errorResponse('Cannot publish questionnaire without questions', 422, [
                'message' => 'Questionnaire must have at least one component with questions',
            ]);
        }

        $questionnaire->update(['status' => 'published']);

        return $this->successResponse(
            new QuestionnaireResource($questionnaire->load('evaluationPeriod')),
            'Questionnaire published successfully'
        );
    }
}
