<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $services = Service::withCount('serviceRequests')->get();

        return response()->json([
            'services' => $services
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'boolean'
        ]);

        $service = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'message' => 'Service created successfully',
            'service' => $service
        ], 201);
    }

    public function show(Request $request, Service $service)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $service->loadCount('serviceRequests');

        return response()->json([
            'service' => $service
        ]);
    }

    public function update(Request $request, Service $service)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'is_active' => 'sometimes|boolean'
        ]);

        $service->update($request->only(['name', 'description', 'is_active']));

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service
        ]);
    }

    public function destroy(Request $request, Service $service)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if service has pending requests
        if ($service->serviceRequests()->where('status', 'pending')->exists()) {
            return response()->json([
                'message' => 'Cannot delete service with pending requests'
            ], 400);
        }

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ]);
    }
}
