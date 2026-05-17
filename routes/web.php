<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ChefController;
use App\Http\Controllers\ChefOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
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
use App\Http\Controllers\AdminZoneController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationController;

Route::post('/payments/mpesa/callback', [MobileMoneyPaymentController::class, 'mpesaCallback'])
    ->name('payments.mpesa.callback');
Route::post('/payments/tigo/callback', [MobileMoneyPaymentController::class, 'tigoCallback'])
    ->name('payments.tigo.callback');
Route::post('/payments/airtel/callback', [MobileMoneyPaymentController::class, 'airtelCallback'])
    ->name('payments.airtel.callback');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/meals', [MealController::class, 'index'])->name('meals.index');
Route::get('/chefs', [ChefController::class, 'index'])->name('chefs.index');
Route::get('/chefs/{chef}', [ChefController::class, 'show'])->name('chefs.show');
Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');

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

// Password Reset (token step — separate limit)
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Cart: guests can add/view; checkout requires auth
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{meal}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove/{meal}', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');


    // Impersonation: admin can stop impersonating
    Route::post('/impersonation/stop', [AdminController::class, 'stopImpersonating'])->name('impersonation.stop');

    // User Verification Workflow (Chefs & Travelers)
    Route::get('/verify', [VerificationController::class, 'showVerificationForm'])->name('verification.show');
    Route::post('/verify', [VerificationController::class, 'submitVerificationForm'])->name('verification.submit');


    Route::get('/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::post('/checkout/delivery', [OrderController::class, 'storeDeliveryStep'])->name('orders.checkout.delivery');
    Route::post('/checkout/payment', [OrderController::class, 'storePaymentStep'])->name('orders.checkout.payment');
    Route::post('/orders', [OrderController::class, 'place'])->name('orders.place');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'showByOrder'])->name('orders.invoice');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
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
        Route::get('/users', [AdminController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::post('/users/{user}/approve', [AdminController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject', [AdminController::class, 'reject'])->name('users.reject');
        Route::post('/users/{user}/suspend', [AdminController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/unsuspend', [AdminController::class, 'unsuspend'])->name('users.unsuspend');
        Route::post('/users/bulk', [AdminController::class, 'bulkUpdate'])->name('users.bulk');
        Route::get('/users/export', [AdminController::class, 'export'])->name('users.export');
        Route::post('/users/{user}/impersonate', [AdminController::class, 'impersonate'])->name('users.impersonate');

        // FR-ADMIN-03: Verification workflow
        Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('verifications.index');
        Route::post('/verifications/{document}/approve', [AdminVerificationController::class, 'approve'])->name('verifications.approve');
        Route::post('/verifications/{document}/reject', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
        Route::post('/verifications/{document}/request-more', [AdminVerificationController::class, 'requestMore'])->name('verifications.request-more');

        // FR-ADMIN-05: Order monitoring
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/reassign-traveler', [AdminOrderController::class, 'reassignTraveler'])->name('orders.reassign-traveler');

        // FR-ADMIN-06: Finance
        Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance.index');
        Route::post('/payments/{payment}/refund', [AdminFinanceController::class, 'refund'])->name('payments.refund');

        // FR-ADMIN-07: Logistics
        Route::get('/logistics', [AdminLogisticsController::class, 'index'])->name('logistics.index');

        // FR-ADMIN-08: Disputes
        Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
        Route::post('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');

        // FR-ADMIN-09: Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');

        // FR-ADMIN-10 & 12: Analytics & performance
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/delivery-locations', [AdminAnalyticsController::class, 'deliveryLocations'])->name('analytics.delivery-locations');

        // FR-ADMIN-11: Configuration
        Route::get('/config', [AdminConfigController::class, 'index'])->name('config.index');
        Route::post('/config', [AdminConfigController::class, 'update'])->name('config.update');

        // FR-ADMIN-13: Zones
        Route::get('/zones', [AdminZoneController::class, 'index'])->name('zones.index');
        Route::post('/zones', [AdminZoneController::class, 'store'])->name('zones.store');
    });

    Route::middleware('role:chef')->prefix('/chef')->name('chef.')->group(function () {
        Route::get('/meals', [MealController::class, 'chefIndex'])->name('meals.index');
        Route::get('/meals/create', [MealController::class, 'create'])->name('meals.create');
        Route::post('/meals', [MealController::class, 'store'])->name('meals.store');

        Route::get('/orders', [ChefOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [ChefOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/accept', [ChefOrderController::class, 'accept'])->name('orders.accept');
        Route::post('/orders/{order}/reject', [ChefOrderController::class, 'reject'])->name('orders.reject');
        Route::post('/orders/{order}/status', [ChefOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/orders/{order}/assign-traveler', [ChefOrderController::class, 'assignTraveler'])->name('orders.assign-traveler');

        Route::get('/earnings', [EarningsController::class, 'chefEarnings'])->name('earnings');
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
