<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RekomendasiController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/recommendations
     * List all recommendations with pagination.
     */
    public function index(Request $request)
    {
        $query = Recommendation::with('indicator');

        // Search by recommendationText
        if ($request->has('search') && $request->search) {
            $query->where('recommendationText', 'like', '%' . $request->search . '%');
        }

        // Filter by indicator
        if ($request->has('indicatorId') && $request->indicatorId) {
            $query->where('indicatorId', $request->indicatorId);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $limit = min($request->get('limit', 10), 100);

        $recommendations = $query->orderBy('minScore', 'asc')
            ->paginate($limit);

        return $this->listResponse($recommendations, 'Recommendations retrieved successfully');
    }

    /**
     * POST /api/v1/admin/recommendations
     * Create a new recommendation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'indicatorId' => 'required|exists:indicators,id',
            'minScore' => 'required|numeric|min:0',
            'maxScore' => 'required|numeric|min:0|gte:minScore',
            'category' => 'required|string|size:1|in:A,B,C,D,E',
            'recommendationText' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $recommendation = Recommendation::create($validator->validated());

        return $this->successResponse(
            $recommendation->load('indicator'),
            'Recommendation created successfully',
            201
        );
    }

    /**
     * GET /api/v1/admin/recommendations/{id}
     * Get a single recommendation.
     */
    public function show($id)
    {
        $recommendation = Recommendation::with('indicator')->find($id);

        if (!$recommendation) {
            return $this->errorResponse('Recommendation not found', 404);
        }

        return $this->successResponse($recommendation, 'Recommendation retrieved successfully');
    }

    /**
     * PUT /api/v1/admin/recommendations/{id}
     * Update a recommendation.
     */
    public function update(Request $request, $id)
    {
        $recommendation = Recommendation::find($id);

        if (!$recommendation) {
            return $this->errorResponse('Recommendation not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'indicatorId' => 'required|exists:indicators,id',
            'minScore' => 'required|numeric|min:0',
            'maxScore' => 'required|numeric|min:0|gte:minScore',
            'category' => 'required|string|size:1|in:A,B,C,D,E',
            'recommendationText' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $recommendation->update($validator->validated());

        return $this->successResponse(
            $recommendation->load('indicator'),
            'Recommendation updated successfully'
        );
    }

    /**
     * DELETE /api/v1/admin/recommendations/{id}
     * Delete a recommendation.
     */
    public function destroy($id)
    {
        $recommendation = Recommendation::find($id);

        if (!$recommendation) {
            return $this->errorResponse('Recommendation not found', 404);
        }

        $recommendation->delete();

        return $this->successResponse(null, 'Recommendation deleted successfully');
    }
}
