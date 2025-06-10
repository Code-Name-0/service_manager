<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{

    public function toArray(Request $request): array
    {

        return [

            'id' => $this->id,
            'name' => $this->name,

            'email' => $this->email,
            'is_blocked' => $this->is_blocked,

            'permissions' => $this->permissions->pluck('name'),
            'role' => $this->getRoleName(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }


    public function getRoleName()
    {
        $permissions = $this->permissions->pluck('name')->toArray();

        if (in_array('super_admin', $permissions)) {
            return 'Super Admin';
        } elseif (in_array('manage_services', $permissions)) {
            return 'Service Manager';
        } elseif (in_array('manage_requests', $permissions)) {
            return 'Request Manager';
        }

        return 'Admin';
    }
}
