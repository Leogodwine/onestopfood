<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $status = (string) $request->query('status', ''); // paid | pending | failed | refunded
        $search = (string) $request->query('search', '');

        $query = Invoice::query()
            ->with(['order.customer', 'order.chef'])
            ->latest('issued_at');

        if ($status !== '') {
            $query->where('payment_status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('order_id', 'like', '%' . $search . '%');
            });
        }

        $invoices = $query->paginate($perPage)->withQueryString();

        return view('admin.invoices', [
            'invoices' => $invoices,
            'status' => $status,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }
}

