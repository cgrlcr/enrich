<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Libraries\JWT;

class AuthController extends Controller
{
    protected $auth;

    public function __construct()
    {
        $this->auth = Auth::guard('user');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'payload' => ['validation_errors' => $validator->messages()],
                'error' => true,
                'message' => __('system.validation_error'),
            ], 400);
        }

        $credentials = $request->only('email', 'password');

        if ($this->auth->attempt($credentials)) {
            $user = $this->auth->user();

            if ($user->status === 'active') {
                $tokens = Token::where('authable_id', $user->id)->where('authable_type', 'User')->where('status', '!=', 'revoked')->get();
                if (isset($tokens)) {
                    foreach ($tokens as $token) {
                        JWT::revoke($token->key);
                    }
                }

                $key = JWT::sign('User', $user->id, '+9 hours', [], $user->id, null);
                $user->updateQuietly(['token' => $key]);

                //Log::info('login', ['user_id' => $user->id, 'process' => 'login', 'ip' => $request->ip()]);

                return response()->json([
                    'payload' => array(
                        'user' => $user,
                        'token' => $key,
                        'ip' => $request->ip(),
                    ),
                    'error' => false,
                    'message' => __('system.success'),
                ], 200);
            } else {
                // Log::info('login:failed', ['user_id' => $user->id, 'process' => 'login:failed', 'ip' => $request->ip()]);

                return response()->json([
                    'payload' => null,
                    'error' => true,
                    'message' => __('system.auth_failed'),
                ], 403);
            }
        } else {
            return response()->json([
                'payload' => null,
                'error' => true,
                'message' => __('system.auth_failed'),
            ], 403);
        }
    }

    public function checkToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'verify' => 'sometimes|nullable|in:1,0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'payload' => ['validation_errors' => $validator->messages()],
                'error' => true,
                'message' => __('system.validation_error'),
            ], 400);
        }
        $validated = $validator->validated();

        if (isset($validated['verify']) && $validated['verify'] == 1) {
            $verification = JWT::verify($validated['token'], true);
        } else {
            $verification = JWT::verify($validated['token']);
        }

        if (isset($verification)) {
            return response()->json([
                'payload' => null,
                'error' => false,
                'message' => __('system.success'),
            ], 200);
        } else {
            return response()->json([
                'payload' => null,
                'error' => true,
                'message' => __('system.invalid_token'),
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = User::find($request->user_id);
        if (isset($user) and $user->status === 'active') {
            $tokens = Token::where('user_id', $user->id)->where('status', '!=', 'revoked')->get();
            if (isset($tokens)) {
                foreach ($tokens as $token) {
                    JWT::revoke($token->key);
                }
            }
        }

        //Log::info('logout', ['user_id' => $user->id, 'process' => 'logout', 'ip' => $request->ip()]);

        return response()->json([
            'payload' => null,
            'error' => false,
            'message' => __('system.success'),
        ], 200);
    }
}
