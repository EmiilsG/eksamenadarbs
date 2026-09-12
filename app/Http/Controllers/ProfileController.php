<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function edit(Request $request)
    {
        return view('profile.edit', ['profileUser' => $request->user()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && !str_starts_with($user->profile_image, 'http')) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->profile_image = $request->file('profile_image')->store('profiles', 'public');
        }

        $user->name = $request->name;
        $user->bio = $request->bio;

        $user->save();

        return redirect()->route('profile')->with('success', 'Profils veiksmīgi atjaunināts!');
    }
}
