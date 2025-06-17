<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    public function index()
    {
        return Inertia::render('landingPages/Index', [
            'landingPages' => LandingPage::latest()->get(),
        ]);
    }

    public function create()
    {
        $products = Product::select('name', 'image', 'id')
            ->groupBy('name', 'image', 'id')
            ->get()
            ->unique(fn($item) => $item->name . $item->image)
            ->values();
        return Inertia::render('landingPages/Create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'banner' => 'nullable|image|max:2048',
            'images.*' => 'nullable|image|max:2048',
            'product_id' => 'required|exists:products,id',
        ]);

        // Banner image upload
        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('banners', 'public');
        }

        // Multiple image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('gallery', 'public');
            }
        }

        LandingPage::create([
            'title' => $request->title,
            'description' => $request->description,
            'banner' => $bannerPath,
            'images' => json_encode($imagePaths),
            'product_id' => $request->product_id,
        ]);

        return redirect()->route('landingPages.index')->with('success', 'Page created successfully');
    }

    public function edit(LandingPage $landingPage)
    {
        $products = Product::select('name', 'image', 'id')
            ->groupBy('name', 'image', 'id')
            ->get()
            ->unique(fn($item) => $item->name . $item->image)
            ->values();

        return Inertia::render('landingPages/Edit', [
            'landingPage' => $landingPage,
            'products' => $products,
        ]);
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'product_id'  => 'required|exists:products,id',
            'banner'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Handle banner upload
        if ($request->hasFile('banner')) {
            if ($landingPage->banner && Storage::exists($landingPage->banner)) {
                Storage::delete($landingPage->banner);
            }
            $bannerPath = $request->file('banner')->store('landing_banners');
            $landingPage->banner = $bannerPath;
        }

        // Handle gallery image uploads
        if ($request->hasFile('images')) {
            // Optional: delete old gallery images if stored (you may keep old if desired)
            if (is_array($landingPage->images)) {
                foreach ($landingPage->images as $img) {
                    if (Storage::exists($img)) {
                        Storage::delete($img);
                    }
                }
            }

            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('landing_gallery');
                $imagePaths[] = $path;
            }
            $landingPage->images = $imagePaths;
        }

        $landingPage->update([
            'title'       => $request->title,
            'description' => $request->description,
            'product_id'  => $request->product_id,
        ]);

        $landingPage->save();

        return redirect()->route('landingPages.index')->with('success', 'Landing Page updated successfully');
    }

    public function destroy(LandingPage $landingPage)
    {
        $landingPage->delete();

        return redirect()->route('landingPages.index')->with('success', 'Page deleted');
    }

    public function removeImage($id, $index)
    {
        $page = LandingPage::findOrFail($id);
        $images = $page->images;

        if (isset($images[$index]) && Storage::exists($images[$index])) {
            Storage::delete($images[$index]);
            unset($images[$index]);
            $page->images = array_values($images); // Re-index
            $page->save();
        }

        return response()->json(['message' => 'Image removed']);
    }
}
