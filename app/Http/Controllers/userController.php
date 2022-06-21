<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class userController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'min:6', 'required_with:password_confirmation', 'confirmed'], //regex:/^\(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6}$/
        ]);

        if ($validator->fails()) {
            return response()->json([
                'payload' => ['validation_errors' => $validator->messages()],
                'error' => true,
                'message' => __('system.validation_error'),
            ], 400);
        }
        $validated = $validator->validated();

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->normalized_name = normalize($validated['name']);

        if ($user->save()) {
            return response()->json([
                'payload' => compact('user'),
                'error' => false,
                'message' => __('system.success'),
            ], 200);
        } else {
            return response()->json([
                'payload' => null,
                'error' => true,
                'message' => __('system.individual_failed_to_save'),
            ], 500);
        }
    }

    public function index()
    {
        $users = User::all();
        return response()->json([
            'payload' => compact('users'),
            'error' => false,
            'message' => __('system.success'),
        ], 200);
    }
}
