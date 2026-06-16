<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::when($request->search, fn($q) => $q->where('company_name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(15);
        return view('clients.index', compact('clients'));
    }
    public function create() { return view('clients.create'); }
    public function store(Request $request)
    {
        $request->validate(['company_name' => 'required|string|max:255', 'email' => 'required|email|unique:clients,email']);
        $client = Client::create($request->only(['company_name','contact_person','email','phone','mobile','address','city','state','country','pincode','gst_number','website','notes','status']));
        return redirect()->route('clients.show', $client)->with('success', 'Client added!');
    }
    public function show(Client $client) {
        $client->load(['projects', 'invoices', 'payments']);
        return view('clients.show', compact('client'));
    }
    public function edit(Client $client) { return view('clients.edit', compact('client')); }
    public function update(Request $request, Client $client) {
        $client->update($request->only(['company_name','contact_person','email','phone','mobile','address','city','state','country','pincode','gst_number','website','notes','status']));
        return redirect()->route('clients.show', $client)->with('success', 'Client updated!');
    }
    public function quickStore(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $client = Client::create([
            'company_name' => $request->company_name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'client' => $client
        ]);
    }
    public function destroy(Client $client) { $client->delete(); return redirect()->route('clients.index')->with('success', 'Client deleted.'); }
}
