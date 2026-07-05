<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Client;

use App\Models\Bank;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['client', 'invoice', 'recordedBy'])
            ->when($request->client, fn($q) => $q->where('client_id', $request->client))
            ->latest()->paginate(20);
        $clients = Client::where('status', 'active')->get();
        $banks = Bank::all();
        return view('payments.index', compact('payments', 'clients', 'banks'));
    }
    public function create(Request $request)
    {
        $invoices = Invoice::with('client')->whereIn('status', ['sent','pending','partially_paid'])->get();
        $selectedInvoice = $request->invoice_id ? Invoice::with('client')->find($request->invoice_id) : null;
        $banks = Bank::where('status', 'active')->get();
        return view('payments.create', compact('invoices', 'selectedInvoice', 'banks'));
    }
    public function store(Request $request)
    {
        $request->validate(['invoice_id' => 'required|exists:invoices,id', 'amount' => 'required|numeric|min:1', 'payment_date' => 'required|date', 'payment_mode' => 'required|string']);
        $invoice = Invoice::find($request->invoice_id);
        $payment = Payment::create(array_merge($request->only(['invoice_id','amount','payment_date','payment_mode','transaction_id','notes','attachment']), ['client_id' => $invoice->client_id, 'project_id' => $invoice->project_id, 'recorded_by' => auth()->id(), 'payment_reference' => 'PAY-' . strtoupper(uniqid())]));
        // Update invoice
        $totalPaid = $invoice->payments()->sum('amount');
        $balance   = $invoice->total - $totalPaid;
        $invoice->update(['paid_amount' => $totalPaid, 'balance_amount' => max(0, $balance), 'status' => $balance <= 0 ? 'paid' : 'partially_paid']);
        return redirect()->route('payments.index')->with('success', 'Payment recorded! Balance: ₹' . number_format(max(0, $balance), 2));
    }
    public function show(Payment $payment) { $payment->load(['client', 'invoice', 'recordedBy']); return view('payments.show', compact('payment')); }
    public function destroy(Payment $payment) { $payment->delete(); return redirect()->route('payments.index')->with('success', 'Payment deleted.'); }
}
