<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $quotations = Quotation::with(['client', 'lead', 'createdBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(15);
        return view('quotations.index', compact('quotations'));
    }
    public function create(Request $request)
    {
        $clients = Client::where('status', 'active')->get();
        $lead = $request->lead_id ? Lead::find($request->lead_id) : null;
        $prefix = Setting::get('quotation_prefix', 'QUO');
        $number = $prefix . '-' . now()->format('Ymd') . '-' . str_pad(Quotation::count() + 1, 4, '0', STR_PAD_LEFT);
        return view('quotations.create', compact('clients', 'lead', 'number'));
    }
    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255', 'client_id' => 'nullable|exists:clients,id', 'quotation_number' => 'required|unique:quotations', 'total' => 'required|numeric']);
        $quotation = Quotation::create(array_merge($request->except('_token'), ['created_by' => auth()->id(), 'status' => 'draft']));
        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation created!');
    }
    public function show(Quotation $quotation) { $quotation->load(['client', 'lead', 'createdBy']); return view('quotations.show', compact('quotation')); }
    public function edit(Quotation $quotation) { $clients = Client::where('status', 'active')->get(); return view('quotations.edit', compact('quotation', 'clients')); }
    public function update(Request $request, Quotation $quotation) { $quotation->update($request->except(['_token','_method'])); return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation updated!'); }
    public function destroy(Quotation $quotation) { $quotation->delete(); return redirect()->route('quotations.index')->with('success', 'Quotation deleted.'); }
    public function pdf(Quotation $quotation) { $pdf = Pdf::loadView('quotations.pdf', compact('quotation')); return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf'); }
    public function convertToProject(Request $request, Quotation $quotation)
    {
        $quotation->update(['status' => 'accepted']);
        return redirect()->route('projects.create', ['quotation_id' => $quotation->id])->with('success', 'Quotation accepted! Create the project now.');
    }
}
