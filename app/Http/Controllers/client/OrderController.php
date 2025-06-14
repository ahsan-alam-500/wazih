<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Order_items;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        // Step 1: Validate Inputs (includes user data now)
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'mobile'           => 'required',
            'total_amount'     => 'required|numeric',
            'source'           => 'nullable|string',
            'payment_status'   => 'required|string',
            'shipping_address' => 'required|string',
            'status'           => 'required|string',
            'product_id'       => 'required|integer|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            'price'            => 'required|numeric',
            'total_price'      => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Step 2: Create new user
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'mobile'    => $request->mobile,
                'role' => 'customer',
                'password' => Hash::make($request->email),
            ]);

            $userId = User::where('email', $request->email)->first()->id;

            // Step 3: Create Order
            $order = Order::create([
                'user_id'          => $userId,
                'order_number'     => Str::random(10),
                'total_amount'     => $request->total_amount,
                'source'           => $request->source,
                'payment_status'   => $request->payment_status,
                'shipping_address' => $request->shipping_address,
                'status'           => $request->status,
            ]);

            // Step 4: Create Order Item
            Order_items::create([
                'order_id'   => $order->id,
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity,
                'price'      => $request->price,
                'total'      => $request->total_price,
            ]);

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

    public function update(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'nullable|string|in:Pending,Processing,Shipped,Delivered,Cancelled',
            'payment_status' => 'nullable|string|in:paid,unpaid',
        ]);

        $data = [];

        if ($request->filled('status')) {
            $data['status'] = $request->status;
        }

        if ($request->filled('payment_status')) {
            $data['payment_status'] = $request->payment_status;
        }

        Order::where('id', $request->order_id)->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
        ]);
    }
}
