<?php

namespace App\Http\Controllers;

use App\Events\PlaceOrder;
use App\Models\AbandonedOrder;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Order;
use App\Models\Order_items;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Str;

class AbandonedController extends Controller
{
    public function index()
    {
        $abandonedOrders = AbandonedOrder::all();
        return Inertia::render("abandoned/Index", [
            "abandonedOrders" => $abandonedOrders
        ]);
    }

    public function store(Request $request)
    {
        $abandoned = AbandonedOrder::create([
            "product_id" => $request->product_id,
            "product_name" => $request->product_name,
            "product_image" => $request->product_image,
            "product_price" => $request->product_price,
            "mobile" => $request->mobile,
            "email" => $request->email,
        ]);
        return response()->json(["success" => true, "message" => __("Abandoned order created successfully")], 200);
    }

    public function update(Request $request, AbandonedOrder $abandonedOrder)
    {
        try {
            $user = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name'     => $request->name ?? 'Guest',
                    'mobile'   => $request->mobile,
                    'role'     => 'customer',
                    'password' => Hash::make($request->mobile),
                ]
            );

            $product = Product::create([
                'category_id' => 2,
                'name'        => $request->product_name,
                'slug'        => Str::slug($request->product_name),
                'description' => 'transformed from Incomplete cart',
                'price'       => $request->product_price,
                'quantity' => 9999,
                'image'       => $request->product_image,
                'status'      => 'active',
            ]);

            $order = Order::create([
                'user_id'          => $user->id,
                'product_id' => $product->id,
                'order_number'     => Str::random(10),
                'total_amount'     => $request->price,
                'source'           => 'web',
                'payment_status'   => 'unpaid',
                'shipping_address' => 'Not Set',
                'status'           => 'processing',
            ]);

            Order_items::create([
                'order_id'   => $order->id,
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity,
                'price'      => $request->price,
                'total'      => $request->total_price,
            ]);

            Activity::create([
                'user_id' => $user->id,
                'action' => "Order Has been Placed",
                'description' => "A new order has been placed",
            ]);

            event(new PlaceOrder($order));

            return response()->json([
                'success'   => true,
                'message'   => 'Order created successfully',
                'order_id'  => $order->id,
                'user_id'   => $user->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
