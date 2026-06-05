<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ChefController;
use App\Http\Controllers\ChefOrderController;
use App\Http\Controllers\ChefLogisticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\MobileMoneyPaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TravelerController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminFinanceController;
use App\Http\Controllers\AdminLogisticsController;
use App\Http\Controllers\AdminDisputeController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\AdminConfigController;
use App\Http\Controllers\AdminSystemController;
use App\Http\Controllers\AdminSecurityController;
use App\Http\Controllers\AdminBackupController;
use App\Http\Controllers\AdminZoneController;
use App\Http\Controllers\AdminMealController;
use App\Http\Controllers\AdminInvoiceController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\PartnerApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialSignupController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\DocumentFileController;

Route::post('/payments/mpesa/callback', [MobileMoneyPaymentController::class, 'mpesaCallback'])
    ->name('payments.mpesa.callback');
Route::post('/payments/tigo/callback', [MobileMoneyPaymentController::class, 'tigoCallback'])
    ->name('payments.tigo.callback');
Route::post('/payments/airtel/callback', [MobileMoneyPaymentController::class, 'airtelCallback'])
    ->name('payments.airtel.callback');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/currency/{currency}', [CurrencyController::class, 'switch'])->name('currency.switch');

Route::get('/meals', [MealController::class, 'index'])->name('meals.index');
Route::get('/meals/{meal}/image', [MealController::class, 'image'])->name('meals.image');
Route::get('/chefs', [ChefController::class, 'index'])->name('chefs.index');
Route::get('/chefs/{chef}', [ChefController::class, 'show'])->name('chefs.show');
Route::get('/users/{user}/avatar', [ProfileController::class, 'userAvatar'])->name('users.avatar');
Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');

Route::get('/docs/user-manual', [DocumentationController::class, 'userManual'])->name('docs.user-manual');
Route::get('/docs/guidelines', [DocumentationController::class, 'guidelines'])->name('docs.guidelines');

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/login/2fa', [AuthController::class, 'showTwoFactorForm'])->name('login.2fa.show');
    Route::post('/login/2fa', [AuthController::class, 'verifyTwoFactor'])->name('login.2fa.verify');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/auth/google', [SocialAuthController::class, 'redirectGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'callbackGoogle'])->name('auth.google.callback');
    Route::get('/auth/facebook', [SocialAuthController::class, 'redirectFacebook'])->name('auth.facebook.redirect');
    Route::get('/auth/facebook/callback', [SocialAuthController::class, 'callbackFacebook'])->name('auth.facebook.callback');

    Route::get('/auth/complete-signup', [SocialSignupController::class, 'showComplete'])->name('social.signup.complete');
    Route::post('/auth/complete-signup/send-otp', [SocialSignupController::class, 'sendOtp'])->name('social.signup.send-otp');
    Route::post('/auth/complete-signup/verify-otp', [SocialSignupController::class, 'verifyOtp'])->name('social.signup.verify-otp');
    Route::post('/auth/complete-signup/resend-otp', [SocialSignupController::class, 'resendOtp'])->name('social.signup.resend-otp');
});

