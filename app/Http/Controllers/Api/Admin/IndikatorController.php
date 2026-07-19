<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndicatorResource;
use App\Models\Indicator;
use App\Models\SubComponent;
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
        $query = Indicator::with('subComponent.component.questionnaire');

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by sub-component
        if ($request->has('subComponentId') && $request->subComponentId) {
            $query->where('sub_component_id', $request->subComponentId);
        }

        $limit = min($request->get('limit', 10), 100);
        $indicators = $query->orderBy('order_number', 'asc')
            ->paginate($limit);

        // Get breadcrumb data
        $breadCrumbList = null;
        if ($indicators->count() != 0) {
            $firstSubComponent = $indicators->first()->subComponent;
            $breadCrumbList = [
                'questionnaire' => $firstSubComponent->component->questionnaire ?? null,
                'component' => $firstSubComponent->component ?? null,
                'subComponent' => $firstSubComponent,
            ];
        } else {
            $subComponent = SubComponent::with('component.questionnaire')->find($request->subComponentId);
            $breadCrumbList = [
                'questionnaire' => $subComponent->component->questionnaire ?? null,
                'component' => $subComponent->component ?? null,
                'subComponent' => $subComponent,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Indicators retrieved successfully',
            'data' => [
                'breadCrumbList' => $breadCrumbList,
                'contents' => IndicatorResource::collection($indicators),
                'meta' => [
                    'page' => $indicators->currentPage(),
                    'limit' => $indicators->perPage(),
                    'total' => $indicators->total(),
                ],
            ],
        ]);
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
            'isActive' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'sub_component_id' => $data['subComponentId'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['isActive'] ?? 1,
        ];

        // Auto-generate order_number if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Indicator::where('sub_component_id', $dbData['sub_component_id'])
                ->max('order_number');
            $dbData['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $dbData['order_number'] = $data['orderNumber'];
        }

        $indicator = Indicator::create($dbData);

        return $this->successResponse(
            new IndicatorResource($indicator->load('subComponent')),
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

        return $this->successResponse(
            new IndicatorResource($indicator),
            'Indicator retrieved successfully'
        );
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
            'isActive' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'sub_component_id' => $data['subComponentId'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['isActive'] ?? $indicator->is_active,
        ];

        // Auto-generate order_number if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Indicator::where('sub_component_id', $dbData['sub_component_id'])
                ->where('id', '!=', $id)
                ->max('order_number');
            $dbData['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $dbData['order_number'] = $data['orderNumber'];
        }

        $indicator->update($dbData);

        return $this->successResponse(
            new IndicatorResource($indicator->load('subComponent')),
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
