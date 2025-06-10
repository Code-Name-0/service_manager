<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceCollection;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }


        $query = Service::withCount('serviceRequests');

        if ($request->has('search') && !empty($request->search)) {

            $searchTerm = '%' . $request->search . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)

                    ->orWhere('description', 'like', $searchTerm);
            });
        }


        if ($request->has('sort_by') && !empty($request->sort_by)) {
            $sortBy = $request->sort_by;


            $sortOrder = $request->get('sort_order', 'asc');


            $allowedSortColumns = ['name', 'description', 'is_active', 'created_at', 'updated_at', 'service_requests_count'];


            if (in_array($sortBy, $allowedSortColumns)) {

                if ($sortBy === 'service_requests_count') {

                    $query->orderBy('service_requests_count', $sortOrder);
                } else {


                    $query->orderBy($sortBy, $sortOrder);
                }
            }
        } else {

            $query->orderBy('created_at', 'desc');
        }


        $perPage = max(1, min($request->get('per_page', 10), 100));

        $services = $query->paginate($perPage);

        return new ServiceCollection($services);
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
            'service' => new ServiceResource($service)
        ], 201);
    }

    public function show(Request $request, Service $service)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $service->loadCount('serviceRequests');

        return response()->json([
            'service' => new ServiceResource($service)
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
            'service' => new ServiceResource($service)
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

    public function toggleStatus(Request $request, Service $service)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $service->update([
            'is_active' => !$service->is_active
        ]);

        $status = $service->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Service {$status} successfully",
            'service' => new ServiceResource($service)
        ]);
    }

    public function getStats(Request $request)
    {
        if (!$request->user()->hasPermission('manage_services')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_services' => Service::count(),
            'active_services' => Service::where('is_active', true)->count(),
            'inactive_services' => Service::where('is_active', false)->count(),
            'total_requests' => \App\Models\ServiceRequest::count(),
        ];

        return response()->json($stats);
    }
}
