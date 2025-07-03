<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Api\V1\AuthenticatedUserResource;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Services\AuthService;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{

    public function __construct(private AuthService $authService) {}

    public function __invoke(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'user' => $user,
            'token' => $this->authService->issueToken($user),
        ], Response::HTTP_CREATED);
    }
}
