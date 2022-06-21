<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Auth;
use Illuminate\Support\Arr;

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
        $user->name = mb_convert_case(mb_strtolower($validated['name']), MB_CASE_TITLE, 'UTF-8');
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->normalized_name = strtolower(normalize($validated['name']));

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

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|nullable|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'sort' => 'required_with:order|in:name,city',
            'order' => 'required_with:sort|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'payload' => ['validation_errors' => $validator->messages()],
                'error' => true,
                'message' => __('system.validation_error'),
            ], 400);
        }
        $validated = $validator->validated();

        $users = User::select('*');
        if (isset($validated['name'])) {
            $searchString = normalize($validated['name']);

            $users = $users->where('normalized_name', 'LIKE', '%' . $searchString . '%');
        }

        if (isset($validated['city'])) {
            $users = $users->where(function ($q) use ($validated) {
                $q->whereHas('address', function ($q) use ($validated) {
                    $q->where('city', '=', $validated['city']);
                });
            });
        }

        if (isset($validated['sort']) && isset($validated['order'])) {
            if ($validated['sort'] == 'city') {
                $users = $users->join('addresses', 'users.id', '=', 'addresses.user_id')
                    ->orderBy('city', $validated['order']);

            } else {
                $users = $users->orderBy($validated['sort'], $validated['order']);
            }
        } else {
            $users = $users->orderBy('name', 'asc');
        }

        $users = $users->get();
        if (count($users) > 0) {
            return response()->json([
                'payload' => compact('users'),
                'error' => false,
                'message' => __('system.success'),
            ], 200);
        } else {
            return response()->json([
                'payload' => null,
                'error' => true,
                'message' => __('system.not_found'),
            ], 404);
        }
    }

    public function show($id)
    {
        $user = User::with('address')->whereId($id)->first();
        if (empty($user)) {
            return response()->json([
                'payload' => null,
                'error' => true,
                'message' => __('system.not_found'),
            ], 404);
        }

        return response()->json([
            'payload' => compact('user'),
            'error' => false,
            'message' => __('system.success'),
        ], 200);
    }

    public function update($id, Request $request)
    {
        $user = User::find($id);
        if (empty($user)) {
            return response()->json([
                'payload' => null,
                'error' => false,
                'message' => __('system.not_found'),
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required_without:email|nullable|string|max:100',
            'email' => 'required_without:name|nullable|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'payload' => ['validation_errors' => $validator->messages()],
                'error' => true,
                'message' => __('system.validation_error'),
            ], 400);
        }
        $validated = $validator->validated();

        if (isset($validated['name'])) {
            $user->name = mb_convert_case(mb_strtolower($validated['name']), MB_CASE_TITLE, 'UTF-8');
            $user->normalized_name = strtolower(normalize($validated['name']));
        }
        if (isset($validated['email'])) {
            $user->email = strtolower($validated['email']);
        }

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
                'message' => __('system.failed_to_save'),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = User::with('address')->whereId($id)->first();
        if (empty($user)) {
            return response()->json([
                'payload' => null,
                'error' => false,
                'message' => __('system.not_found'),
            ], 404);
        }

        $userAdresses = Arr::pluck($user->address, 'id');
        if (isset($userAdresses) && count($userAdresses) > 0) {
            foreach ($userAdresses as $addressId) {
                $userAddress = Address::find($addressId);
                $userAddress->delete();
            }
        }

        $user->delete();
        return response()->json([
            'payload' => null,
            'error' => false,
            'message' => __('system.deleted'),
        ], 200);
    }
}
