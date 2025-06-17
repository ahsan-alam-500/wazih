<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderFilterController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status'); // Get query param like 'pending'

        // Filter orders by status if provided
        $orders = Order::query()
            ->when($status, fn($query) => $query->where('status', $status))
            ->with('user') // Optional: eager load customer
            ->latest()
            ->paginate(15)
            ->withQueryString(); // Keep ?status on pagination links

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }
}
