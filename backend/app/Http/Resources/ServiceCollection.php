<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceCollection extends ResourceCollection
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
                'total_services' => $this->collection->count(),
                'active_services' => $this->collection->where('is_active', true)->count(),
                'inactive_services' => $this->collection->where('is_active', false)->count(),
                'total_requests' => $this->collection->sum('service_requests_count'),
            ],
        ];
    }
}
