<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ResellerController extends Controller
{
    public function index()
    {
        $companies = Company::withCount(['users' => function ($q) {
            $q->where('status', 'active')->whereHas('role', function ($r) {
                $r->where('slug', '!=', 'client');
            });
        }])->latest()->get();

        foreach ($companies as $company) {
            $company->admin = User::where('company_id', $company->id)
                ->whereHas('role', function ($q) {
                    $q->where('slug', 'super-admin')->orWhere('slug', 'admin');
                })
                ->first();
        }

        return view('reseller.dashboard', compact('companies'));
    }

    public function create()
    {
        return view('reseller.companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'admin_name'   => 'required|string|max:255',
            'admin_email'  => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        $company = Company::create([
            'name'   => $request->company_name,
            'email'  => $request->admin_email,
            'status' => 'active',
        ]);

        // Seed default departments/designations for the new company
        \Database\Seeders\DepartmentSeeder::seedForCompany($company->id);

        $adminRole = Role::where('slug', 'super-admin')->first();

        // Create the company admin user
        User::create([
            'name'              => $request->admin_name,
            'email'             => $request->admin_email,
            'password'          => Hash::make($request->admin_password),
            'role_id'           => $adminRole?->id,
            'company_id'        => $company->id,
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        return redirect()->route('reseller.dashboard')->with('success', 'Company created successfully!');
    }

    public function toggleStatus(Company $company)
    {
        // Prevent toggling the default company
        if ($company->id === 1) {
            return back()->with('error', 'Cannot suspend the default company.');
        }

        $newStatus = $company->status === 'active' ? 'suspended' : 'active';
        $company->update(['status' => $newStatus]);

        // Toggle user accounts as well
        User::where('company_id', $company->id)->update([
            'status' => $newStatus === 'active' ? 'active' : 'inactive'
        ]);

        return back()->with('success', "Company status updated to {$newStatus}.");
    }
}
