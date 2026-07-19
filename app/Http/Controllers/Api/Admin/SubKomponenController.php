<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubComponentResource;
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
            $firstSubComponent = $subComponents->first();
            $firstComponent = $firstSubComponent->component;
            if (!$firstComponent && $firstSubComponent->component_id) {
                $firstComponent = Component::with('questionnaire')->withTrashed()->find($firstSubComponent->component_id);
            }
            $breadCrumbList = [
                'questionnaire' => $firstComponent->questionnaire ?? null,
                'component' => $firstComponent,
            ];
        } elseif ($request->has('componentId') && $request->componentId) {
            $component = Component::with('questionnaire')->withTrashed()->find($request->componentId);
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
                'contents' => SubComponentResource::collection($subComponents),
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
            'isActive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'component_id' => $data['componentId'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['isActive'] ?? 1,
        ];

        // Auto-generate order_number if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = SubComponent::where('component_id', $dbData['component_id'])
                ->max('order_number');
            $dbData['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $dbData['order_number'] = $data['orderNumber'];
        }

        $subComponent = SubComponent::create($dbData);

        return $this->successResponse(
            new SubComponentResource($subComponent->load('component')),
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

        return $this->successResponse(new SubComponentResource($subComponent), 'Sub-component retrieved successfully');
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
            'isActive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'component_id' => $data['componentId'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['isActive'] ?? $subComponent->is_active,
        ];

        // Auto-generate order_number if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = SubComponent::where('component_id', $dbData['component_id'])
                ->where('id', '!=', $id)
                ->max('order_number');
            $dbData['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $dbData['order_number'] = $data['orderNumber'];
        }

        $subComponent->update($dbData);

        return $this->successResponse(
            new SubComponentResource($subComponent->load('component')),
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
