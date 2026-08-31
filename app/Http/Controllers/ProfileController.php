<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request, ?User $user = null)
    {
        $user = $user ?? $request->user();

        abort_unless($user, 404);

        $reviews = Review::with('reviewer')
            ->where('reviewee_id', $user->id)
            ->latest()
            ->get();

        return view('profile', [
            'profileUser' => $user,
            'productsCount' => $user->products()->count(),
            'reviewsCount' => $user->reviews_count,
            'averageRating' => $user->average_rating,
            'reviews' => $reviews,
            'isOwnProfile' => $request->user() && $request->user()->id === $user->id,
        ]);
    }
}
