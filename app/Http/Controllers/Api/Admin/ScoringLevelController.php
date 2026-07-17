<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScoringLevel;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScoringLevelController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/scoring-levels
     * List all scoring levels with pagination.
     */
    public function index(Request $request)
    {
        $query = ScoringLevel::query();

        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $limit = min($request->get('limit', 15), 100);

        $scoringLevels = $query->orderBy('value', 'asc')
            ->paginate($limit);

        return $this->listResponse($scoringLevels, 'Scoring levels retrieved successfully');
    }

    /**
     * POST /api/v1/admin/scoring-levels
     * Create a new scoring level.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:36',
            'value' => 'required|integer|min:1|max:7|unique:scoring_level,value',
            'is_active' => 'required|boolean',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['is_active'] = $data['is_active'] ? 1 : 0;

        $scoringLevel = ScoringLevel::create($data);

        return $this->successResponse($scoringLevel, 'Scoring level created successfully', 201);
    }

    /**
     * GET /api/v1/admin/scoring-levels/{id}
     * Get a single scoring level.
     */
    public function show($id)
    {
        $scoringLevel = ScoringLevel::find($id);

        if (!$scoringLevel) {
            return $this->errorResponse('Scoring level not found', 404);
        }

        return $this->successResponse($scoringLevel, 'Scoring level retrieved successfully');
    }

    /**
     * PUT /api/v1/admin/scoring-levels/{id}
     * Update a scoring level.
     */
    public function update(Request $request, $id)
    {
        $scoringLevel = ScoringLevel::find($id);

        if (!$scoringLevel) {
            return $this->errorResponse('Scoring level not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:36',
            'value' => 'required|integer|min:1|max:7|unique:scoring_level,value,' . $id,
            'is_active' => 'required|boolean',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();
        $data['is_active'] = $data['is_active'] ? 1 : 0;

        $scoringLevel->update($data);

        return $this->successResponse($scoringLevel, 'Scoring level updated successfully');
    }

    /**
     * DELETE /api/v1/admin/scoring-levels/{id}
     * Delete a scoring level.
     */
    public function destroy($id)
    {
        $scoringLevel = ScoringLevel::find($id);

        if (!$scoringLevel) {
            return $this->errorResponse('Scoring level not found', 404);
        }

        $scoringLevel->delete();

        return $this->successResponse(null, 'Scoring level deleted successfully');
    }
}
