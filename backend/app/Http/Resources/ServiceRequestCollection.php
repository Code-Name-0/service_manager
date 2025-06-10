<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceRequestCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_requests' => $this->collection->count(),
                'pending_requests' => $this->collection->where('status', 'pending')->count(),
                'approved_requests' => $this->collection->where('status', 'approved')->count(),
                'denied_requests' => $this->collection->where('status', 'denied')->count(),
                'completed_requests' => $this->collection->where('status', 'completed')->count(),
            ],
        ];
    }
}
