<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use Illuminate\Support\Timebox;

class LoginController extends Controller
{

    public function __construct(private AuthService $authService) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = (new Timebox())->call(fn () => $request->authenticate(), 1000);

        return response()->json([
            'user' => $user,
            'token' => $this->authService->issueToken($user),
        ], Response::HTTP_OK);
    }
}
