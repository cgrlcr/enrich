<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class adressController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string|in:İSTANBUL,ANKARA,İZMİR',
            'address' => 'required|string|max:1000',
            'user_id' => 'required|numeric|exists:users,id', //this must be taken from auth user
        ]);

        if ($validator->fails()) {
            return response()->json([
                'payload' => ['validation_errors' => $validator->messages()],
                'error' => true,
                'message' => __('system.validation_error'),
            ], 400);
        }
        $validated = $validator->validated();

        $address = new Address();
        $address->city = $validated['city'];
        $address->address = $validated['address'];
        $address->user_id = $validated['user_id'];

        if ($address->save()) {
            return response()->json([
                'payload' => compact('address'),
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
}
