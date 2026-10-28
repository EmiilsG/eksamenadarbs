<?php

namespace App\Providers;

use App\Models\Message;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $key = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(3)->by($key);
        });

        RateLimiter::for('messages', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('reviews', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('*', function ($view) {
            $view->with('unreadMessagesCount', $this->unreadMessagesCount());
        });
    }

    protected function unreadMessagesCount(): int
    {
        if (! Auth::check()) {
            return 0;
        }

        $request = request();

        if ($request->attributes->has('unreadMessagesCount')) {
            return $request->attributes->get('unreadMessagesCount');
        }

        $userId = Auth::id();

        $count = Message::query()
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->whereHas('conversation', function ($query) use ($userId) {
                $query->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId);
            })
            ->count();

        $request->attributes->set('unreadMessagesCount', $count);

        return $count;
    }
}
