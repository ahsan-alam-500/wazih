<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;

class CartController extends Controller
{
    // ✅ Add to Cart
    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $cart = json_decode(Cookie::get('cart', '[]'), true);

        // Check if product already exists in the cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        // If not found, add new item
        if (!$found) {
            $cart[] = ['id' => $productId, 'quantity' => $quantity];
        }

        // Save updated cart to cookie for 7 days
        Cookie::queue('cart', json_encode($cart), 60 * 24 * 7);

        return response()->json(['message' => 'Added to cart' . $productId . ''], 200);
    }

    // ✅ View Cart
    public function viewCart()
    {
        $cart = json_decode(Cookie::get('cart', '[]'), true);
        $productIds = collect($cart)->pluck('id')->unique();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $cartItems = collect($cart)->map(function ($item) use ($products) {
            $product = $products[$item['id']] ?? null;
            if (!$product) return null;

            return [
                'id' => $item['id'],
                'quantity' => $item['quantity'],
                'name' => $product->name,
                'image' => $product->image,
                'price' => $product->price,
            ];
        })->filter()->values();

        return Inertia::render('frontend/Cart', [
            'cartItems' => $cartItems,
        ]);
    }

    // ✅ Remove Specific Item from Cart
    public function removeFromCart($id)
    {
        $cart = json_decode(Cookie::get('cart', '[]'), true);
        $cart = array_filter($cart, fn($item) => $item['id'] != $id);

        Cookie::queue('cart', json_encode(array_values($cart)), 60 * 24 * 7);

        return response()->json(['message' => 'Removed from cart']);
    }

    // ✅ Clear All Cart Items
    public function clearCart()
    {
        Cookie::queue(Cookie::forget('cart'));
        return response()->json(['message' => 'Cart cleared']);
    }
}
