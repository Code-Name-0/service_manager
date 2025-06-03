<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * I think this will be removed at some point
 * request status update triggers only email notification
 */


class ServiceRequestStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceRequest $serviceRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('service-requests'),
            new PrivateChannel('admin-notifications')
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'service.request.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->serviceRequest->id,
            'status' => $this->serviceRequest->status,
            'client_name' => $this->serviceRequest->client_name,
            'service_name' => $this->serviceRequest->service->name,
            'admin_response' => $this->serviceRequest->admin_response,
            'updated_at' => $this->serviceRequest->updated_at->toISOString(),
            'message' => $this->getBroadcastMessage()
        ];
    }

    /**
     * Get broadcast message
     */
    private function getBroadcastMessage(): string
    {
        return match ($this->serviceRequest->status) {
            'approved' => "Service request from {$this->serviceRequest->client_name} has been approved",
            'denied' => "Service request from {$this->serviceRequest->client_name} has been denied",
            'completed' => "Service request from {$this->serviceRequest->client_name} has been completed",
            default => "Service request from {$this->serviceRequest->client_name} has been updated"
        };
    }
}
