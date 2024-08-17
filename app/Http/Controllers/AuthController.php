<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8||max:255',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                ['errors' => $validator->errors()
                ],
                422
            ); // HTTP Status Code 422 Unprocessable Content
        }

        $user = User::create(
            [
            'name' => $request->name,
            'email' => $request->email,
            // @phpstan-ignore-next-line
            'password' => Hash::make($request->password),
            ]
        );

        return response()->json(
            ['message' => 'User registered successfully',
            'user' => $user],
            201
        ); // HTTP Status Code 201 Created Successfully
    }
}
