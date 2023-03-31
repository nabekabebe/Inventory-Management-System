<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Traits\HttpResponses;
use Auth;
use Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginRequest $request)
    {
        $request->validated($request->all());

        if (!Auth::attempt($request->safe()->only(['email', 'password']))) {
            return $this->failure(
                'Credentials do not match!',
                Response::HTTP_UNAUTHORIZED
            );
        }
        $user = User::firstWhere('email', $request->email);

        return $this->success([
            'user' => $user,
            'token' => $user->createToken('Login Token for' . $user->full_name)
                ->plainTextToken
        ]);
    }
    public function register(StoreUserRequest $request)
    {
        //        Todo: restrict manager signup
        $request->validated($request->all());
        $is_manager = request('is_manager');
        $managing_token = request('managing_token');
        if ($is_manager) {
            $managing_token = Hash::make(now() . $request->full_name);
        }
        $newUser = User::create([
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'managing_token' => $managing_token,
            'is_manager' => $is_manager
        ]);
        return $this->success([
            'user' => $newUser,
            'token' => $newUser->createToken('token of' . $newUser->full_name)
                ->plainTextToken
        ]);
    }
    public function logout()
    {
        Auth::user()
            ->currentAccessToken()
            ->delete();
        return $this->success(['message' => 'Successfully logged out']);
    }
    public function noSuchRoute()
    {
        return $this->failure(
            'Invalid request! no such route exits!',
            Response::HTTP_BAD_GATEWAY
        );
    }
}
