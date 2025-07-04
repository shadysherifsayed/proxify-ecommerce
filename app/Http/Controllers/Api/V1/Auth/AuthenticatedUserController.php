<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticatedUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Return the authenticated user
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
