<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecommendationResource;
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

        // Search by recommendation_text
        if ($request->has('search') && $request->search) {
            $query->where('recommendation_text', 'like', '%' . $request->search . '%');
        }

        // Filter by indicator (request key camelCase, DB column snake_case)
        if ($request->has('indicatorId') && $request->indicatorId) {
            $query->where('indicator_id', $request->indicatorId);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $limit = min($request->get('limit', 10), 100);

        $recommendations = $query->orderBy('min_score', 'asc')
            ->paginate($limit);

        return $this->listResponse(
            RecommendationResource::collection($recommendations),
            'Recommendations retrieved successfully',
            $recommendations
        );
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

        $data = $validator->validated();

        // Map camelCase request keys to snake_case DB columns
        $recommendation = Recommendation::create([
            'indicator_id' => $data['indicatorId'],
            'min_score' => $data['minScore'],
            'max_score' => $data['maxScore'],
            'category' => $data['category'],
            'recommendation_text' => $data['recommendationText'],
        ]);

        return $this->successResponse(
            new RecommendationResource($recommendation->load('indicator')),
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

        return $this->successResponse(
            new RecommendationResource($recommendation),
            'Recommendation retrieved successfully'
        );
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

        $data = $validator->validated();

        // Map camelCase request keys to snake_case DB columns
        $recommendation->update([
            'indicator_id' => $data['indicatorId'],
            'min_score' => $data['minScore'],
            'max_score' => $data['maxScore'],
            'category' => $data['category'],
            'recommendation_text' => $data['recommendationText'],
        ]);

        return $this->successResponse(
            new RecommendationResource($recommendation->load('indicator')),
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
