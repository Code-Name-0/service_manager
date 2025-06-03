<?php

namespace Database\Seeders;

use App\Models\ServiceRequest;
use App\Models\Service;
use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $webDevService = Service::where('name', 'Web Development')->first();
        $digitalMarketingService = Service::where('name', 'Digital Marketing')->first();
        $superAdmin = Admin::where('email', 'superadmin@example.com')->first();

        $requests = [
            [
                'service_id' => $webDevService->id,
                'client_name' => 'John Doe',
                'client_email' => 'john@example.com',
                'client_phone' => '+1234567890',
                'message' => 'I need a custom e-commerce website for my business. Looking for modern design with payment integration.',
                'status' => 'pending',
            ],
            [
                'service_id' => $digitalMarketingService->id,
                'client_name' => 'Jane Smith',
                'client_email' => 'djebarra.ra@gmail.com',
                'client_phone' => '+1987654321',
                'message' => 'Need help with SEO optimization and social media marketing for my startup.',
                'status' => 'approved',
                'admin_response' => 'Your request has been approved. We will contact you within 24 hours to discuss the details.',
                'admin_id' => $superAdmin->id,
            ],
            [
                'service_id' => $webDevService->id,
                'client_name' => 'Bob Johnson',
                'client_email' => 'bob@example.com',
                'client_phone' => '+1122334455',
                'message' => 'Looking for a simple portfolio website for my freelance business.',
                'status' => 'completed',
                'admin_response' => 'Project completed successfully. Thank you for choosing our services!',
                'admin_id' => $superAdmin->id,
            ],
            [
                'service_id' => $digitalMarketingService->id,
                'client_name' => 'Alice Brown',
                'client_email' => 'alice@example.com',
                'client_phone' => '+1555666777',
                'message' => 'Interested in comprehensive digital marketing strategy for my restaurant chain.',
                'status' => 'denied',
                'admin_response' => 'Unfortunately, we cannot take on this project at the moment due to capacity constraints.',
                'admin_id' => $superAdmin->id,
            ],
            [
                'service_id' => $webDevService->id,
                'client_name' => 'Mike Wilson',
                'client_email' => 'mike@example.com',
                'client_phone' => '+1888999000',
                'message' => 'Need a web application for inventory management with user authentication.',
                'status' => 'pending',
            ],
        ];

        foreach ($requests as $request) {
            ServiceRequest::create($request);
        }
    }
}
