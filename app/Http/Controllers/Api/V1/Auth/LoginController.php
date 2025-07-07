<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Timebox;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /**
     * Create a new controller instance
     *
     * @param  AuthService  $authService  Service for authentication operations
     */
    public function __construct(private AuthService $authService) {}

    /**
     * Authenticate user and return access token
     *
     * Validates user credentials and returns user information along with
     * an authentication token. Uses a timebox to prevent timing attacks.
     *
     * @param  LoginRequest  $request  Validated login request containing credentials
     * @return JsonResponse JSON response with user data and auth token (200 OK)
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = (new Timebox)->call(fn () => $request->authenticate(), 500);

        return response()->json([
            'user' => $user,
            'token' => $this->authService->issueToken($user),
        ], Response::HTTP_OK);
    }
}
