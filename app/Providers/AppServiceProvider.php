<?php

namespace App\Providers;

use App\Support\PasswordRules;
use App\Models\Meal;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Observers\PaymentObserver;
use App\Services\AdminAccessService;
use App\Services\SocialAuthService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Password::defaults(fn () => PasswordRules::defaults());

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('vendor.pagination.bootstrap-5-compact');

        Payment::observe(PaymentObserver::class);

        $socialAuth = app(SocialAuthService::class);
        View::share('googleSignInEnabled', $socialAuth->isConfigured('google'));
        View::share('facebookSignInEnabled', $socialAuth->isConfigured('facebook'));

        View::composer('*', function ($view) {
            $user = auth()->user();

            if (! $user || $user->role !== \App\Models\User::ROLE_ADMIN) {
                $view->with('adminPermissions', []);
                $view->with('adminTitle', null);
                $view->with('adminTitleLabel', null);

                return;
            }

            $access = app(AdminAccessService::class);
            $title = $access->effectiveTitle($user);

            $view->with('adminPermissions', $access->permissionsMap($user));
            $view->with('adminTitle', $title);
            $view->with('adminTitleLabel', $access->titleLabel($title));
        });

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
                fn () => SystemSetting::getValue('support_phone', config('contacts.support_phone', '+255 651 490 677'))
            ));
            View::share('supportEmail', Cache::remember(
                'settings.support_email',
                3600,
                fn () => SystemSetting::getValue('support_email', config('contacts.support_email'))
            ));
            View::share('noreplyEmail', Cache::remember(
                'settings.noreply_email',
                3600,
                fn () => SystemSetting::getValue('noreply_email', config('contacts.noreply_email'))
            ));
        } else {
            View::share('supportPhone', config('contacts.support_phone', '+255 651 490 677'));
            View::share('supportEmail', config('contacts.support_email'));
            View::share('noreplyEmail', config('contacts.noreply_email'));
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
