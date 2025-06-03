<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'client_name',
        'client_email',
        'client_phone',
        'message',
        'status',
        'admin_response',
        'admin_id',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDenied($query)
    {
        return $query->where('status', 'denied');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
