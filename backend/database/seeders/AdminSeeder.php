<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'is_blocked' => false,
        ]);

        $allPermissions = Permission::all();
        $superAdmin->permissions()->attach($allPermissions);

        $requestManager = Admin::create([
            'name' => 'Request Manager',
            'email' => 'requestmanager@example.com',
            'password' => Hash::make('password'),
            'is_blocked' => false,
        ]);

        $manageRequestsPermission = Permission::where('name', 'manage_requests')->first();
        $requestManager->permissions()->attach($manageRequestsPermission);

        $serviceManager = Admin::create([
            'name' => 'Service Manager',
            'email' => 'servicemanager@example.com',
            'password' => Hash::make('password'),
            'is_blocked' => false,
        ]);

        $manageServicesPermission = Permission::where('name', 'manage_services')->first();
        $serviceManager->permissions()->attach($manageServicesPermission);

        $blockedAdmin = Admin::create([
            'name' => 'Blocked Admin',
            'email' => 'blocked@example.com',
            'password' => Hash::make('password'),
            'is_blocked' => true,
        ]);

        $blockedAdmin->permissions()->attach($manageRequestsPermission);
    }
}
