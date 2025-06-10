<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Http\Resources\AdminResource;
use App\Http\Resources\AdminCollection;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Admin::with('permissions');


        if ($request->has('search') && !empty($request->search)) {

            $searchTerm = '%' . $request->search . '%';

            $query->where(function ($q) use ($searchTerm) {

                $q->where('name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm);
            });
        }


        if ($request->has('sort_by') && !empty($request->sort_by)) {

            $sortBy = $request->sort_by;

            $sortOrder = $request->get('sort_order', 'asc');


            $allowedSortColumns = ['name', 'email', 'created_at', 'updated_at', 'is_blocked'];



            if (in_array($sortBy, $allowedSortColumns)) {

                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }


        $perPage = max(1, min($request->get('per_page', 10), 100));

        $admins = $query->paginate($perPage);

        return new AdminCollection($admins);
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

        $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id');
        $admin->permissions()->attach($permissionIds);
        $admin->load('permissions');

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => new AdminResource($admin)
        ], 201);
    }

    public function show(Request $request, Admin $admin)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $admin->load('permissions');

        return response()->json([
            'admin' => new AdminResource($admin)
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
            $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id');
            $admin->permissions()->sync($permissionIds);
        }

        $admin->load('permissions');

        return response()->json([
            'message' => 'Admin updated successfully',
            'admin' => new AdminResource($admin)
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

    public function getStats(Request $request)
    {
        if (!$request->user()->hasPermission('super_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_admins' => Admin::count(),
            'active_admins' => Admin::where('is_blocked', false)->count(),
            'blocked_admins' => Admin::where('is_blocked', true)->count(),
            'super_admins' => Admin::whereHas('permissions', function ($query) {
                $query->where('name', 'super_admin');
            })->count(),
            'total_permissions' => Permission::count(),
        ];

        return response()->json($stats);
    }
}
