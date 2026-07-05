<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\File;

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
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email'
        ]);
        $client = Client::create($request->only(['company_name','contact_person','email','phone','mobile','address','city','state','country','pincode','gst_number','website','notes','status']));
        return redirect()->route('clients.show', $client)->with('success', 'Client added!');
    }
    public function show(Client $client) {
        $client->load(['projects', 'invoices', 'payments']);
        return view('clients.show', compact('client'));
    }
    public function edit(Client $client) { return view('clients.edit', compact('client')); }
    public function update(Request $request, Client $client) {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id
        ]);
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

    public function downloadTemplate()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can perform Excel operations.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Columns definition
        $headers = [
            'Company Name',
            'Contact Person',
            'Email',
            'Phone',
            'Address',
            'City',
            'State',
            'Country',
            'GST Number',
            'Website',
            'Notes',
            'Status'
        ];

        // Write Headers
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Sample Data
        $sampleData = [
            'Acme Corporation',
            'John Doe',
            'john.doe@example.com',
            '1234567890',
            '123 Main Street',
            'New York',
            'NY',
            'USA',
            '29GGGGG1111A1Z1',
            'www.acme.com',
            'Standard corporate client.',
            'active' // active, inactive
        ];

        foreach ($sampleData as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $value);
        }

        // Style the headers (Bold, Slate Indigo background)
        $headerRange = 'A1:L1';
        $styleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FF4F46E5', // Slate Indigo color
                ],
            ],
        ];
        $sheet->getStyle($headerRange)->applyFromArray($styleArray);

        // Adjust column widths automatically
        foreach (range(1, count($headers)) as $colIndex) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="clients_import_template.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    public function preview(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can perform Excel operations.');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:4096',
        ]);

        $file = $request->file('file');
        
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $tempFileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($tempDir, $tempFileName);
        $tempFilePath = $tempDir . '/' . $tempFileName;

        try {
            $spreadsheet = IOFactory::load($tempFilePath);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error reading the excel file. Please make sure it is a valid format.']);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            File::delete($tempFilePath);
            return back()->withErrors(['file' => 'The uploaded file contains no data rows.']);
        }

        $validRows = [];
        $invalidRows = [];

        // Parse data starting from row index 1 (row 2 in spreadsheet)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip completely empty rows
            $allEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim($cell) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            $companyName   = isset($row[0]) ? trim($row[0]) : '';
            $contactPerson = isset($row[1]) ? trim($row[1]) : '';
            $email         = isset($row[2]) ? trim($row[2]) : '';
            $phone         = isset($row[3]) ? trim($row[3]) : '';
            $address       = isset($row[4]) ? trim($row[4]) : '';
            $city          = isset($row[5]) ? trim($row[5]) : '';
            $state         = isset($row[6]) ? trim($row[6]) : '';
            $country       = isset($row[7]) ? trim($row[7]) : '';
            $gstNumber     = isset($row[8]) ? trim($row[8]) : '';
            $website       = isset($row[9]) ? trim($row[9]) : '';
            $notes         = isset($row[10]) ? trim($row[10]) : '';
            $status        = isset($row[11]) ? strtolower(trim($row[11])) : 'active';

            $errors = [];
            if (empty($companyName)) {
                $errors[] = 'Company Name is required.';
            }
            if (empty($contactPerson)) {
                $errors[] = 'Contact Person is required.';
            }
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format ('{$email}').";
            } else {
                $exists = Client::where('email', $email)->exists();
                if ($exists) {
                    $errors[] = "Client with email '{$email}' already exists.";
                }
            }

            $rowData = [
                'company_name'   => $companyName,
                'contact_person' => $contactPerson,
                'email'          => $email,
                'phone'          => $phone,
                'address'        => $address,
                'city'           => $city,
                'state'          => $state,
                'country'        => $country,
                'gst_number'     => $gstNumber,
                'website'        => $website,
                'notes'          => $notes,
                'status'         => in_array($status, ['active', 'inactive']) ? $status : 'active',
            ];

            if (empty($errors)) {
                $validRows[] = $rowData;
            } else {
                $rowData['errors'] = implode(' ', $errors);
                $invalidRows[] = $rowData;
            }
        }

        return view('clients.import_preview', [
            'validRows' => $validRows,
            'invalidRows' => $invalidRows,
            'tempFilePath' => $tempFilePath,
        ]);
    }

    public function submit(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can perform Excel operations.');
        }

        $request->validate([
            'temp_file_path' => 'required|string',
        ]);

        $tempFilePath = $request->temp_file_path;

        if (!File::exists($tempFilePath)) {
            return redirect()->route('clients.index')->withErrors(['file' => 'The uploaded file session expired. Please upload again.']);
        }

        try {
            $spreadsheet = IOFactory::load($tempFilePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            File::delete($tempFilePath);
            return redirect()->route('clients.index')->withErrors(['file' => 'Error parsing stored temporary file.']);
        }

        $successCount = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $allEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim($cell) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            $companyName   = isset($row[0]) ? trim($row[0]) : '';
            $contactPerson = isset($row[1]) ? trim($row[1]) : '';
            $email         = isset($row[2]) ? trim($row[2]) : '';
            $phone         = isset($row[3]) ? trim($row[3]) : '';
            $address       = isset($row[4]) ? trim($row[4]) : '';
            $city          = isset($row[5]) ? trim($row[5]) : '';
            $state         = isset($row[6]) ? trim($row[6]) : '';
            $country       = isset($row[7]) ? trim($row[7]) : '';
            $gstNumber     = isset($row[8]) ? trim($row[8]) : '';
            $website       = isset($row[9]) ? trim($row[9]) : '';
            $notes         = isset($row[10]) ? trim($row[10]) : '';
            $status        = isset($row[11]) ? strtolower(trim($row[11])) : 'active';

            // double check validations
            if (empty($companyName) || empty($contactPerson) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $exists = Client::where('email', $email)->exists();
            if ($exists) {
                continue;
            }

            Client::create([
                'company_name'   => $companyName,
                'contact_person' => $contactPerson,
                'email'          => $email,
                'phone'          => $phone ?: null,
                'address'        => $address ?: null,
                'city'           => $city ?: null,
                'state'          => $state ?: null,
                'country'        => $country ?: null,
                'gst_number'     => $gstNumber ?: null,
                'website'        => $website ?: null,
                'notes'          => $notes ?: null,
                'status'         => in_array($status, ['active', 'inactive']) ? $status : 'active',
                'created_by'     => auth()->id(),
            ]);

            $successCount++;
        }

        File::delete($tempFilePath);

        return redirect()->route('clients.index')->with('success', "Import completed! Successfully imported {$successCount} clients.");
    }
}
