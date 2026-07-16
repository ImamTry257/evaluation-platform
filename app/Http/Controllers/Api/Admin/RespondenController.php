<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RespondenController extends Controller
{
    use HasApiResponse;

    /**
     * GET /api/v1/admin/respondents
     * List all respondents with pagination.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'respondent');

        // Search by name, username, or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by isActive
        if ($request->has('isActive') && $request->isActive !== '') {
            $query->where('isActive', filter_var($request->isActive, FILTER_VALIDATE_BOOLEAN));
        }

        $limit = min($request->get('limit', 10), 100);

        $respondents = $query->select('id', 'name', 'username', 'email', 'isActive', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->listResponse($respondents, 'Respondents retrieved successfully');
    }

    /**
     * POST /api/v1/admin/respondents
     * Create a new respondent.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'isActive' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $respondent = User::create([
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role' => 'respondent',
            'isActive' => $request->boolean('isActive', true),
        ]);

        return $this->successResponse(
            $respondent->only('id', 'name', 'username', 'email', 'isActive', 'created_at', 'updated_at'),
            'Respondent created successfully',
            201
        );
    }

    /**
     * GET /api/v1/admin/respondents/{id}
     * Get a single respondent.
     */
    public function show($id)
    {
        $respondent = User::where('role', 'respondent')->find($id);

        if (!$respondent) {
            return $this->errorResponse('Respondent not found', 404);
        }

        return $this->successResponse(
            $respondent->only('id', 'name', 'username', 'email', 'isActive', 'created_at', 'updated_at'),
            'Respondent retrieved successfully'
        );
    }

    /**
     * PUT /api/v1/admin/respondents/{id}
     * Update a respondent.
     */
    public function update(Request $request, $id)
    {
        $respondent = User::where('role', 'respondent')->find($id);

        if (!$respondent) {
            return $this->errorResponse('Respondent not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'isActive' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $data = [
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => strtolower($request->email),
            'isActive' => $request->boolean('isActive', true),
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $respondent->update($data);

        return $this->successResponse(
            $respondent->only('id', 'name', 'username', 'email', 'isActive', 'created_at', 'updated_at'),
            'Respondent updated successfully'
        );
    }

    /**
     * DELETE /api/v1/admin/respondents/{id}
     * Delete a respondent.
     */
    public function destroy($id)
    {
        $respondent = User::where('role', 'respondent')->find($id);

        if (!$respondent) {
            return $this->errorResponse('Respondent not found', 404);
        }

        // Check if respondent has response sessions
        if ($respondent->responseSessions()->count() > 0) {
            return $this->errorResponse('Cannot delete respondent with existing evaluation sessions', 422);
        }

        $respondent->delete();

        return $this->successResponse(null, 'Respondent deleted successfully');
    }
}
