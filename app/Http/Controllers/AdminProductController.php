<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Str;

class AdminProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::orderBy("id", "desc")->get();
        return Inertia::render("products/Index", ['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return Inertia::render('products/Create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'discount_price' => 'nullable|numeric',
            'quantity' => 'required|integer',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $filename = uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('products', $filename, 'public');
            $data['image'] = url('storage/' . $path); // This generates something like: http://127.0.0.1:8000/storage/products/abc.jpg
        }

        Product::create($data);

        return redirect()->back()->with('success', 'Product created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Product = Product::find($id);
        $categories = Category::all();
        return Inertia::render('products/Edit', ['Product' => $Product, 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id'     => 'required',
            'name'            => 'required|string|max:255',
            'slug'            => 'required|string|max:255|unique:products,slug,' . $product->id,
            'description'     => 'nullable|string',
            'price'           => 'required|numeric',
            'discount_price'  => 'nullable|numeric',
            'quantity'        => 'required|integer',
            'status'          => 'required|in:active,inactive',
            'image'           => 'nullable|image|max:2048',
        ]);

        // Handle new image upload if provided
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = env('APP_URL') . '/storage/' . $path;
        }

        $product->update($data);

        return redirect()->back()->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully!');
    }
}
