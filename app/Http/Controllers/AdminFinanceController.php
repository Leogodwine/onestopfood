<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminFinanceController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $from = $request->query('from');
        $to = $request->query('to');

        $paymentsQuery = Payment::with('order')->latest();

        if ($status !== '') {
            $paymentsQuery->where('status', $status);
        }

        if ($from) {
            $paymentsQuery->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $paymentsQuery->whereDate('created_at', '<=', $to);
        }

        $payments = $paymentsQuery->paginate(20)->withQueryString();

        $totalRevenue = Payment::where('status', 'paid')->sum('amount');
        $totalRefunded = Payment::where('status', 'refunded')->sum('amount');

        $ordersCount = Order::count();

        return view('admin.finance', [
            'payments' => $payments,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'totalRefunded' => $totalRefunded,
            'ordersCount' => $ordersCount,
        ]);
    }

    public function refund(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        // In a real system, this would call the payment gateway.
        $payment->status = 'refunded';
        $payment->save();

        return back()->with('status', 'Payment marked as refunded. Ensure this matches the payment gateway.');
    }
}

