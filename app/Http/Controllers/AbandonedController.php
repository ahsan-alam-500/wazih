<?php

namespace App\Http\Controllers;

use App\Models\AbandonedOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbandonedController extends Controller
{
    public function index()
    {
        return Inertia::render("abandoned/Index");
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
}
