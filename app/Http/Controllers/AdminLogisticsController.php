<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;

class AdminLogisticsController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');

        $query = Delivery::with(['order.customer', 'traveler'])->latest();

        if ($status !== '') {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['assigned', 'picked_up', 'delivered', 'failed']);
        }

        $deliveries = $query->paginate(20)->withQueryString();

        return view('admin.logistics', [
            'deliveries' => $deliveries,
            'status' => $status,
        ]);
    }
}

