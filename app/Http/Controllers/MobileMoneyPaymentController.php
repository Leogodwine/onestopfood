<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Airtel\AirtelMoneyService;
use App\Services\Mpesa\MpesaDarajaService;
use App\Services\Payments\MobileMoneyDispatcher;
use App\Services\Tigo\TigoPesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileMoneyPaymentController extends Controller
{
    public function __construct(
        private readonly MobileMoneyDispatcher $dispatcher,
        private readonly MpesaDarajaService $mpesa,
        private readonly TigoPesaService $tigo,
        private readonly AirtelMoneyService $airtel,
    ) {}

    public function mpesaCallback(Request $request)
    {
        Log::info('M-Pesa STK callback received', ['payload' => $request->all()]);

        try {
            $this->mpesa->handleCallback($request->all());
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function tigoCallback(Request $request)
    {
        Log::info('Tigo Pesa callback received', ['payload' => $request->all()]);

        try {
            $this->tigo->handleCallback($request->all());
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['status' => 'ok']);
    }

    public function airtelCallback(Request $request)
    {
        Log::info('Airtel Money callback received', ['payload' => $request->all()]);

        try {
            $this->airtel->handleCallback($request->all());
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['status' => 'success']);
    }

    public function initiate(Request $request, Order $order)
    {
        if ((int) $order->customer_id !== (int) $request->user()->id) {
            abort(403);
        }

        $payment = $order->payment;
        if (! $payment || ! $this->dispatcher->supports($payment->method)) {
            return back()->withErrors(['phone' => 'This order does not use a supported mobile money method.']);
        }

        if ($payment->status === 'paid') {
            return back()->with('status', 'This order is already paid.');
        }

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $result = $this->dispatcher->initiate($payment, $data['phone']);

        if (! $result['success']) {
            return back()->withErrors(['phone' => $result['message']]);
        }

        return back()->with('status', $result['message']);
    }
}
