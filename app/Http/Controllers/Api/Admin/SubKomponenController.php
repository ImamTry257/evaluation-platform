<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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
        $query = SubComponent::with('component');

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by component
        if ($request->has('componentId') && $request->componentId) {
            $query->where('componentId', $request->componentId);
        }

        $limit = min($request->get('limit', 10), 100);

        $subComponents = $query->orderBy('orderNumber', 'asc')
            ->paginate($limit);

        return $this->listResponse($subComponents, 'Sub-components retrieved successfully');
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
            'orderNumber' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $subComponent = SubComponent::create($validator->validated());

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
            'orderNumber' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $subComponent->update($validator->validated());

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
