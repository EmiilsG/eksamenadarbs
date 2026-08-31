<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, User $user)
    {
        abort_unless($user, 404);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['rating' => 'Jūs nevarat novērtēt pats sevi.']);
        }

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|max:1000',
        ]);

        Review::create([
            'reviewee_id' => $user->id,
            'reviewer_id' => $request->user()->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $user->rating = Review::where('reviewee_id', $user->id)->avg('rating');
        $user->save();

        return back()->with('success', 'Atsauksme veiksmīgi pievienota!');
    }
}
