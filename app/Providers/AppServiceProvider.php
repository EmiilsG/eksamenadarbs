<?php

namespace App\Providers;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('*', function ($view) {
            $view->with('unreadMessagesCount', $this->unreadMessagesCount());
        });
    }

    protected function unreadMessagesCount(): int
    {
        if (!Auth::check()) {
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
