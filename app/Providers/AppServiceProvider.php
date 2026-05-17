<?php

namespace App\Providers;

use App\Models\Meal;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Observers\PaymentObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('vendor.pagination.bootstrap-5-compact');

        Payment::observe(PaymentObserver::class);

        if (Schema::hasTable('system_settings')) {
            View::share('siteName', Cache::remember(
                'settings.site_name',
                3600,
                fn () => SystemSetting::getValue('site_name', 'One Stop')
            ));
            View::share('currencyCode', Cache::remember(
                'settings.currency',
                3600,
                fn () => SystemSetting::getValue('currency', 'TZS')
            ));
            View::share('supportPhone', Cache::remember(
                'settings.support_phone',
                3600,
                fn () => SystemSetting::getValue('support_phone', '+255626725383')
            ));
        }

        View::composer(['layout', 'layouts.dashboard'], function ($view) {
            $cart = session('cart', []);
            $cartItems = [];
            $cartSubtotal = 0;

            if (!empty($cart) && is_array($cart)) {
                $meals = Meal::query()
                    ->whereIn('id', array_keys($cart))
                    ->with('chef')
                    ->get()
                    ->keyBy('id');

                foreach ($cart as $mealId => $qty) {
                    $meal = $meals->get((int) $mealId);
                    if (!$meal) {
                        continue;
                    }
                    $line = $meal->price * (int) $qty;
                    $cartSubtotal += $line;
                    $cartItems[] = [
                        'meal' => $meal,
                        'quantity' => (int) $qty,
                        'line_total' => $line,
                    ];
                }
            }

            $view->with(compact('cartItems', 'cartSubtotal'));
        });
    }
}
