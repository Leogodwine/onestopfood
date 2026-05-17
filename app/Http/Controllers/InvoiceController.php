<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function showByOrder(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $order->load('payment');
        $invoice = $order->invoice;
        if (! $invoice && $order->payment) {
            $invoice = $this->invoiceService->createForOrder($order, $order->payment);
        }
        if (! $invoice) {
            abort(404, 'Invoice not found for this order.');
        }

        return $this->renderInvoice($invoice, false);
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->authorizeOrder($request, $invoice->order);

        return $this->renderInvoice($invoice, false);
    }

    public function print(Request $request, Invoice $invoice)
    {
        $this->authorizeOrder($request, $invoice->order);

        return $this->renderInvoice($invoice, true);
    }

    private function renderInvoice(Invoice $invoice, bool $forPrint)
    {
        $invoice->load([
            'order.customer',
            'order.items.meal',
            'order.chef',
            'order.orderChefs.chef',
            'order.payment',
            'order.deliveryLocation',
        ]);

        $invoice->syncFromPayment();

        return view($forPrint ? 'invoices.print' : 'invoices.show', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ]);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();
        $isCustomer = (int) $order->customer_id === (int) $user->id;
        $isChef = (int) $order->chef_id === (int) $user->id
            || $order->orderChefs()->where('chef_id', $user->id)->exists();
        $isTraveler = $order->delivery && (int) $order->delivery->traveler_id === (int) $user->id;

        if ($user->role !== User::ROLE_ADMIN && ! $isCustomer && ! $isChef && ! $isTraveler) {
            abort(403);
        }
    }
}
