<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('web');

        return response()->json([
            'data' => [
                'id' => (string) $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'timezone' => $user->timezone,
            ],
        ]);
    }
}
