<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Services\AuthService;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance
     *
     * @param  AuthService  $authService  Service for authentication operations
     */
    public function __construct(private AuthService $authService) {}

    /**
     * Register a new user account
     *
     * Creates a new user account with the provided registration data and
     * returns the user information along with an authentication token
     * for immediate login.
     *
     * @param  RegisterRequest  $request  Validated registration request containing user data
     * @return \Illuminate\Http\JsonResponse JSON response with user data and auth token (201 Created)
     */
    public function __invoke(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'user' => $user,
            'token' => $this->authService->issueToken($user),
        ], Response::HTTP_CREATED);
    }
}
