<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComponentResource;
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
            $query->where('questionnaire_id', $request->questionnaireId);
        }

        $limit = min($request->get('limit', 10), 100);
        $components = $query->orderBy('order_number', 'asc')
            ->paginate($limit);

        // Get breadcrumb data
        $breadCrumbList = null;
        if ($components->count() != 0) {
            $firstComponent = $components->first();
            $breadCrumbList = [
                'questionnaire' => $firstComponent->questionnaire ?? null,
            ];
        } else {
            $questionnaire = \App\Models\Questionnaire::find($request->questionnaireId);
            $breadCrumbList = [
                'questionnaire' => $questionnaire,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Components retrieved successfully',
            'data' => [
                'breadCrumbList' => $breadCrumbList,
                'contents' => ComponentResource::collection($components),
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
            'isActive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'questionnaire_id' => $data['questionnaireId'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['isActive'] ?? false,
        ];

        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Component::where('questionnaire_id', $dbData['questionnaire_id'])
                ->max('order_number');
            $dbData['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $dbData['order_number'] = $data['orderNumber'];
        }

        $component = Component::create($dbData);

        return $this->successResponse(
            new ComponentResource($component->load('questionnaire')),
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

        return $this->successResponse(
            new ComponentResource($component),
            'Component retrieved successfully'
        );
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
            'isActive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        // Map camelCase to snake_case for database
        $dbData = [
            'questionnaire_id' => $data['questionnaireId'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['isActive'] ?? false,
        ];

        // Auto-generate orderNumber if not provided
        if (!isset($data['orderNumber'])) {
            $maxOrder = Component::where('questionnaire_id', $dbData['questionnaire_id'])
                ->where('id', '!=', $id)
                ->max('order_number');
            $dbData['order_number'] = ($maxOrder ?? 0) + 1;
        } else {
            $dbData['order_number'] = $data['orderNumber'];
        }

        $component->update($dbData);

        return $this->successResponse(
            new ComponentResource($component->load('questionnaire')),
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
