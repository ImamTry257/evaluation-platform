<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use HasApiResponse;

    /**
     * POST /api/v1/auth/login
     * Unified login for admin and respondent.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $username = strtolower($request->username);
        $user = User::where('email', $username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Username atau password salah', 401);
        }

        if (!$user->isActive) {
            return $this->errorResponse('Akun tidak aktif', 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => strtoupper($user->role),
            ],
        ], 'Login successful');
    }

    /**
     * POST /api/v1/auth/logout
     * Revoke current access token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * GET /api/v1/auth/profile
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => strtoupper($user->role),
        ]);
    }
}
