<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Client;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with(['client', 'project'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->client, fn($q) => $q->where('client_id', $request->client))
            ->latest()->paginate(20);
        $clients = Client::where('status', 'active')->get();
        return view('invoices.index', compact('invoices', 'clients'));
    }
    public function create()
    {
        $projects = Project::whereNotIn('status', ['cancelled'])->with('client')->get();
        $clients  = Client::where('status', 'active')->get();
        $prefix   = Setting::get('invoice_prefix', 'INV');
        $number   = $prefix . '-' . now()->format('Ymd') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
        $tax      = Setting::get('invoice_tax', 18);
        return view('invoices.create', compact('projects', 'clients', 'number', 'tax'));
    }
    public function store(Request $request)
    {
        $request->validate(['invoice_number' => 'required|unique:invoices', 'client_id' => 'required|exists:clients,id', 'total' => 'required|numeric|min:0']);
        $invoice = Invoice::create(array_merge($request->except(['_token']), ['created_by' => auth()->id(), 'status' => 'pending', 'balance_amount' => $request->total]));
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created!');
    }
    public function show(Invoice $invoice) { $invoice->load(['client', 'project', 'payments']); return view('invoices.show', compact('invoice')); }
    public function edit(Invoice $invoice) { $projects = Project::all(); $clients = Client::where('status','active')->get(); return view('invoices.edit', compact('invoice', 'projects', 'clients')); }
    public function update(Request $request, Invoice $invoice) { $invoice->update($request->except(['_token','_method'])); return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated!'); }
    public function destroy(Invoice $invoice) { $invoice->delete(); return redirect()->route('invoices.index')->with('success', 'Invoice deleted.'); }
    public function pdf(Invoice $invoice) { $invoice->load(['client', 'project', 'payments']); $pdf = Pdf::loadView('invoices.pdf', compact('invoice')); return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf'); }
    public function send(Request $request, Invoice $invoice)
    {
        // TODO: Send email with PDF
        $invoice->update(['status' => 'sent']);
        return back()->with('success', 'Invoice sent to client!');
    }
}
