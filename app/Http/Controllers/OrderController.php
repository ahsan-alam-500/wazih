<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items.product'])->paginate(10);
        return Inertia::render('Orders/Index', [
            'orders' => $orders,
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