// Password Reset (token step — separate limit)
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Cart: guests can add/view; checkout requires auth
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{meal}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove/{meal}', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware(['auth', 'social.signup.complete'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');

    Route::post('/partner/apply', [PartnerApplicationController::class, 'apply'])->name('partner.apply');


    // Impersonation: admin can stop impersonating
    Route::post('/impersonation/stop', [AdminController::class, 'stopImpersonating'])->name('impersonation.stop');

    // User Verification Workflow (Chefs & Travelers)
    Route::get('/verify', [VerificationController::class, 'showVerificationForm'])->name('verification.show');
    Route::post('/verify', [VerificationController::class, 'submitVerificationForm'])->name('verification.submit');
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/verifications/{document}', [DocumentFileController::class, 'verification'])
            ->name('verifications.show');
        Route::get('/users/{user}/profiles/{field}', [DocumentFileController::class, 'profile'])
            ->where('field', '[a-z0-9\-]+')
            ->name('profiles.show');
    });

    Route::get('/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::post('/checkout/delivery', [OrderController::class, 'storeDeliveryStep'])->name('orders.checkout.delivery');
    Route::post('/checkout/payment', [OrderController::class, 'storePaymentStep'])->name('orders.checkout.payment');
    Route::post('/orders', [OrderController::class, 'place'])->name('orders.place');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'showByOrder'])->name('orders.invoice');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    Route::get('/billing', fn () => redirect()->route('invoices.index'))->name('billing.index');
    Route::post('/orders/{order}/pay/mobile', [MobileMoneyPaymentController::class, 'initiate'])
        ->middleware('throttle:5,1')
        ->name('orders.pay.mobile');
    Route::post('/orders/{order}/pay/mpesa', [MobileMoneyPaymentController::class, 'initiate'])
        ->middleware('throttle:5,1')
        ->name('orders.pay.mpesa');

    Route::get('/my-orders', [CustomerOrderController::class, 'index'])->name('customer.orders');

    Route::prefix('/locations')->name('locations.')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('index');
        Route::get('/create', [LocationController::class, 'create'])->name('create');
        Route::post('/', [LocationController::class, 'store'])->name('store');
        Route::get('/{location}/edit', [LocationController::class, 'edit'])->name('edit');
        Route::put('/{location}', [LocationController::class, 'update'])->name('update');
        Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
        Route::post('/{location}/set-primary', [LocationController::class, 'setPrimary'])->name('set-primary');
    });

    Route::get('/orders/{order}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');

    Route::middleware('role:admin')->prefix('/admin')->name('admin.')->group(function () {
        Route::middleware('admin.permission:users.view')->group(function () {
            Route::get('/users', [AdminController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
            Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        });

        Route::middleware('admin.permission:users.create')->group(function () {
            Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        });

        Route::middleware('admin.permission:users.export')->group(function () {
            Route::get('/users/export', [AdminController::class, 'export'])->name('users.export');
        });

        Route::middleware('admin.permission:users.manage')->group(function () {
            Route::post('/users/bulk', [AdminController::class, 'bulkUpdate'])->name('users.bulk');
            Route::post('/users/{user}/suspend', [AdminController::class, 'suspend'])->name('users.suspend');
        });

        Route::middleware('admin.permission:users.approve')->group(function () {
            Route::post('/users/{user}/approve', [AdminController::class, 'approve'])->name('users.approve');
            Route::post('/users/{user}/reject', [AdminController::class, 'reject'])->name('users.reject');
            Route::post('/users/{user}/unsuspend', [AdminController::class, 'unsuspend'])->name('users.unsuspend');
        });

        Route::middleware('admin.permission:users.impersonate')->group(function () {
            Route::post('/users/{user}/impersonate', [AdminController::class, 'impersonate'])->name('users.impersonate');
        });

        Route::middleware('admin.permission:meals')->group(function () {
            Route::get('/meals', [AdminMealController::class, 'index'])->name('meals.index');
        });

        Route::middleware('admin.permission:invoices')->group(function () {
            Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/billing', fn () => redirect()->route('admin.invoices.index'))->name('billing.index');
        });

        Route::middleware('admin.permission:verifications')->group(function () {
            Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('verifications.index');
            Route::post('/verifications/{document}/approve', [AdminVerificationController::class, 'approve'])->name('verifications.approve');
            Route::post('/verifications/{document}/reject', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
            Route::post('/verifications/{document}/request-more', [AdminVerificationController::class, 'requestMore'])->name('verifications.request-more');
        });

        Route::middleware('admin.permission:orders')->group(function () {
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
            Route::post('/orders/{order}/reassign-traveler', [AdminOrderController::class, 'reassignTraveler'])->name('orders.reassign-traveler');
        });

        Route::middleware('admin.permission:finance')->group(function () {
            Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance.index');
        });

        Route::middleware('admin.permission:finance.refund')->group(function () {
            Route::post('/payments/{payment}/refund', [AdminFinanceController::class, 'refund'])->name('payments.refund');
        });

        Route::middleware('admin.permission:logistics')->group(function () {
            Route::get('/logistics', [AdminLogisticsController::class, 'index'])->name('logistics.index');
        });

        Route::middleware('admin.permission:disputes')->group(function () {
            Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
            Route::get('/disputes/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
            Route::post('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');
        });

        Route::middleware('admin.permission:notifications')->group(function () {
            Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        });

        Route::middleware('admin.permission:analytics')->group(function () {
            Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
            Route::get('/analytics/delivery-locations', [AdminAnalyticsController::class, 'deliveryLocations'])->name('analytics.delivery-locations');
        });

        Route::middleware('admin.permission:config')->group(function () {
            Route::get('/config', [AdminConfigController::class, 'index'])->name('config.index');
            Route::post('/config', [AdminConfigController::class, 'update'])->name('config.update');
        });

        Route::middleware('admin.permission:zones')->group(function () {
            Route::get('/zones', [AdminZoneController::class, 'index'])->name('zones.index');
            Route::post('/zones', [AdminZoneController::class, 'store'])->name('zones.store');
        });

        Route::middleware('admin.permission:system.monitor')->group(function () {
            Route::get('/system', [AdminSystemController::class, 'index'])->name('system.index');
            Route::post('/system/maintenance', [AdminSystemController::class, 'maintenance'])->name('system.maintenance');
            Route::post('/system/tasks', [AdminSystemController::class, 'runTask'])->name('system.tasks');
        });

        Route::middleware('admin.permission:system.security')->group(function () {
            Route::get('/security', [AdminSecurityController::class, 'index'])->name('security.index');
            Route::post('/security/settings', [AdminSecurityController::class, 'updateSettings'])->name('security.settings');
            Route::post('/security/users/{user}/block', [AdminSecurityController::class, 'blockUser'])->name('security.block');
            Route::post('/security/users/{user}/reset-login', [AdminSecurityController::class, 'resetLoginAttempts'])->name('security.reset-login');
        });

        Route::middleware('admin.permission:system.backups')->group(function () {
            Route::get('/backups', [AdminBackupController::class, 'index'])->name('backups.index');
            Route::post('/backups', [AdminBackupController::class, 'store'])->name('backups.store');
            Route::post('/backups/schedule', [AdminBackupController::class, 'updateSchedule'])->name('backups.schedule');
            Route::post('/backups/{backup}/restore', [AdminBackupController::class, 'restore'])->name('backups.restore');
            Route::delete('/backups/{backup}', [AdminBackupController::class, 'destroy'])->name('backups.destroy');
            Route::get('/backups/{backup}/download', [AdminBackupController::class, 'download'])->name('backups.download');
        });
    });

    Route::middleware('role:chef')->prefix('/chef')->name('chef.')->group(function () {
        Route::get('/meals', [MealController::class, 'chefIndex'])->name('meals.index');
        Route::get('/meals/create', [MealController::class, 'create'])->name('meals.create');
        Route::post('/meals', [MealController::class, 'store'])->name('meals.store');
        Route::get('/meals/{meal}', [MealController::class, 'chefShow'])->name('meals.show');
        Route::get('/meals/{meal}/edit', [MealController::class, 'edit'])->name('meals.edit');
        Route::put('/meals/{meal}', [MealController::class, 'update'])->name('meals.update');
        Route::post('/meals/{meal}/toggle-availability', [MealController::class, 'toggleAvailability'])->name('meals.toggle-availability');
        Route::delete('/meals/{meal}', [MealController::class, 'destroy'])->name('meals.destroy');

        Route::get('/orders', [ChefOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [ChefOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/accept', [ChefOrderController::class, 'accept'])->name('orders.accept');
        Route::post('/orders/{order}/reject', [ChefOrderController::class, 'reject'])->name('orders.reject');
        Route::post('/orders/{order}/status', [ChefOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/assign-traveler', [ChefOrderController::class, 'assignTraveler'])->name('orders.assign-traveler');

        Route::get('/logistics', [ChefLogisticsController::class, 'index'])->name('logistics.index');

        Route::get('/earnings', [EarningsController::class, 'chefEarnings'])->name('earnings');
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    });

    Route::middleware('role:traveler')->prefix('/traveler')->name('traveler.')->group(function () {
        Route::post('/toggle-online', [TravelerController::class, 'toggleOnline'])->name('toggle-online');
        Route::get('/deliveries', [TravelerController::class, 'deliveries'])->name('deliveries');
        Route::post('/deliveries/{delivery}/accept', [TravelerController::class, 'acceptDelivery'])->name('deliveries.accept');
        Route::post('/deliveries/{delivery}/status', [TravelerController::class, 'updateDeliveryStatus'])->name('deliveries.update-status');
        Route::post('/location', [TravelerController::class, 'updateLocation'])->name('location.update');

        Route::get('/earnings', [EarningsController::class, 'travelerEarnings'])->name('earnings');
    });
});
