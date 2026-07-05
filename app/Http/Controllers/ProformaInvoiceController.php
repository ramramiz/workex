<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProformaInvoice;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Client;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ProformaInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $proformas = ProformaInvoice::with(['client', 'project'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->client, fn($q) => $q->where('client_id', $request->client))
            ->latest()->paginate(20);
        $clients = Client::where('status', 'active')->get();
        return view('proforma_invoices.index', compact('proformas', 'clients'));
    }

    public function create()
    {
        $projects = Project::whereNotIn('status', ['cancelled'])->with('client')->get();
        $clients  = Client::where('status', 'active')->get();
        $prefix   = Setting::get('proforma_prefix', 'PRO');
        $number   = $prefix . '-' . now()->format('Ymd') . '-' . str_pad(ProformaInvoice::count() + 1, 4, '0', STR_PAD_LEFT);
        $tax      = Setting::get('invoice_tax', 18);
        return view('proforma_invoices.create', compact('projects', 'clients', 'number', 'tax'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proforma_number' => 'required|unique:proforma_invoices',
            'client_id' => 'required|exists:clients,id',
            'total' => 'required|numeric|min:0'
        ]);

        $proforma = ProformaInvoice::create(array_merge(
            $request->except(['_token']),
            ['created_by' => auth()->id(), 'status' => 'draft']
        ));

        return redirect()->route('proforma-invoices.show', $proforma)->with('success', 'Proforma Invoice created!');
    }

    public function show(ProformaInvoice $proformaInvoice)
    {
        $proformaInvoice->load(['client', 'project', 'createdBy', 'convertedInvoice']);
        return view('proforma_invoices.show', compact('proformaInvoice'));
    }

    public function edit(ProformaInvoice $proformaInvoice)
    {
        $projects = Project::all();
        $clients = Client::where('status', 'active')->get();
        return view('proforma_invoices.edit', compact('proformaInvoice', 'projects', 'clients'));
    }

    public function update(Request $request, ProformaInvoice $proformaInvoice)
    {
        $proformaInvoice->update($request->except(['_token', '_method']));
        return redirect()->route('proforma-invoices.show', $proformaInvoice)->with('success', 'Proforma Invoice updated!');
    }

    public function destroy(ProformaInvoice $proformaInvoice)
    {
        $proformaInvoice->delete();
        return redirect()->route('proforma-invoices.index')->with('success', 'Proforma Invoice deleted.');
    }

    public function pdf(ProformaInvoice $proformaInvoice)
    {
        $proformaInvoice->load(['client', 'project']);
        $pdf = Pdf::loadView('proforma_invoices.pdf', compact('proformaInvoice'));
        return $pdf->download('proforma-' . $proformaInvoice->proforma_number . '.pdf');
    }

    public function send(Request $request, ProformaInvoice $proformaInvoice)
    {
        $proformaInvoice->update(['status' => 'sent']);
        return back()->with('success', 'Proforma Invoice marked as sent!');
    }

    public function convertToInvoice(ProformaInvoice $proformaInvoice)
    {
        if ($proformaInvoice->status === 'converted' || $proformaInvoice->converted_invoice_id) {
            return back()->with('error', 'This Proforma Invoice has already been converted to an invoice.');
        }

        return DB::transaction(function () use ($proformaInvoice) {
            $prefix = Setting::get('invoice_prefix', 'INV');
            $invoiceNumber = $prefix . '-' . now()->format('Ymd') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create standard Invoice
            $invoice = Invoice::create([
                'company_id' => $proformaInvoice->company_id,
                'project_id' => $proformaInvoice->project_id,
                'client_id' => $proformaInvoice->client_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => $proformaInvoice->due_date ? $proformaInvoice->due_date->format('Y-m-d') : now()->addDays(15)->format('Y-m-d'),
                'items' => $proformaInvoice->items,
                'subtotal' => $proformaInvoice->subtotal,
                'tax_percentage' => $proformaInvoice->tax_percentage,
                'tax_amount' => $proformaInvoice->tax_amount,
                'discount' => $proformaInvoice->discount,
                'total' => $proformaInvoice->total,
                'paid_amount' => 0.00,
                'balance_amount' => $proformaInvoice->total,
                'status' => 'pending',
                'notes' => $proformaInvoice->notes,
                'created_by' => auth()->id()
            ]);

            // Update Proforma status and link
            $proformaInvoice->update([
                'status' => 'converted',
                'converted_invoice_id' => $invoice->id
            ]);

            return redirect()->route('invoices.show', $invoice)->with('success', 'Proforma Invoice successfully converted to Tax Invoice!');
        });
    }
}
