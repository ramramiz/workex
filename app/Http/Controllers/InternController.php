<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class InternController extends Controller
{
    public function index(Request $request)
    {
        $interns = Intern::with(['department', 'designation'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->department, function ($query, $deptId) {
                $query->where('department_id', $deptId);
            })
            ->latest()
            ->paginate(15);

        $departments = Department::where('status', 'active')->get();

        return view('interns.index', compact('interns', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->with('designations')->get();
        return view('interns.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'joining_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:joining_date',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|string|in:active,completed,cancelled',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('intern_photos', 'public');
        }

        // Create the intern record first
        $intern = Intern::create(array_merge($request->all(), [
            'certificate_code' => null,
            'photo' => $photoPath,
        ]));

        // Generate the code using the auto-incremented ID
        do {
            $sequence = str_pad(14737 + $intern->id, 6, '0', STR_PAD_LEFT);
            $randomStr = strtoupper(Str::random(6));
            $code = "TSL-{$sequence}-{$randomStr}";
        } while (Intern::where('certificate_code', $code)->exists());

        $intern->update(['certificate_code' => $code]);

        \App\Models\ActivityLog::log('intern_created', "Registered intern: {$request->name}");

        return redirect()->route('interns.index')->with('success', 'Intern registered successfully!');
    }

    public function edit(Intern $intern)
    {
        $departments = Department::where('status', 'active')->with('designations')->get();
        return view('interns.edit', compact('intern', 'departments'));
    }

    public function update(Request $request, Intern $intern)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'joining_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:joining_date',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|string|in:active,completed,cancelled',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($intern->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($intern->photo);
            }
            $data['photo'] = $request->file('photo')->store('intern_photos', 'public');
        }

        $intern->update($data);

        \App\Models\ActivityLog::log('intern_updated', "Updated intern details for: {$intern->name}");

        return redirect()->route('interns.index')->with('success', 'Intern updated successfully!');
    }

    public function destroy(Intern $intern)
    {
        $name = $intern->name;
        $intern->delete();

        \App\Models\ActivityLog::log('intern_deleted', "Deleted intern: {$name}");

        return redirect()->route('interns.index')->with('success', 'Intern deleted successfully!');
    }

    public function generateCertificate(Intern $intern)
    {
        $companyName = auth()->user()->company ? auth()->user()->company->name : \App\Models\Setting::get('company_name', 'WorkeX');
        $companyLogo = auth()->user()->company && auth()->user()->company->logo ? auth()->user()->company->logo : \App\Models\Setting::get('company_logo');
        $authPerson = auth()->user()->company && auth()->user()->company->auth_person_name ? auth()->user()->company->auth_person_name : \App\Models\Setting::get('company_auth_person_name', 'Authorized Signatory');

        $encryptedCode = Intern::encryptCode($intern->certificate_code);
        $verificationUrl = route('interns.verify.public', $encryptedCode);

        // Generate QR code via public API and base64 encode it
        $qrCodeBase64 = '';
        try {
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($verificationUrl);
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                ]
            ]);
            $qrImage = @file_get_contents($qrUrl, false, $context);
            if ($qrImage !== false) {
                $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to generate QR code: ' . $e->getMessage());
        }

        $pdf = Pdf::loadView('interns.certificate', compact('intern', 'companyName', 'companyLogo', 'authPerson', 'qrCodeBase64', 'verificationUrl'))->setPaper('a4', 'portrait');
        return $pdf->stream('certificate-' . Str::slug($intern->name) . '.pdf');
    }

    public function downloadQrCode(Intern $intern)
    {
        $encryptedCode = Intern::encryptCode($intern->certificate_code);
        $verificationUrl = route('interns.verify.public', $encryptedCode);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($verificationUrl);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
            ]
        ]);
        $qrImage = @file_get_contents($qrUrl, false, $context);
        
        if ($qrImage === false) {
            return back()->with('error', 'Failed to retrieve QR code from server.');
        }

        return response($qrImage)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qr-' . Str::slug($intern->name) . '.png"');
    }

    public function verifyPublic($code)
    {
        $decryptedCode = Intern::decryptCode($code);
        $intern = null;
        if ($decryptedCode) {
            $intern = Intern::where('certificate_code', $decryptedCode)->with(['department', 'designation', 'company'])->first();
        }
        $displayCode = $intern ? $intern->certificate_code : $code;
        return view('interns.verify', compact('intern', 'displayCode'));
    }
}
