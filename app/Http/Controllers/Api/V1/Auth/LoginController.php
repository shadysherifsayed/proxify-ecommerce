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
    public function __construct(private AuthService $authService) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = (new Timebox)->call(fn () => $request->authenticate(), 1000);

        return response()->json([
            'user' => $user,
            'token' => $this->authService->issueToken($user),
        ], Response::HTTP_OK);
    }
}
