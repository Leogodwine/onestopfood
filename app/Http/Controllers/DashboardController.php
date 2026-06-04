<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Meal;
use App\Models\Delivery;
use App\Services\AdminAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // Redirect pending, rejected, or suspended chefs/travelers to the pending status page
        if ($user->status !== User::STATUS_APPROVED && in_array($user->role, [User::ROLE_CHEF, User::ROLE_TRAVELER])) {
            return view('dashboard.pending');
        }

        $stats = [];

        switch ($user->role) {
            case User::ROLE_ADMIN:
                $stats = [
                    'total_users' => User::count(),
                    'total_chefs' => User::where('role', User::ROLE_CHEF)->count(),
                    'total_customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
                    'total_travelers' => User::where('role', User::ROLE_TRAVELER)->count(),
                    'pending_approvals' => User::whereIn('role', [User::ROLE_CHEF, User::ROLE_TRAVELER])
                        ->where('status', User::STATUS_PENDING)->count(),
                    'total_orders' => Order::count(),
                    'total_meals' => Meal::count(),
                    'recent_orders' => Order::with(['customer', 'chef.chefProfile'])->latest()->limit(10)->get()
                ];
                $access = app(AdminAccessService::class);
                $adminTitle = $access->effectiveTitle($user);

                return view('dashboard.admin', [
                    'stats' => $stats,
                    'adminTitle' => $adminTitle,
                    'adminTitleLabel' => $access->titleLabel($adminTitle),
                    'adminTitleDescription' => $access->titleDescription($adminTitle),
                    'adminPermissions' => $access->permissionsMap($user),
                ]);

            case User::ROLE_CHEF:
                $stats = [
                    'total_meals' => Meal::where('chef_id', $user->id)->count(),
                    'available_meals' => Meal::where('chef_id', $user->id)->where('is_available', true)->count(),
                    'total_orders' => Order::where('chef_id', $user->id)->count(),
                    'pending_orders' => Order::where('chef_id', $user->id)->where('status', 'pending')->count(),
                    'total_earnings' => Order::where('chef_id', $user->id)->where('status', 'delivered')->sum('subtotal'),
                    'recent_orders' => Order::where('chef_id', $user->id)->with('customer')->latest()->limit(5)->get()
                ];
                return view('dashboard.chef', compact('stats'));

            case User::ROLE_TRAVELER:
                $stats = [
                    'total_deliveries' => Delivery::where('traveler_id', $user->id)->count(),
                    'pending_deliveries' => Delivery::where('traveler_id', $user->id)->whereIn('status', ['assigned', 'picked_up'])->count(),
                    'completed_deliveries' => Delivery::where('traveler_id', $user->id)->where('status', 'delivered')->count(),
                    'total_earnings' => Delivery::where('traveler_id', $user->id)->where('status', 'delivered')->sum('traveler_earning')
                ];
                return view('dashboard.traveler', compact('stats'));

            case User::ROLE_CUSTOMER:
            default:
                $stats = [
                    'total_orders' => Order::where('customer_id', $user->id)->count(),
                    'pending_orders' => Order::where('customer_id', $user->id)->whereNotIn('status', ['delivered', 'cancelled'])->count(),
                    'completed_orders' => Order::where('customer_id', $user->id)->where('status', 'delivered')->count(),
                    'recent_orders' => Order::where('customer_id', $user->id)
                        ->with(['items.meal', 'chef', 'payment', 'delivery.traveler'])
                        ->latest()->limit(5)->get()
                ];
                return view('dashboard.customer', compact('stats'));
        }
    }
}
