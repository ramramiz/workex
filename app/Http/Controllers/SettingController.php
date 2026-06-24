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
        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $request->validate([
                'company_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $path = $request->file('company_logo')->store('logos', 'public');
            Setting::set('company_logo', $path);
        }

        // Handle weekly off days checklist
        if ($request->has('week_off_days_present')) {
            $weekOffDays = $request->has('week_off_days')
                ? implode(',', $request->input('week_off_days'))
                : '';
            Setting::set('week_off_days', $weekOffDays);
        }

        // Exclude fields from the general settings update loop
        $excludeFields = [
            '_token', '_method', 'company_logo', 'week_off_days', 'week_off_days_present',
            'company_name', 'company_email', 'company_auth_person_name',
            'company_auth_person_email', 'company_phone', 'company_address', 'company_gst'
        ];

        // Update global settings
        foreach ($request->except($excludeFields) as $key => $value) {
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

