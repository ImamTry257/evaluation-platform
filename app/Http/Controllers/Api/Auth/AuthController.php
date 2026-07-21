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
     * POST /api/v1/auth/register
     * Register new respondent.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|same:passwordConfirmation',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role' => 'respondent',
            'is_active' => true,
        ]);

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => strtoupper($user->role),
            ],
        ], 'Registration successful', 201);
    }

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
        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Username atau password salah', 401);
        }

        if (!$user->is_active) {
            return $this->errorResponse('Akun tidak aktif', 403);
        }

        $expiredTokenAt = now()->addHours(1);
        $token = $user->createToken(
            'auth-token',
            ['*'],
            $expiredTokenAt,
        )->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'expiredAt' => $expiredTokenAt->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => strtoupper($user->role),
            ],
        ], 'Login successful');
    }

    /**
     * POST /api/v1/auth/login-admin
     * Login khusus admin & superadmin via email.
     */
    public function loginAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Email atau password salah', 401);
        }

        // Hanya admin & superadmin yang boleh login via ini
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            return $this->errorResponse('Akun ini tidak memiliki akses admin', 403);
        }

        if (!$user->is_active) {
            return $this->errorResponse('Akun tidak aktif', 403);
        }

        $expiredTokenAt = now()->addHours(1);
        $token = $user->createToken(
            'auth-token',
            ['*'],
            $expiredTokenAt,
        )->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'expiredAt' => $expiredTokenAt->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
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
            'username' => $user->username,
            'email' => $user->email,
            'role' => strtoupper($user->role),
        ]);
    }
}
