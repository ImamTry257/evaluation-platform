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

        // Get breadcrumb data
        $breadCrumbList = null;
        if ($request->has('questionnaireId') && $request->questionnaireId) {
            $breadCrumbList = [
                'questionnaire' => \App\Models\Questionnaire::find($request->questionnaireId),
            ];
        } elseif ($components->count() > 0) {
            $breadCrumbList = [
                'questionnaire' => $components->first()->questionnaire,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Components retrieved successfully',
            'data' => [
                'breadCrumbList' => $breadCrumbList,
                'contents' => $components->items(),
                'meta' => [
                    'page' => $components->currentPage(),
                    'limit' => $components->perPage(),
                    'total' => $components->total(),
                ],
            ],
        ]);
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
            'orderNumber' => 'nullable|integer|min:0',
            'is_active' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();
        
        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Component::where('questionnaireId', $data['questionnaireId'])
                ->max('orderNumber');
            $data['orderNumber'] = ($maxOrder ?? 0) + 1;
        }

        // Set default is_active if not provided
        if (!isset($data['is_active'])) {
            $data['is_active'] = 1;
        }

        $component = Component::create($data);

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
            'orderNumber' => 'nullable|integer|min:0',
            'is_active' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();
        
        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Component::where('questionnaireId', $data['questionnaireId'])
                ->where('id', '!=', $id)
                ->max('orderNumber');
            $data['orderNumber'] = ($maxOrder ?? 0) + 1;
        }

        $component->update($data);

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
