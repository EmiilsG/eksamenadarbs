<?php

namespace App\Http\Controllers;

use App\Models\Product;

class FavoriteController extends Controller
{
    public function index()
    {
        $products = Product::whereHas('favorites', function ($query) {
            $query->where('user_id', auth()->id());
        })->with('user')->latest()->get();

        return view('favorites.index', compact('products'));
    }

    public function store(Product $product)
    {
        auth()->user()->favorites()->firstOrCreate(['product_id' => $product->id]);

        return back()->with('success', 'Prece pievienota favorītiem!');
    }

    public function destroy(Product $product)
    {
        auth()->user()->favorites()->where('product_id', $product->id)->delete();

        return back()->with('success', 'Prece noņemta no favorītiem!');
    }
}