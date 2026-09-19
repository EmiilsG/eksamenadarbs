<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('user');

        if ($request->filled('search')) {
            $query->where('products.name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('products.category', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('products.price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('products.price', '<=', $request->max_price);
        }

        switch ($request->get('sort')) {
            case 'price_low':
                $query->orderBy('products.price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('products.price', 'desc');
                break;
            case 'rating':
                $query->join('users', 'users.id', '=', 'products.user_id')
                    ->select('products.*')
                    ->orderBy('users.rating', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->get();
        $categories = Product::select('category')->distinct()->pluck('category')->sort()->values();
        $favoritedIds = auth()->check() ? auth()->user()->favorites()->pluck('product_id')->all() : [];

        return view('products.index', compact('products', 'categories', 'favoritedIds'));
    }

    public function mine()
    {
        $products = auth()->user()->products()->latest()->get();

        return view('products.mine', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $request->user()->products()->create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category ?: 'Cita',
            'image' => $imagePath,
        ]);

        return redirect()->route('products.index')->with('success', 'Produkts veiksmīgi pievienots!');
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Jūs varat dzēst tikai savus produktus.');
        }

        if ($product->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produkts veiksmīgi dzēsts!');
    }
}
