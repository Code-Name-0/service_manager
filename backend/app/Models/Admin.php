<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [

        'name',
        'email',

        'password',
        'is_blocked',

    ];


    protected $hidden = [

        'password',
        'remember_token',

    ];


    protected $casts = [

        'is_blocked' => 'boolean',
    ];


    public function permissions()
    {

        return $this->belongsToMany(Permission::class, 'admin_permissions');
    }


    public function serviceRequests()
    {

        return $this->hasMany(ServiceRequest::class);
    }


    public function hasPermission($permission)
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }
}
