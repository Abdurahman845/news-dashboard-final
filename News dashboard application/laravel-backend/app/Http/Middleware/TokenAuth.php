<?php

namespace App\Http\Middleware;

use App\Models\Token;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization') ?? $request->server('HTTP_AUTHORIZATION');

        if (!$header || !Str::startsWith(Str::lower($header), 'bearer ')) {
            return response()->json(['error' => 'Unauthorized - Please login'], 401);
        }

        $tokenString = trim(Str::substr($header, 7));
        if (!$tokenString) {
            return response()->json(['error' => 'Unauthorized - Please login'], 401);
        }

        $token = Token::with('user')->where('token', $tokenString)->first();
        if (!$token || !$token->user) {
            return response()->json(['error' => 'Unauthorized - Please login'], 401);
        }

        Auth::setUser($token->user);
        $request->attributes->set('auth_user', $token->user);

        return $next($request);
    }
}
