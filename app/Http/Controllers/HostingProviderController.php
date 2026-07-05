<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HostingProvider;

class HostingProviderController extends Controller
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
        $hostingProviders = HostingProvider::latest()->paginate(15);
        return view('hosting_providers.index', compact('hostingProviders'));
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

        HostingProvider::create([
            'name' => $request->name,
            'url' => $request->url,
            'username' => $request->username,
            'password' => $request->password,
            'renewal_date' => $request->renewal_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('hosting-providers.index')->with('success', 'Hosting Provider created successfully!');
    }

    public function edit(HostingProvider $hostingProvider)
    {
        $this->checkAccess();
        return response()->json($hostingProvider);
    }

    public function update(Request $request, HostingProvider $hostingProvider)
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

        $hostingProvider->update([
            'name' => $request->name,
            'url' => $request->url,
            'username' => $request->username,
            'password' => $request->password,
            'renewal_date' => $request->renewal_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('hosting-providers.index')->with('success', 'Hosting Provider updated successfully!');
    }

    public function destroy(HostingProvider $hostingProvider)
    {
        $this->checkAccess();
        $hostingProvider->delete();
        return redirect()->route('hosting-providers.index')->with('success', 'Hosting Provider deleted successfully!');
    }
}
