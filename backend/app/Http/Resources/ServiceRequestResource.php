<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'client_email' => $this->client_email,
            'client_phone' => $this->client_phone,
            'message' => $this->message,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'admin_response' => $this->admin_response,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'admin' => new AdminResource($this->whenLoaded('admin')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'formatted_created_at' => $this->created_at->format('M d, Y H:i'),
        ];
    }

    /**
     * Get human readable status text
     */
    private function getStatusText()
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'denied' => 'Denied',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }
}
