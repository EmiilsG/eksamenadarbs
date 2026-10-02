<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
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

    public function show(Request $request, Product $product)
    {
        $product->load('images', 'user');

        $seller = $product->user;

        $sellerReviews = Review::with('reviewer')
            ->where('reviewee_id', $seller->id)
            ->latest()
            ->limit(3)
            ->get();

        $otherProducts = $seller->products()
            ->where('products.id', '!=', $product->id)
            ->latest()
            ->limit(3)
            ->get();

        $isFavorited = $request->user()
            ? $request->user()->favorites()->where('product_id', $product->id)->exists()
            : false;

        $sellerStats = Review::where('reviewee_id', $seller->id)
            ->selectRaw('count(*) as total, avg(rating) as average')
            ->first();

        return view('products.show', [
            'product' => $product,
            'seller' => $seller,
            'sellerReviews' => $sellerReviews,
            'otherProducts' => $otherProducts,
            'favoritesCount' => $product->favorites()->count(),
            'sellerRating' => round((float) $sellerStats->average, 1),
            'sellerReviewsCount' => (int) $sellerStats->total,
            'sellerProductsCount' => $seller->products()->count(),
            'isFavorited' => $isFavorited,
            'gallery' => $product->gallery(),
        ]);
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
            'images' => 'nullable|array|max:' . Product::MAX_IMAGES,
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [], ['images' => 'attēli']);

        $product = $request->user()->products()->create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category ?: 'Cita',
        ]);

        foreach ($request->file('images', []) as $position => $file) {
            $product->storeImage($file, $position);
        }

        return redirect()->route('products.index')->with('success', 'Produkts veiksmīgi pievienots!');
    }

    public function edit(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Jūs varat rediģēt tikai savus produktus.');
        }

        $product->load('images');

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Jūs varat rediģēt tikai savus produktus.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:' . $product->remainingImageSlots(),
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'images.max' => 'Produktam var būt ne vairāk par :max attēliem.',
        ], [
            'images' => 'attēli',
        ]);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category ?: 'Cita',
        ]);

        foreach ($request->file('images', []) as $file) {
            $product->storeImage($file);
        }

        return redirect()->route('products.mine')->with('success', 'Produkts veiksmīgi atjaunināts!');
    }

    public function storeImages(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Jūs varat pievienot attēlus tikai saviem produktiem.');
        }

        $request->validate([
            'images' => 'required|array|min:1|max:' . $product->remainingImageSlots(),
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'images.required' => 'Izvēlieties vismaz vienu attēlu.',
            'images.max' => 'Produktam var būt ne vairāk par :max attēliem.',
        ], [
            'images' => 'attēli',
        ]);

        foreach ($request->file('images', []) as $file) {
            $product->storeImage($file);
        }

        return redirect()->route('products.edit', $product)->with('success', 'Attēli veiksmīgi pievienoti!');
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Jūs varat dzēst attēlus tikai saviem produktiem.');
        }

        abort_unless($image->product_id === $product->id, 404);

        $product->deleteImage($image);

        return redirect()->route('products.edit', $product)->with('success', 'Attēls veiksmīgi dzēsts!');
    }

    public function makeMainImage(Product $product, ProductImage $image)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Jūs varat mainīt attēlus tikai saviem produktiem.');
        }

        abort_unless($image->product_id === $product->id, 404);

        $product->makeMainImage($image);

        return redirect()->route('products.edit', $product)->with('success', 'Galvenā attēla nomainīts!');
    }

    public function destroy(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Jūs varat dzēst tikai savus produktus.');
        }

        if ($product->image) {
            $product->deleteStoredImage($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produkts veiksmīgi dzēsts!');
    }
}
