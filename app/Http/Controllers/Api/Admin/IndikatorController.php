<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IndikatorController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/indicators
     * List all indicators with pagination.
     */
    public function index(Request $request)
    {
        $query = Indicator::with('subComponent');

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by subComponent
        if ($request->has('subComponentId') && $request->subComponentId) {
            $query->where('subComponentId', $request->subComponentId);
        }

        $limit = min($request->get('limit', 10), 100);
        $indicators = $query->orderBy('orderNumber', 'asc')
            ->paginate($limit);

        return $this->listResponse($indicators, 'Indicators retrieved successfully');
    }

    /**
     * POST /api/v1/admin/indicators
     * Create a new indicator.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subComponentId' => 'required|exists:sub_components,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'orderNumber' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Indicator::where('subComponentId', $data['subComponentId'])
                ->max('orderNumber');
            $data['orderNumber'] = ($maxOrder ?? 0) + 1;
        }

        $indicator = Indicator::create($data);

        return $this->successResponse(
            $indicator->load('subComponent'),
            'Indicator created successfully',
            201
        );
    }

    /**
     * GET /api/v1/admin/indicators/{id}
     * Get a single indicator.
     */
    public function show($id)
    {
        $indicator = Indicator::with(['subComponent', 'questions', 'recommendations'])->find($id);

        if (!$indicator) {
            return $this->errorResponse('Indicator not found', 404);
        }

        return $this->successResponse($indicator, 'Indicator retrieved successfully');
    }

    /**
     * PUT /api/v1/admin/indicators/{id}
     * Update an indicator.
     */
    public function update(Request $request, $id)
    {
        $indicator = Indicator::find($id);

        if (!$indicator) {
            return $this->errorResponse('Indicator not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'subComponentId' => 'required|exists:sub_components,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'orderNumber' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Indicator::where('subComponentId', $data['subComponentId'])
                ->where('id', '!=', $id)
                ->max('orderNumber');
            $data['orderNumber'] = ($maxOrder ?? 0) + 1;
        }

        $indicator->update($data);

        return $this->successResponse(
            $indicator->load('subComponent'),
            'Indicator updated successfully'
        );
    }

    /**
     * DELETE /api/v1/admin/indicators/{id}
     * Delete an indicator.
     */
    public function destroy($id)
    {
        $indicator = Indicator::find($id);

        if (!$indicator) {
            return $this->errorResponse('Indicator not found', 404);
        }

        $indicator->delete();

        return $this->successResponse(null, 'Indicator deleted successfully');
    }
}
