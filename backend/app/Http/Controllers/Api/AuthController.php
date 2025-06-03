<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'creds' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }

        if ($admin->is_blocked) {
            return response()->json([
                'message' => 'Your account has been blocked. Please contact the administrator.'
            ], 403);
        }


        $admin->load('permissions');

        $token = $admin->createToken('auth-token')->plainTextToken;

        return response()->json([
            'admin' => $admin,
            'token' => $token,
            'permissions' => $admin->permissions->pluck('name')
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function me(Request $request)
    {
        $admin = $request->user();
        $admin->load('permissions');

        return response()->json([
            'admin' => $admin,
            'permissions' => $admin->permissions->pluck('name')
        ]);
    }
}
