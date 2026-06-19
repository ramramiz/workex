<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $company = auth()->user()->company;
        return view('settings.index', compact('settings', 'company'));
    }

    public function update(Request $request)
    {
        // Update global settings
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::set($key, $value);
        }

        // Update company details for the logged-in user's company
        $company = auth()->user()->company;
        if ($company) {
            $company->update([
                'name'              => $request->input('company_name', $company->name),
                'email'             => $request->input('company_email', $company->email),
                'auth_person_name'  => $request->input('company_auth_person_name'),
                'auth_person_email' => $request->input('company_auth_person_email'),
                'phone'             => $request->input('company_phone'),
                'address'           => $request->input('company_address'),
                'gst'               => $request->input('company_gst'),
            ]);
        }

        \App\Models\ActivityLog::log('settings_updated', 'System settings and company details updated');

        return back()->with('success', 'Settings saved successfully!');
    }
}
