<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class AuthenticatedUserController extends Controller
{
    /**
     * Get the currently authenticated user
     * 
     * Returns the profile information of the currently authenticated user
     * based on the provided authentication token.
     * 
     * @param Request $request Request object containing user authentication
     * @return JsonResponse JSON response containing the authenticated user data
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Return the authenticated user
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
