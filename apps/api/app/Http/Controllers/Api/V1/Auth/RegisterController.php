<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create($request->validated());

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return response()->json([
            'data' => $this->payload($user),
        ], 201);
    }

    /** @return array{id: string, name: string, email: string, timezone: string} */
    private function payload(User $user): array
    {
        return [
            'id' => (string) $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'timezone' => $user->timezone,
        ];
    }
}
