<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function __invoke(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->noContent();
    }
}
