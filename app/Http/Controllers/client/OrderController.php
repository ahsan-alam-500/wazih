<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Order;
use App\Models\Order_items;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Events\PlaceOrder;
use App\Models\EmployeeRange;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'email'            => 'required|email',
            'mobile'           => 'required',
            'total_amount'     => 'required|numeric',
            'source'           => 'nullable|string',
            'payment_status'   => 'required|string',
            'shipping_address' => 'required|string',
            'cart'             => 'required|array|min:1',
            'cart.*.id'        => 'required|integer|exists:products,id',
            'cart.*.quantity'  => 'required|integer|min:1',
            'cart.*.price'     => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name'     => $request->name,
                    'mobile'   => $request->mobile,
                    'role'     => 'customer',
                    'password' => Hash::make($request->email),
                ]
            );

            $order = Order::create([
                'user_id'          => $user->id,
                'order_number'     => Str::random(10),
                'total_amount'     => $request->total_amount,
                'source'           => $request->source,
                'payment_status'   => $request->payment_status,
                'shipping_address' => $request->shipping_address,
                'status'           => $request->status ?? 'pending',
            ]);

            foreach ($request->cart as $item) {
                Order_items::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'total'      => $item['price'] * $item['quantity'],
                ]);
            }

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

    public function wpOrder(Request $request)
    {
        try {
            \Log::info('Webhook received', $request->all());
            $billing = $request->billing;
            $email = $billing['email'] ?? 'guest_' . Str::random(6) . '@example.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $billing['first_name'] . ' ' . $billing['last_name'],
                    'mobile'   => $billing['phone'],
                    'role'     => 'customer',
                    'password' => Hash::make($billing['email']),
                ]
            );

            foreach ($request->line_items as $item) {
                $imageUrl = $item['image']['src'] ?? null;

                $product = Product::create([
                    'category_id' => 2,
                    'name'        => $item['name'],
                    'slug'        => Str::slug($item['name']),
                    'description' => $item['name'],
                    'price'       => $item['price'],
                    'quantity'    => $item['quantity'],
                    'image'       => $imageUrl,
                    'status'      => 'active',
                ]);

                if (!isset($order)) {
                    $order = Order::create([
                        'user_id'          => $user->id,
                        'order_number'     => $request->number ?? Str::random(10),
                        'total_amount'     => $request->total,
                        'source'           => 'wordpress',
                        'payment_status'   => $request->payment_method_title == 'Cash on delivery' ? 'unpaid' : 'paid',
                        'shipping_address' => implode(', ', array_filter([
                            $request->shipping['address_1'] ?? '',
                            $request->shipping['address_2'] ?? '',
                            $request->shipping['city'] ?? '',
                            $request->shipping['state'] ?? '',
                            $request->shipping['postcode'] ?? ''
                        ])),
                        'status' => 'pending',
                    ]);
                }

                Order_items::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'total'      => $item['total'],
                ]);
            }

            Activity::create([
                'user_id'     => $user->id,
                'action'      => "Order Has been Placed",
                'description' => "A new order has been placed via WooCommerce",
            ]);

            event(new PlaceOrder($order));

            return response()->json([
                'success'  => true,
                'message'  => 'Order created successfully',
                'order_id' => $order->id,
                'user_id'  => $user->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Order webhook error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Order creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'nullable|string',
            'payment_status' => 'nullable|string|in:paid,unpaid',
        ]);

        $data = [];

        if ($request->filled('status')) {
            $data['status'] = $request->status;
        }

        if ($request->filled('payment_status')) {
            $data['payment_status'] = $request->payment_status;
        }

        $data['status_updated_by'] = $request->user()->id . '-' . $request->user()->name;

        Order::where('id', $request->order_id)->update($data);

        $user = Auth::user();

        Activity::create([
            'user_id' => $user->id,
            'action' => "Order has been updated by {$user->name}",
            'description' => "Order has been updated"
        ]);

        try {
            EmployeeRange::create([
                "name" => Auth::user()->name,
                "user_id" => Auth::user()->id,
                "order_id" => $request->order_id,
                "type" => "order status updated",
                "stage" => "Basic",
                "status" => "active",
                "point" => 1,
            ]);
        } catch (\Exception $e) {
            \Log::error('EmployeeRange create failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
        ]);
    }
}
