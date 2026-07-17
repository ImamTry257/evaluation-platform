<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

    /**
     * POST /api/v1/admin/respondents/import
     * Bulk import respondents from CSV file.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        if (!$handle) {
            return $this->errorResponse('Failed to read CSV file', 500);
        }

        // Read header
        $header = fgetcsv($handle);

        // Validate header columns
        $requiredColumns = ['name', 'email', 'password'];
        $headerLower = array_map('strtolower', array_map('trim', $header));

        foreach ($requiredColumns as $col) {
            if (!in_array($col, $headerLower)) {
                fclose($handle);
                return $this->errorResponse('CSV must have columns: name, email, password', 422);
            }
        }

        $nameIndex = array_search('name', $headerLower);
        $emailIndex = array_search('email', $headerLower);
        $passwordIndex = array_search('password', $headerLower);

        $imported = 0;
        $failed = 0;
        $errors = [];
        $rowNumber = 1;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $name = trim($row[$nameIndex] ?? '');
                $email = trim($row[$emailIndex] ?? '');
                $password = trim($row[$passwordIndex] ?? '');

                // Validate row data
                if (empty($name) || empty($email) || empty($password)) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: Name, email, and password are required";
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: Invalid email format ({$email})";
                    continue;
                }

                if (strlen($password) < 8) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: Password must be at least 8 characters";
                    continue;
                }

                // Check if email already exists
                if (User::where('email', strtolower($email))->exists()) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: Email already exists ({$email})";
                    continue;
                }

                // Generate username from email
                $username = strtolower(explode('@', $email)[0]);

                // Ensure username is unique
                $baseUsername = $username;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                // Create user
                User::create([
                    'name' => $name,
                    'username' => $username,
                    'email' => strtolower($email),
                    'password' => Hash::make($password),
                    'role' => 'respondent',
                    'isActive' => true,
                ]);

                $imported++;
            }

            fclose($handle);
            \Illuminate\Support\Facades\DB::commit();

            return $this->successResponse([
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ], "{$imported} respondents successfully imported");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            fclose($handle);
            return $this->errorResponse('Import failed: ' . $e->getMessage(), 500);
        }
    }
}
