<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items.product'])->paginate(10);


        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
