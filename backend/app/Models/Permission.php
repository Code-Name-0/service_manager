<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_permissions');
    }
}
