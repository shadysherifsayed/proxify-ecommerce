<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest; 
use App\Http\Resources\Api\V1\AuthenticatedUserResource;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request)
    {
        $user = $request->authenticate();

        return new AuthenticatedUserResource($user);
    }
}
