<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Events\NewServiceRequestSubmitted;
use App\Http\Resources\ServiceRequestResource;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:20',
            'message' => 'required|string'
        ]);

        $serviceRequest = ServiceRequest::create([
            'service_id' => $request->service_id,
            'client_name' => $request->client_name,
            'client_email' => $request->client_email,
            'client_phone' => $request->client_phone,
            'message' => $request->message,
            'status' => 'pending'
        ]);


        $serviceRequest->load('service');

        // broadcast event for real-time notifications
        broadcast(new NewServiceRequestSubmitted($serviceRequest));

        return response()->json([
            'message' => 'Service request submitted successfully!',
            'service_request' => new ServiceRequestResource($serviceRequest)
        ], 201);
    }
}
