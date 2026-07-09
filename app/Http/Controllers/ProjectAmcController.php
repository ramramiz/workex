<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectAmc;
use App\Models\ProjectAmcLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\File;

class ProjectAmcController extends Controller
{
    private function checkAccess($writeOnly = false)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        
        if ($writeOnly) {
            if (!$user->isAdminOrAbove() && !$user->isAccounts()) {
                abort(403, 'Unauthorized');
            }
        } else {
            if (!$user->isAdminOrAbove() && !$user->isAccounts() && !$user->isTeamLeader()) {
                abort(403, 'Unauthorized');
            }
        }
    }

    public function index(Request $request)
    {
        $this->checkAccess();

        // Dynamic status check auto-expiry
        ProjectAmc::where('status', 'active')
            ->whereDate('end_date', '<', today())
            ->update(['status' => 'expired']);

        $query = ProjectAmc::with(['project.client']);

        // Restrictions for non-admins (Team Leaders only see their projects' AMCs)
        $user = auth()->user();
        if (!$user->isAdminOrAbove() && !$user->isAccounts()) {
            $query->whereHas('project', function($q) use ($user) {
                $q->where('team_leader_id', $user->id);
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('client_id')) {
            $query->whereHas('project', function($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        $amcs = $query->latest()->paginate(15);

        // Fetch projects & clients for filter dropdowns and Add/Edit modals
        $projectsQuery = Project::latest();
        if (!$user->isAdminOrAbove() && !$user->isAccounts()) {
            $projectsQuery->where('team_leader_id', $user->id);
        }
        $projects = $projectsQuery->get();

        return view('project_amcs.index', compact('amcs', 'projects'));
    }

    public function store(Request $request)
    {
        $this->checkAccess(true);

        $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'amount'       => 'required|numeric|min:0',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'frequency'    => 'required|in:monthly,quarterly,semi-annually,annually',
            'status'       => 'required|in:active,expired,pending_renewal',
            'remarks'      => 'nullable|string',
            'alert_phone'  => 'nullable|string|max:255',
            'alert_email'  => 'nullable|email|max:255',
            'service_type' => 'nullable|string|max:255',
        ]);

        ProjectAmc::create([
            'project_id'   => $request->project_id,
            'amount'       => $request->amount,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'frequency'    => $request->frequency,
            'status'       => $request->status,
            'remarks'      => $request->remarks,
            'alert_phone'  => $request->alert_phone,
            'alert_email'  => $request->alert_email,
            'service_type' => $request->service_type ?? 'AMC',
            'company_id'   => auth()->user()->company_id,
        ]);

        return redirect()->route('project-amcs.index')->with('success', 'Project AMC created successfully!');
    }

    public function show(ProjectAmc $projectAmc)
    {
        $this->checkAccess();

        // Auto expire check on single load
        if ($projectAmc->status === 'active' && $projectAmc->end_date->isPast()) {
            $projectAmc->update(['status' => 'expired']);
        }

        $projectAmc->load(['project.client', 'logs']);

        $whatsappLogs = \App\Models\ActivityLog::with('user')
            ->where('model_type', \App\Models\ProjectAmc::class)
            ->where('model_id', $projectAmc->id)
            ->where('action', 'amc_whatsapp_reminder_sent')
            ->latest()
            ->get();

        return view('project_amcs.show', compact('projectAmc', 'whatsappLogs'));
    }

    public function edit(ProjectAmc $projectAmc)
    {
        $this->checkAccess();
        return response()->json($projectAmc);
    }

    public function update(Request $request, ProjectAmc $projectAmc)
    {
        $this->checkAccess(true);

        $request->validate([
            'amount'       => 'required|numeric|min:0',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'frequency'    => 'required|in:monthly,quarterly,semi-annually,annually',
            'status'       => 'required|in:active,expired,pending_renewal',
            'remarks'      => 'nullable|string',
            'alert_phone'  => 'nullable|string|max:255',
            'alert_email'  => 'nullable|email|max:255',
            'service_type' => 'nullable|string|max:255',
        ]);

        $projectAmc->update([
            'amount'       => $request->amount,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'frequency'    => $request->frequency,
            'status'       => $request->status,
            'remarks'      => $request->remarks,
            'alert_phone'  => $request->alert_phone,
            'alert_email'  => $request->alert_email,
            'service_type' => $request->service_type ?? 'AMC',
        ]);

        return redirect()->route('project-amcs.index')->with('success', 'Project AMC updated successfully!');
    }

    public function destroy(ProjectAmc $projectAmc)
    {
        $this->checkAccess(true);
        $projectAmc->delete();
        return redirect()->route('project-amcs.index')->with('success', 'Project AMC deleted successfully!');
    }

    public function storeLog(Request $request, ProjectAmc $projectAmc)
    {
        $this->checkAccess(true);

        $request->validate([
            'payment_date' => 'required|date',
            'amount_paid'  => 'required|numeric|min:0.01',
            'payment_mode' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255',
            'remarks'      => 'nullable|string',
        ]);

        ProjectAmcLog::create([
            'project_amc_id' => $projectAmc->id,
            'payment_date'   => $request->payment_date,
            'amount_paid'    => $request->amount_paid,
            'payment_mode'   => $request->payment_mode,
            'reference_no'   => $request->reference_no,
            'remarks'        => $request->remarks,
        ]);

        return redirect()->route('project-amcs.show', $projectAmc)->with('success', 'Payment / Renewal log registered successfully!');
    }

    public function downloadTemplate()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can perform Excel operations.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Columns definition
        $headers = [
            'Project Code',
            'Project Name',
            'AMC Amount',
            'Start Date',
            'End Date',
            'Frequency',
            'Status',
            'Remarks'
        ];

        // Write Headers
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Sample Data
        $sampleData = [
            'PRJ-001',
            'Sample Project',
            '50000.00',
            '2026-01-01',
            '2026-12-31',
            'annually',
            'active',
            'Standard contract remarks'
        ];

        foreach ($sampleData as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $value);
        }

        // Style the headers (Bold, Slate Indigo background)
        $headerRange = 'A1:H1';
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
                'Content-Disposition' => 'attachment; filename="project_amcs_import_template.xlsx"',
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

            $projectCode = isset($row[0]) ? trim($row[0]) : '';
            $projectName = isset($row[1]) ? trim($row[1]) : '';
            $amount      = isset($row[2]) ? trim($row[2]) : '';
            $startDate   = isset($row[3]) ? trim($row[3]) : '';
            $endDate     = isset($row[4]) ? trim($row[4]) : '';
            $frequency   = isset($row[5]) ? strtolower(trim($row[5])) : 'annually';
            $status      = isset($row[6]) ? strtolower(trim($row[6])) : 'active';
            $remarks     = isset($row[7]) ? trim($row[7]) : null;

            $errors = [];

            // Find project
            $project = null;
            if (!empty($projectCode)) {
                $project = Project::where('project_code', $projectCode)->first();
            }
            if (!$project && !empty($projectName)) {
                $project = Project::where('name', $projectName)->first();
            }

            if (!$project) {
                $errors[] = "Project not found (Code: '{$projectCode}', Name: '{$projectName}').";
            }

            if (empty($startDate) || empty($endDate)) {
                $errors[] = 'Start Date and End Date are required.';
            }

            if (!is_numeric($amount)) {
                $errors[] = 'Amount must be numeric.';
            }

            $rowData = [
                'project_code' => $projectCode,
                'project_name' => $projectName,
                'project_title'=> $project ? $project->name : null,
                'amount'       => is_numeric($amount) ? floatval($amount) : 0,
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'frequency'    => in_array($frequency, ['monthly', 'quarterly', 'semi-annually', 'annually']) ? $frequency : 'annually',
                'status'       => in_array($status, ['active', 'expired', 'pending_renewal']) ? $status : 'active',
                'remarks'      => $remarks,
            ];

            if (empty($errors)) {
                $validRows[] = $rowData;
            } else {
                $rowData['errors'] = implode(' ', $errors);
                $invalidRows[] = $rowData;
            }
        }

        return view('project_amcs.import_preview', [
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
            return redirect()->route('project-amcs.index')->withErrors(['file' => 'The uploaded file session expired. Please upload again.']);
        }

        try {
            $spreadsheet = IOFactory::load($tempFilePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            File::delete($tempFilePath);
            return redirect()->route('project-amcs.index')->withErrors(['file' => 'Error parsing stored temporary file.']);
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

            $projectCode = isset($row[0]) ? trim($row[0]) : '';
            $projectName = isset($row[1]) ? trim($row[1]) : '';
            $amount      = isset($row[2]) ? trim($row[2]) : '';
            $startDate   = isset($row[3]) ? trim($row[3]) : '';
            $endDate     = isset($row[4]) ? trim($row[4]) : '';
            $frequency   = isset($row[5]) ? strtolower(trim($row[5])) : 'annually';
            $status      = isset($row[6]) ? strtolower(trim($row[6])) : 'active';
            $remarks     = isset($row[7]) ? trim($row[7]) : null;

            $project = null;
            if (!empty($projectCode)) {
                $project = Project::where('project_code', $projectCode)->first();
            }
            if (!$project && !empty($projectName)) {
                $project = Project::where('name', $projectName)->first();
            }

            if (!$project || !is_numeric($amount) || empty($startDate) || empty($endDate)) {
                continue;
            }

            if (!in_array($frequency, ['monthly', 'quarterly', 'semi-annually', 'annually'])) {
                $frequency = 'annually';
            }

            if (!in_array($status, ['active', 'expired', 'pending_renewal'])) {
                $status = 'active';
            }

            ProjectAmc::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'amount'     => floatval($amount),
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'frequency'  => $frequency,
                    'status'     => $status,
                    'remarks'    => $remarks,
                    'company_id' => auth()->user()->company_id,
                ]
            );

            $successCount++;
        }

        File::delete($tempFilePath);

        return redirect()->route('project-amcs.index')->with('success', "Import completed! Successfully imported/updated {$successCount} project AMCs.");
    }

    public function sendWhatsappReminder(ProjectAmc $projectAmc)
    {
        $this->checkAccess();

        $daysRemainingOverride = request()->query('days_remaining');
        $result = $projectAmc->sendWhatsappReminderNotification($daysRemainingOverride);

        if ($result['success']) {
            return back()->with('success', 'WhatsApp reminder sent successfully!');
        } else {
            return back()->with('error', 'Failed to send WhatsApp reminder: ' . $result['error']);
        }
    }
}
