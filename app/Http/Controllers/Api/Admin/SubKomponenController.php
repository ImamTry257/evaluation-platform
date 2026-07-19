<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\SubComponent;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubKomponenController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/sub-components
     * List all sub-components with pagination.
     */
    public function index(Request $request)
    {
        $query = SubComponent::with('component.questionnaire');

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by component
        if ($request->has('componentId') && $request->componentId) {
            $query->where('component_id', $request->componentId);
        }

        $limit = min($request->get('limit', 10), 100);
        $subComponents = $query->orderBy('order_number', 'asc')
            ->paginate($limit);

        // Get breadcrumb data
        $breadCrumbList = null;
        if ($subComponents->count() != 0) {
            $firstComponent = $subComponents->first()->component;
            $breadCrumbList = [
                'questionnaire' => $firstComponent->questionnaire ?? null,
                'component' => $firstComponent,
            ];
        }else {
            $component = Component::with('questionnaire')->find($request->componentId);
            $breadCrumbList = [
                'questionnaire' => $component->questionnaire ?? null,
                'component' => $component,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Sub-components retrieved successfully',
            'data' => [
                'breadCrumbList' => $breadCrumbList,
                'contents' => $subComponents->items(),
                'meta' => [
                    'page' => $subComponents->currentPage(),
                    'limit' => $subComponents->perPage(),
                    'total' => $subComponents->total(),
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/sub-components
     * Create a new sub-component.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'componentId' => 'required|exists:components,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'orderNumber' => 'nullable|integer|min:0',
            'is_active' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = SubComponent::where('component_id', $data['componentId'])
                ->max('order_number');
            $data['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $data['order_number'] = $data['orderNumber'];
            unset($data['orderNumber']);
        }

        // Map camelCase to snake_case for database
        $dbData = [
            'component_id' => $data['componentId'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'order_number' => $data['order_number'],
            'is_active' => $data['is_active'] ?? 1,
        ];

        $subComponent = SubComponent::create($dbData);

        return $this->successResponse(
            $subComponent->load('component'),
            'Sub-component created successfully',
            201
        );
    }

    /**
     * GET /api/v1/admin/sub-components/{id}
     * Get a single sub-component.
     */
    public function show($id)
    {
        $subComponent = SubComponent::with('component')->find($id);

        if (!$subComponent) {
            return $this->errorResponse('Sub-component not found', 404);
        }

        return $this->successResponse($subComponent, 'Sub-component retrieved successfully');
    }

    /**
     * PUT /api/v1/admin/sub-components/{id}
     * Update a sub-component.
     */
    public function update(Request $request, $id)
    {
        $subComponent = SubComponent::find($id);

        if (!$subComponent) {
            return $this->errorResponse('Sub-component not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'componentId' => 'required|exists:components,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'orderNumber' => 'nullable|integer|min:0',
            'is_active' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = SubComponent::where('component_id', $data['componentId'])
                ->where('id', '!=', $id)
                ->max('order_number');
            $data['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $data['order_number'] = $data['orderNumber'];
            unset($data['orderNumber']);
        }

        // Map camelCase to snake_case for database
        $dbData = [
            'component_id' => $data['componentId'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'order_number' => $data['order_number'],
            'is_active' => $data['is_active'] ?? 1,
        ];

        $subComponent->update($dbData);

        return $this->successResponse(
            $subComponent->load('component'),
            'Sub-component updated successfully'
        );
    }

    /**
     * DELETE /api/v1/admin/sub-components/{id}
     * Delete a sub-component.
     */
    public function destroy($id)
    {
        $subComponent = SubComponent::find($id);

        if (!$subComponent) {
            return $this->errorResponse('Sub-component not found', 404);
        }

        $subComponent->delete();

        return $this->successResponse(null, 'Sub-component deleted successfully');
    }
}
