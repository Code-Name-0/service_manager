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

        // Get all admins with their permissions as names array
        $admins = Admin::with('permissions')->get()->map(function ($admin) {
            $adminArray = $admin->toArray();
            $adminArray['permissions'] = $admin->permissions->pluck('name');
            return $adminArray;
        });

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
            'permissions.*' => 'exists:permissions,name'
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_blocked' => false
        ]);

        // Sync permissions by names
        $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id');
        $admin->permissions()->attach($permissionIds);
        $admin->load('permissions');

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => array_merge($admin->toArray(), [
                'permissions' => $admin->permissions->pluck('name')
            ])
        ], 201);
    }

    public function show(Request $request, Admin $admin)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $admin->load('permissions');

        return response()->json([
            'admin' => array_merge($admin->toArray(), [
                'permissions' => $admin->permissions->pluck('name')
            ])
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
            'permissions.*' => 'exists:permissions,name'
        ]);

        $updateData = $request->only(['name', 'email']);

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);

        if ($request->has('permissions')) {
            // Sync permissions by names
            $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id');
            $admin->permissions()->sync($permissionIds);
        }

        $admin->load('permissions');

        return response()->json([
            'message' => 'Admin updated successfully',
            'admin' => array_merge($admin->toArray(), [
                'permissions' => $admin->permissions->pluck('name')
            ])
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

    public function destroy(Request $request, Admin $admin)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent deleting yourself
        if ($admin->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 400);
        }

        $admin->delete();

        return response()->json([
            'message' => 'Admin deleted successfully'
        ]);
    }
}
