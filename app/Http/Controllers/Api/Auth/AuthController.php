<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            $response = $this->errorResponse('Validation failed', 422, $validator->errors());
            Log::warning('Register validation failed', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);
            return $response;
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => strtolower($request->username),
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
                'role' => 'respondent',
                'is_active' => true,
            ]);

            $response = $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => strtoupper($user->role),
                ],
            ], 'Registration successful', 201);

            Log::info('Register successful', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);

            return $response;
        } catch (\Throwable $th) {
            $response = $this->errorResponse('Internal Server Error', 500);
            Log::error('Register error', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
                'error' => $th->getMessage(),
            ]);
            return $response;
        }
    }

    /**
     * POST /api/v1/auth/login
     * Unified login for admin and respondent.
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                $response = $this->errorResponse('Validation failed', 422, $validator->errors());
                Log::warning('Login validation failed', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true),
                ]);
                return $response;
            }

            $username = strtolower($request->username);
            $user = User::where(['username' => $username])->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                $response = $this->errorResponse('Username atau password salah', 401);
                Log::warning('Login failed - invalid credentials', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true),
                ]);
                return $response;
            }

            if (!$user->is_active) {
                $response = $this->errorResponse('Akun tidak aktif', 403);
                Log::warning('Login failed - inactive account', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true),
                ]);
                return $response;
            }

            $expiredTokenAt = now()->addHours(1);
            $token = $user->createToken(
                'auth-token',
                ['*'],
                $expiredTokenAt,
            )->plainTextToken;

            // update last login at
            $user->update(['last_login_at' => now()]);

            $response = $this->successResponse([
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

            Log::info('Login successful', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);

            return $response;
        } catch (\Throwable $th) {
            $response = $this->errorResponse('Internal Server Error', 500);
            Log::error('Login error', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
                'error' => $th->getMessage(),
            ]);
            return $response;
        }
    }

    /**
     * POST /api/v1/auth/login-admin
     * Login khusus admin & superadmin via email.
     */
    public function loginAdmin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                $response = $this->errorResponse('Validation failed', 422, $validator->errors());
                Log::warning('LoginAdmin validation failed', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true),
                ]);
                return $response;
            }

            $email = strtolower($request->email);
            $user = User::where('email', $email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                $response = $this->errorResponse('Email atau password salah', 401);
                Log::warning('LoginAdmin failed - invalid credentials', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true),
                ]);
                return $response;
            }

            // Hanya admin & superadmin yang boleh login via ini
            if (!in_array($user->role, ['admin', 'superadmin'])) {
                $response = $this->errorResponse('Akun ini tidak memiliki akses admin', 403);
                Log::warning('LoginAdmin failed - not admin', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true),
                ]);
                return $response;
            }

            if (!$user->is_active) {
                $response = $this->errorResponse('Akun tidak aktif', 403);
                Log::warning('LoginAdmin failed - inactive account', [
                    'path' => $request->url(),
                    'requestDate' => date('Y-m-d h:i:s'),
                    'request' => $request->all(),
                    'response' => $response->getData(true),
                ]);
                return $response;
            }

            $expiredTokenAt = now()->addHours(1);
            $token = $user->createToken(
                'auth-token',
                ['*'],
                $expiredTokenAt,
            )->plainTextToken;

            // update last login at
            $user->update(['last_login_at' => now()]);

            $response = $this->successResponse([
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

            Log::info('LoginAdmin successful', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);

            return $response;
        } catch (\Throwable $th) {
            $response = $this->errorResponse('Internal Server Error', 500);
            Log::error('LoginAdmin error', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
                'error' => $th->getMessage(),
            ]);
            return $response;
        }
    }

    /**
     * POST /api/v1/auth/logout
     * Revoke current access token.
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();

            $response = $this->successResponse(null, 'Logged out successfully');

            Log::info('Logout successful', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);

            return $response;
        } catch (\Throwable $th) {
            $response = $this->errorResponse('Internal Server Error', 500);
            Log::error('Logout error', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
                'error' => $th->getMessage(),
            ]);
            return $response;
        }
    }

    /**
     * GET /api/v1/auth/validate
     * Validate current access token and return user profile.
     */
    public function validate(Request $request)
    {
        try {
            $user = $request->user();

            $response = $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => strtoupper($user->role),
            ]);

            Log::info('Validate token successful', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
            ]);

            return $response;
        } catch (\Throwable $th) {
            $response = $this->errorResponse('Internal Server Error', 500);
            Log::error('Validate token error', [
                'path' => $request->url(),
                'requestDate' => date('Y-m-d h:i:s'),
                'request' => $request->all(),
                'response' => $response->getData(true),
                'error' => $th->getMessage(),
            ]);
            return $response;
        }
    }
}