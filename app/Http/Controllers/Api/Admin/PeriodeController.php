<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EvaluationPeriodResource;
use App\Models\EvaluationPeriod;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PeriodeController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/periods
     * List all periods with pagination.
     */
    public function index(Request $request)
    {
        $query = EvaluationPeriod::query();

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status (request key camelCase, DB column snake_case)
        if ($request->has('isActive')) {
            $query->where('is_active', $request->boolean('isActive'));
        }

        $limit = min($request->get('limit', 10), 100);

        $periods = $query->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->listResponse(
            EvaluationPeriodResource::collection($periods),
            'Periods retrieved successfully',
            $periods
        );
    }

    /**
     * POST /api/v1/admin/periods
     * Create a new period.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'isActive' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase request keys to snake_case DB columns
        $period = EvaluationPeriod::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'is_active' => $data['isActive'] ?? true,
        ]);

        return $this->successResponse(
            new EvaluationPeriodResource($period),
            'Period created successfully',
            201
        );
    }

    /**
     * GET /api/v1/admin/periods/{id}
     * Get a single period.
     */
    public function show($id)
    {
        $period = EvaluationPeriod::find($id);

        if (!$period) {
            return $this->errorResponse('Period not found', 404);
        }

        return $this->successResponse(
            new EvaluationPeriodResource($period),
            'Period retrieved successfully'
        );
    }

    /**
     * PUT /api/v1/admin/periods/{id}
     * Update a period.
     */
    public function update(Request $request, $id)
    {
        $period = EvaluationPeriod::find($id);

        if (!$period) {
            return $this->errorResponse('Period not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'isActive' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase request keys to snake_case DB columns
        $period->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'is_active' => $data['isActive'] ?? true,
        ]);

        return $this->successResponse(
            new EvaluationPeriodResource($period),
            'Period updated successfully'
        );
    }

    /**
     * DELETE /api/v1/admin/periods/{id}
     * Delete a period.
     */
    public function destroy($id)
    {
        $period = EvaluationPeriod::find($id);

        if (!$period) {
            return $this->errorResponse('Period not found', 404);
        }

        $period->delete();

        return $this->successResponse(null, 'Period deleted successfully');
    }
}
