<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Web Development',
                'description' => 'Custom website development using modern technologies like Laravel, Vue.js, React, and more.',
                'is_active' => true,
            ],
            [
                'name' => 'Digital Marketing',
                'description' => 'Comprehensive digital marketing services including SEO, social media management, and online advertising.',
                'is_active' => true,
            ]
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
