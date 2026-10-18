<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Review;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $products = $user->products()->get();

        $totalProducts = $products->count();
        $totalViews = (int) $products->sum('views_count');
        $totalValue = (float) $products->sum('price');
        $averagePrice = $totalProducts > 0 ? $totalValue / $totalProducts : 0;

        $totalFavorites = Favorite::whereIn('product_id', $products->pluck('id'))->count();

        $reviewsCount = $user->reviews_count;
        $averageRating = $user->average_rating;

        $ratingDistribution = $user->reviews()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $categoryBreakdown = $products
            ->groupBy(fn ($product) => $product->category ?: 'Cita')
            ->map(fn ($items, $name) => [
                'name' => $name,
                'count' => $items->count(),
                'views' => (int) $items->sum('views_count'),
            ])
            ->sortByDesc('count')
            ->values();

        $topProducts = $user->products()
            ->withCount('favorites')
            ->orderByDesc('views_count')
            ->orderByDesc('favorites_count')
            ->limit(5)
            ->get();

        $maxTopViews = max(1, $topProducts->max('views_count'));

        $recentReviews = Review::with('reviewer')
            ->where('reviewee_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $months = collect(range(5, 0))
            ->map(fn ($offset) => now()->subMonths($offset));

        $createdByMonth = $products->groupBy(fn ($product) => $product->created_at->format('Y-m'));

        $monthlyActivity = $months->map(fn ($month) => [
            'label' => $month->format('m.Y'),
            'count' => $createdByMonth->get($month->format('Y-m'), collect())->count(),
        ])->values();

        $maxMonthly = max(1, $monthlyActivity->max('count'));

        $daysActive = max(1, (int) abs(now()->diffInDays($user->created_at)) + 1);

        return view('dashboard', [
            'totalProducts' => $totalProducts,
            'totalViews' => $totalViews,
            'totalFavorites' => $totalFavorites,
            'totalValue' => $totalValue,
            'averagePrice' => $averagePrice,
            'averageRating' => $averageRating,
            'reviewsCount' => $reviewsCount,
            'ratingDistribution' => $ratingDistribution,
            'categoryBreakdown' => $categoryBreakdown,
            'topProducts' => $topProducts,
            'maxTopViews' => $maxTopViews,
            'recentReviews' => $recentReviews,
            'monthlyActivity' => $monthlyActivity,
            'maxMonthly' => $maxMonthly,
            'viewsPerDay' => round($totalViews / $daysActive, 1),
            'daysActive' => $daysActive,
        ]);
    }
}
