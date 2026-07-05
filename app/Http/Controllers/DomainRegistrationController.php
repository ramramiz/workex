<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DomainRegistration;

class DomainRegistrationController extends Controller
{
    private function checkAccess()
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdminOrAbove() && !$user->isTeamLeader())) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->checkAccess();
        $domainRegistrations = DomainRegistration::latest()->paginate(15);
        return view('domain_registrations.index', compact('domainRegistrations'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'renewal_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DomainRegistration::create([
            'name' => $request->name,
            'url' => $request->url,
            'username' => $request->username,
            'password' => $request->password,
            'renewal_date' => $request->renewal_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('domain-registrations.index')->with('success', 'Domain Registration created successfully!');
    }

    public function edit(DomainRegistration $domainRegistration)
    {
        $this->checkAccess();
        return response()->json($domainRegistration);
    }

    public function update(Request $request, DomainRegistration $domainRegistration)
    {
        $this->checkAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'renewal_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $domainRegistration->update([
            'name' => $request->name,
            'url' => $request->url,
            'username' => $request->username,
            'password' => $request->password,
            'renewal_date' => $request->renewal_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('domain-registrations.index')->with('success', 'Domain Registration updated successfully!');
    }

    public function destroy(DomainRegistration $domainRegistration)
    {
        $this->checkAccess();
        $domainRegistration->delete();
        return redirect()->route('domain-registrations.index')->with('success', 'Domain Registration deleted successfully!');
    }
}
