<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status'); // Get ?status from URL

        // Filter by status if provided, and eager load user & items.product
        $orders = Order::with(['user', 'items.product'])
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString(); // Keep ?status in pagination links

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'status' => $status, // Pass current filter to frontend
            'credentials' => [
                'id'     => Auth::user()?->id,
                'name'   => Auth::user()?->name,
                'email'  => Auth::user()?->email,
                'mobile' => Auth::user()?->mobile,
            ],
        ]);
    }

    public function report(Request $request)
    {
        $orders = Order::with(['user', 'items.product'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->when(
                $request->from_date && $request->to_date,
                fn($q) => $q->whereBetween('created_at', [$request->from_date, $request->to_date])
            )
            ->latest()
            ->get();

        $completedOrders = $orders->where('status', 'completed')->count();
        $totalSales = $orders->where('status', 'completed')->sum('total_amount');

        return Inertia::render('report/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'payment_status', 'from_date', 'to_date']),
            'stats' => [
                'completed_orders' => $completedOrders,
                'total_sales' => $totalSales,
            ],
        ]);
    }
}
