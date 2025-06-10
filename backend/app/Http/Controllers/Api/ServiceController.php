<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Http\Resources\ServiceCollection;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        $query->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc');

        $perPage = min($request->get('per_page', 15), 100);

        $services = $query->paginate($perPage);

        return new ServiceCollection($services);
    }
}
