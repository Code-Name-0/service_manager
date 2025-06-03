<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Mail\ServiceRequestStatusUpdated;
use App\Events\ServiceRequestStatusChanged;
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

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $serviceRequests = $query->latest()->get();

        return response()->json([
            'service_requests' => $serviceRequests
        ]);
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

        // notification are only for admins when a new request is submitted
        // broadcast(new ServiceRequestStatusChanged($serviceRequest));

        return response()->json([
            'message' => 'Service request approved successfully',
            'service_request' => $serviceRequest
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

        // broadcast(new ServiceRequestStatusChanged($serviceRequest));

        return response()->json([
            'message' => 'Service request denied',
            'service_request' => $serviceRequest
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

        // broadcast(new ServiceRequestStatusChanged($serviceRequest));

        return response()->json([
            'message' => 'Service request marked as completed',
            'service_request' => $serviceRequest
        ]);
    }
}
