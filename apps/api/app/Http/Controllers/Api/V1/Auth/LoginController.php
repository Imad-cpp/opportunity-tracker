<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $key = $this->rateLimitKey($credentials['email'], $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'error' => [
                    'code' => 'RATE_LIMITED',
                    'message' => 'Too many login attempts. Try again later.',
                ],
            ], 429, ['Retry-After' => (string) $retryAfter]);
        }

        if (! Auth::guard('web')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            RateLimiter::hit($key, 60);

            return response()->json([
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'The provided credentials are invalid.',
                ],
            ], 422);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::guard('web')->user();

        return response()->json([
            'data' => [
                'id' => (string) $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'timezone' => $user->timezone,
            ],
        ]);
    }

    private function rateLimitKey(string $email, ?string $ip): string
    {
        return 'login:'.hash('sha256', $email).'|'.($ip ?? 'unknown');
    }
}
