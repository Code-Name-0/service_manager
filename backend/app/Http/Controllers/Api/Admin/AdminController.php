<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // hna jib all admins, and les permissions ta3hom
        $admins = Admin::with('permissions')->get();

        return response()->json([
            'admins' => $admins
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_blocked' => false
        ]);

        $admin->permissions()->attach($request->permissions);
        $admin->load('permissions');

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => $admin
        ], 201);
    }

    public function show(Request $request, Admin $admin)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $admin->load('permissions');

        return response()->json([
            'admin' => $admin
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:admins,email,' . $admin->id,
            'password' => 'sometimes|required|string|min:6',
            'permissions' => 'sometimes|required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $updateData = $request->only(['name', 'email']);

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);

        if ($request->has('permissions')) {
            $admin->permissions()->sync($request->permissions);
        }

        $admin->load('permissions');

        return response()->json([
            'message' => 'Admin updated successfully',
            'admin' => $admin
        ]);
    }

    public function block(Request $request, Admin $admin)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent blocking yourself
        if ($admin->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot block yourself (drop the DB and get blocked by others...)'
            ], 400);
        }

        $admin->update(['is_blocked' => true]);

        return response()->json([
            'message' => 'Admin blocked successfully'
        ]);
    }

    public function unblock(Request $request, Admin $admin)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $admin->update(['is_blocked' => false]);

        return response()->json([
            'message' => 'Admin unblocked successfully'
        ]);
    }

    public function permissions()
    {
        $permissions = Permission::all();

        return response()->json([
            'permissions' => $permissions
        ]);
    }
}
