<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoicePdfService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly InvoicePdfService $invoicePdfService,
    ) {}

    public function showByOrder(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $order->load(['payment', 'sharedPayment', 'invoice']);
        $payment = $order->effectivePayment();
        $invoice = $order->invoice;
        if (! $invoice && $payment) {
            $invoice = $this->invoiceService->createForOrder($order, $payment);
        }
        if (! $invoice) {
            abort(404, 'Invoice not found for this order.');
        }

        return $this->renderInvoice($invoice, false);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->route('admin.invoices.index');
        }

        $query = Invoice::query();

        if ($user->role === User::ROLE_CHEF) {
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('chef_id', $user->id)
                    ->orWhereHas('orderChefs', function ($sq) use ($user) {
                        $sq->where('chef_id', $user->id);
                    });
            });
        } elseif ($user->role === User::ROLE_TRAVELER) {
            $query->whereHas('order.delivery', function ($q) use ($user) {
                $q->where('traveler_id', $user->id);
            });
        } else {
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
        }

        $invoices = $query->with(['order.chef', 'order.customer'])
            ->latest('issued_at')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', [
            'invoices' => $invoices,
        ]);
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

    public function download(Request $request, Invoice $invoice)
    {
        $this->authorizeOrder($request, $invoice->order);

        $invoice->load([
            'order.customer',
            'order.items.meal',
            'order.chef',
            'order.orderChefs.chef',
            'order.payment',
            'order.sharedPayment',
            'order.deliveryLocation',
        ]);

        $invoice->syncFromPayment();

        $safeNumber = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) ($invoice->invoice_number ?: $invoice->id));
        $fileName = 'invoice_' . $safeNumber . '.pdf';

        return $this->invoicePdfService->download('invoices.pdf', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ], $fileName);
    }

    private function renderInvoice(Invoice $invoice, bool $forPrint)
    {
        $invoice->load([
            'order.customer',
            'order.items.meal',
            'order.chef',
            'order.orderChefs.chef',
            'order.payment',
            'order.sharedPayment',
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
