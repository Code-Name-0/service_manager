<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{

    public function toArray(Request $request): array
    {

        return [

            'id' => $this->id,
            'name' => $this->name,

            'description' => $this->description,
            'is_active' => $this->is_active,

            'status_text' => $this->is_active ? 'Active' : 'Inactive',
            'service_requests_count' => $this->whenCounted('serviceRequests'),

            'pending_requests_count' => $this->whenLoaded('serviceRequests', function () {

                return $this->serviceRequests->where('status', 'pending')->count();
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
