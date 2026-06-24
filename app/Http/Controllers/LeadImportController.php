<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadRoom;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\File;

class LeadImportController extends Controller
{
    private function checkAccess()
    {
        if (auth()->user()->isTelecaller()) {
            abort(403, 'Unauthorized access to lead import.');
        }
    }

    public function downloadTemplate()
    {
        $this->checkAccess();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Columns definition
        $headers = [
            'Client Name',
            'Client Phone',
            'Client Email',
            'Location',
            'Business Type',
            'Source',
            'Requirement',
            'Estimated Budget',
            'Notes'
        ];

        // Write Headers
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Sample Data
        $sampleData = [
            'John Doe',
            '9876543210',
            'john@example.com',
            'New York',
            'SaaS Provider',
            'website',
            'Custom Web App Development',
            '5000.00',
            'Wants the project completed by next month'
        ];

        foreach ($sampleData as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $value);
        }

        // Style the headers (Bold, Light Slate gray background)
        $headerRange = 'A1:I1';
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
                'Content-Disposition' => 'attachment; filename="leads_import_template.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    public function showImportForm()
    {
        $this->checkAccess();

        $clients = \App\Models\Client::with('rooms')->latest()->get();
        return view('leads.import', compact('clients'));
    }

    public function preview(Request $request)
    {
        $this->checkAccess();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:4096',
            'lead_room_id' => 'required|exists:lead_rooms,id',
        ]);

        $file = $request->file('file');
        
        // Define directory path in workspace storage
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Save file to temp directory
        $tempFileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($tempDir, $tempFileName);
        $tempFilePath = $tempDir . '/' . $tempFileName;

        // Load spreadsheet
        try {
            $spreadsheet = IOFactory::load($tempFilePath);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error reading the excel file. Please make sure it is a valid format.']);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Check columns size (At least Name and Phone)
        if (count($rows) < 2) {
            File::delete($tempFilePath);
            return back()->withErrors(['file' => 'The uploaded file contains no data rows.']);
        }

        $validRows = [];
        $invalidRows = [];

        // Parse data starting from row index 1 (row 2 in spreadsheet)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Normalize row data with nulls
            $name = isset($row[0]) ? trim($row[0]) : '';
            $phone = isset($row[1]) ? trim($row[1]) : '';
            $email = isset($row[2]) ? trim($row[2]) : '';
            $location = isset($row[3]) ? trim($row[3]) : '';
            $businessType = isset($row[4]) ? trim($row[4]) : '';
            $source = isset($row[5]) ? trim($row[5]) : '';
            $requirement = isset($row[6]) ? trim($row[6]) : '';
            $budget = isset($row[7]) ? trim($row[7]) : '';
            $notes = isset($row[8]) ? trim($row[8]) : '';

            // Check if entire row is empty, if so skip it
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

            $errors = [];
            if (empty($name)) {
                $errors[] = 'Client Name is required.';
            }
            if (empty($phone)) {
                $errors[] = 'Client Phone is required.';
            }

            $rowData = [
                'client_name' => $name,
                'client_phone' => $phone,
                'client_email' => $email,
                'location' => $location,
                'business_type' => $businessType,
                'source' => $source,
                'requirement' => empty($requirement) ? 'Imported lead' : $requirement,
                'estimated_budget' => is_numeric($budget) ? floatval($budget) : null,
                'notes' => $notes,
            ];

            if (empty($errors)) {
                $validRows[] = $rowData;
            } else {
                $rowData['errors'] = implode(' ', $errors);
                $invalidRows[] = $rowData;
            }
        }

        $room = LeadRoom::findOrFail($request->lead_room_id);

        return view('leads.import_preview', [
            'validRows' => $validRows,
            'invalidRows' => $invalidRows,
            'tempFilePath' => $tempFilePath,
            'lead_room_id' => $room->id,
            'roomName' => $room->name,
        ]);
    }

    public function submit(Request $request)
    {
        $this->checkAccess();

        $request->validate([
            'temp_file_path' => 'required|string',
            'lead_room_id' => 'required|exists:lead_rooms,id',
        ]);

        $tempFilePath = $request->temp_file_path;

        if (!File::exists($tempFilePath)) {
            return redirect()->route('leads.import.form')->withErrors(['file' => 'The uploaded file session expired. Please upload again.']);
        }

        try {
            $spreadsheet = IOFactory::load($tempFilePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            File::delete($tempFilePath);
            return redirect()->route('leads.import.form')->withErrors(['file' => 'Error parsing stored temporary file.']);
        }

        $room = LeadRoom::findOrFail($request->lead_room_id);
        $leadsImported = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $name = isset($row[0]) ? trim($row[0]) : '';
            $phone = isset($row[1]) ? trim($row[1]) : '';
            $email = isset($row[2]) ? trim($row[2]) : '';
            $location = isset($row[3]) ? trim($row[3]) : '';
            $businessType = isset($row[4]) ? trim($row[4]) : '';
            $source = isset($row[5]) ? trim($row[5]) : '';
            $requirement = isset($row[6]) ? trim($row[6]) : '';
            $budget = isset($row[7]) ? trim($row[7]) : '';
            $notes = isset($row[8]) ? trim($row[8]) : '';

            // Skip empty rows
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

            // Only import if mandatory fields are met
            if (!empty($name) && !empty($phone)) {
                Lead::create([
                    'client_id' => $room->client_id,
                    'lead_room_id' => $room->id,
                    'client_name' => $name,
                    'client_phone' => $phone,
                    'client_email' => empty($email) ? null : $email,
                    'location' => empty($location) ? null : $location,
                    'business_type' => empty($businessType) ? null : $businessType,
                    'source' => empty($source) ? 'direct' : $source,
                    'requirement' => empty($requirement) ? 'Imported lead' : $requirement,
                    'estimated_budget' => is_numeric($budget) ? floatval($budget) : null,
                    'notes' => empty($notes) ? null : $notes,
                    'status' => 'new',
                    'created_by' => auth()->id(),
                    'assigned_to' => null // Left unassigned initially so room telecallers can work on them
                ]);
                $leadsImported++;
            }
        }

        // Clean up temp file
        File::delete($tempFilePath);

        return redirect()->route('leads.index')->with('success', "Successfully imported {$leadsImported} leads to room \"{$room->name}\"!");
    }
}
