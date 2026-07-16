<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KomponenController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/components
     * List all components with pagination.
     */
    public function index(Request $request)
    {
        $query = Component::with('questionnaire');

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by questionnaire
        if ($request->has('questionnaireId') && $request->questionnaireId) {
            $query->where('questionnaireId', $request->questionnaireId);
        }

        $limit = min($request->get('limit', 10), 100);

        $components = $query->orderBy('orderNumber', 'asc')
            ->paginate($limit);

        return $this->listResponse($components, 'Components retrieved successfully');
    }

    /**
     * POST /api/v1/admin/components
     * Create a new component.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questionnaireId' => 'required|exists:questionnaires,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'orderNumber' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $component = Component::create($validator->validated());

        return $this->successResponse(
            $component->load('questionnaire'),
            'Component created successfully',
            201
        );
    }

    /**
     * GET /api/v1/admin/components/{id}
     * Get a single component.
     */
    public function show($id)
    {
        $component = Component::with('questionnaire')->find($id);

        if (!$component) {
            return $this->errorResponse('Component not found', 404);
        }

        return $this->successResponse($component, 'Component retrieved successfully');
    }

    /**
     * PUT /api/v1/admin/components/{id}
     * Update a component.
     */
    public function update(Request $request, $id)
    {
        $component = Component::find($id);

        if (!$component) {
            return $this->errorResponse('Component not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'questionnaireId' => 'required|exists:questionnaires,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'orderNumber' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $component->update($validator->validated());

        return $this->successResponse(
            $component->load('questionnaire'),
            'Component updated successfully'
        );
    }

    /**
     * DELETE /api/v1/admin/components/{id}
     * Delete a component.
     */
    public function destroy($id)
    {
        $component = Component::find($id);

        if (!$component) {
            return $this->errorResponse('Component not found', 404);
        }

        $component->delete();

        return $this->successResponse(null, 'Component deleted successfully');
    }
}
