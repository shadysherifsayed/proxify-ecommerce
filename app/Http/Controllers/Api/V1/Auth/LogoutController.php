<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Controllers\Controller;

class LogoutController extends Controller
{
    /**
     * Create a new controller instance
     * 
     * @param AuthService $authService Service for authentication operations
     */
    public function __construct(private AuthService $authService) {}

    /**
     * Logout the authenticated user
     * 
     * Revokes the current user's authentication token, effectively
     * logging them out from the current session. The token will
     * no longer be valid for API requests.
     * 
     * @param Request $request Request object containing user authentication
     * @return \Illuminate\Http\Response Empty response with 204 No Content status
     */
    public function __invoke(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->noContent();
    }
}
