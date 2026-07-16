<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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
        $query = Question::with('indicator');

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

        return $this->listResponse($questions, 'Questions retrieved successfully');
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
            'orderNumber' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $question = Question::create($validator->validated());

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
            'orderNumber' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $question->update($validator->validated());

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
