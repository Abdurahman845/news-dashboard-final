<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        $token = $this->createToken($user->id);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $this->userPayload($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Email and password are required',
                'message' => 'Email and password are required',
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        $user = User::where('email', $request->string('email'))->first();

        if (!$user || !Hash::check($request->string('password'), $user->password)) {
            return response()->json([
                'error' => 'Invalid email or password',
                'message' => 'Invalid email or password',
            ], 401);
        }

        $token = $this->createToken($user->id);

        return response()->json([
            'message' => 'Login successful',
            'user' => $this->userPayload($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $this->resolveUser($request);

        return response()->json($this->userPayload($user));
    }

    public function logout(Request $request)
    {
        $tokenString = $this->getBearerToken($request);

        if ($tokenString) {
            Token::where('token', $tokenString)->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    protected function createToken(int $userId): string
    {
        $token = Str::random(64);

        Token::create([
            'user_id' => $userId,
            'token' => $token,
        ]);

        return $token;
    }

    protected function resolveUser(Request $request): User
    {
        return $request->attributes->get('auth_user') ?? Auth::user();
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ];
    }

    protected function getBearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization') ?? $request->server('HTTP_AUTHORIZATION');

        if (!$header || !Str::startsWith(Str::lower($header), 'bearer ')) {
            return null;
        }

        $token = trim(Str::substr($header, 7));

        return $token !== '' ? $token : null;
    }
}
