<?php

namespace App\Http\Controllers\Auth;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('user_name', 'password');
        $token = Auth::attempt($credentials);

        if ($token) {
            $user = Auth::user();

            return response()->json([
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ],
                'user' => $user,
            ]);
        } else {

            return response()->json([
                'error' => 'Invalid User Name and Password',
            ], 401);
        }
    }

    public function updatePassword(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();

        // Validate the input
        $validator = \Validator::make($request->all(), [
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 400);
        }

           // Update the user's password
           DB::table('users')->update([
            'password' => Hash::make($input['password']),
        ]);

        // Return a success response
        return response()->json([
            'success' =>  true,
            'message' => 'Password updated successfully.',
        ], 200);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }
}
