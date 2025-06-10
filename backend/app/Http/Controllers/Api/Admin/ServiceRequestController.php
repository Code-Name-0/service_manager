<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Mail\ServiceRequestStatusUpdated;
use App\Events\ServiceRequestStatusChanged;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Resources\ServiceRequestCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('manage_requests')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = ServiceRequest::with(['service', 'admin']);

        if ($request->has('status') && !empty($request->status)) {
            $query->byStatus($request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';

            $query->where(function ($q) use ($searchTerm) {

                $q->where('client_name', 'like', $searchTerm)

                    ->orWhere('client_email', 'like', $searchTerm)

                    ->orWhere('message', 'like', $searchTerm)
                    ->orWhereHas('service', function ($serviceQuery) use ($searchTerm) {


                        $serviceQuery->where('name', 'like', $searchTerm);
                    });
            });
        }


        if ($request->has('sort_by') && !empty($request->sort_by)) {

            $sortBy = $request->sort_by;


            $sortOrder = $request->get('sort_order', 'asc');


            $allowedSortColumns = ['client_name', 'client_email', 'status', 'created_at', 'updated_at'];



            if (in_array($sortBy, $allowedSortColumns)) {

                $query->orderBy($sortBy, $sortOrder);
            } elseif ($sortBy === 'service') {

                $query->join('services', 'service_requests.service_id', '=', 'services.id')
                    ->orderBy('services.name', $sortOrder)


                    ->select('service_requests.*');
            }
        } else {

            $query->orderBy('created_at', 'desc');
        }

        $perPage = max(1, min($request->get('per_page', 10), 100));

        $serviceRequests = $query->paginate($perPage);

        return new ServiceRequestCollection($serviceRequests);
    }

    public function approve(Request $request, ServiceRequest $serviceRequest)
    {
        if (!$request->user()->hasPermission('manage_requests')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'admin_response' => 'nullable|string'

        ]);

        $serviceRequest->update([
            'status' => 'approved',
            'admin_response' => $request->admin_response ?? 'Your request has been approved.',
            'admin_id' => $request->user()->id
        ]);

        $serviceRequest->load(['service', 'admin']);

        // Send email notification (queued)
        Mail::to($serviceRequest->client_email)->queue(new ServiceRequestStatusUpdated($serviceRequest));


        return response()->json([
            'message' => 'Service request approved successfully',
            'service_request' => new ServiceRequestResource($serviceRequest)
        ]);
    }

    public function deny(Request $request, ServiceRequest $serviceRequest)
    {
        if (!$request->user()->hasPermission('manage_requests')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'admin_response' => 'required|string'
        ]);

        $serviceRequest->update([
            'status' => 'denied',
            'admin_response' => $request->admin_response,
            'admin_id' => $request->user()->id
        ]);

        $serviceRequest->load(['service', 'admin']);

        // Send email notification (queued)
        Mail::to($serviceRequest->client_email)->queue(new ServiceRequestStatusUpdated($serviceRequest));


        return response()->json([
            'message' => 'Service request denied',
            'service_request' => new ServiceRequestResource($serviceRequest)
        ]);
    }

    public function complete(Request $request, ServiceRequest $serviceRequest)
    {
        if (!$request->user()->hasPermission('manage_requests')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($serviceRequest->status !== 'approved') {
            return response()->json([
                'message' => 'Only approved requests can be marked as completed'
            ], 400);
        }

        $request->validate([
            'admin_response' => 'nullable|string'
        ]);

        $serviceRequest->update([
            'status' => 'completed',
            'admin_response' => $request->admin_response ?? $serviceRequest->admin_response,
            'admin_id' => $request->user()->id
        ]);

        $serviceRequest->load(['service', 'admin']);

        // Send email notification (queued)
        Mail::to($serviceRequest->client_email)->queue(new ServiceRequestStatusUpdated($serviceRequest));


        return response()->json([
            'message' => 'Service request marked as completed',
            'service_request' => new ServiceRequestResource($serviceRequest)
        ]);
    }

    public function getStats(Request $request)
    {
        if (!$request->user()->hasPermission('manage_requests')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_requests' => ServiceRequest::count(),
            'pending_requests' => ServiceRequest::where('status', 'pending')->count(),
            'approved_requests' => ServiceRequest::where('status', 'approved')->count(),
            'denied_requests' => ServiceRequest::where('status', 'denied')->count(),
            'completed_requests' => ServiceRequest::where('status', 'completed')->count(),
        ];

        return response()->json($stats);
    }
}
