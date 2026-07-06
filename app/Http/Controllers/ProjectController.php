<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use App\Models\Employee;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    public function previews(Request $request)
    {
        $user = auth()->user();
        
        $query = Project::whereNotNull('url')
            ->where('url', '!=', '')
            ->with(['client', 'teamLeader']);
            
        if ($user->isTeamLeader()) {
            $query->where(function($q) use ($user) {
                $q->where('team_leader_id', $user->id)
                  ->orWhereHas('members', function($mq) use ($user) {
                      $mq->where('user_id', $user->id);
                  });
            });
        }
        
        $projects = $query->latest()->get();
        
        foreach ($projects as $p) {
            $p->iframe_status = Cache::remember('project_iframe_status_' . $p->id, 3600, function() use ($p) {
                try {
                    $response = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                    ])->timeout(2)->connectTimeout(2)->get($p->url);
                    
                    if (!$response->successful()) {
                        return [
                            'embeddable' => false,
                            'reason' => 'Site returned status ' . $response->status()
                        ];
                    }
                    
                    $xFrameOptions = $response->header('X-Frame-Options');
                    if ($xFrameOptions && (stripos($xFrameOptions, 'deny') !== false || stripos($xFrameOptions, 'sameorigin') !== false)) {
                        return [
                            'embeddable' => false,
                            'reason' => 'Embedding blocked by X-Frame-Options'
                        ];
                    }
                    
                    $csp = $response->header('Content-Security-Policy');
                    if ($csp && stripos($csp, 'frame-ancestors') !== false) {
                        return [
                            'embeddable' => false,
                            'reason' => 'Embedding blocked by CSP frame-ancestors'
                        ];
                    }
                    
                    return [
                        'embeddable' => true,
                        'reason' => ''
                    ];
                } catch (\Exception $e) {
                    return [
                        'embeddable' => false,
                        'reason' => 'Offline / Connection Timeout'
                    ];
                }
            });
        }
        
        if ($user->isTeamLeader() || $user->isSuperAdmin()) {
            \App\Models\ActivityLog::log('view_project_previews', 'Logged project preview lookup');
        }
        
        return view('projects.previews', compact('projects'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $tab = $request->query('tab', 'working');

        $query = Project::with(['client', 'teamLeader', 'tasks'])
            ->when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id)
                ->orWhereHas('tasks', fn($t) => $t->where('assigned_to', $user->id)))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->client, fn($q) => $q->where('client_id', $request->client))
            ->when($request->filter === 'delayed', fn($q) => $q->whereNotIn('status', ['completed','delivered','cancelled','completed_started_amc'])->whereDate('deadline', '<', today()));

        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            if ($tab === 'completed') {
                $query->whereIn('status', ['completed', 'delivered', 'cancelled', 'completed_started_amc']);
            } else {
                $query->whereNotIn('status', ['completed', 'delivered', 'cancelled', 'completed_started_amc']);
            }
        }

        $projects = $query->latest()->paginate(12);
        $clients  = Client::where('status', 'active')->get();

        return view('projects.index', compact('projects', 'clients'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('projects.create')) {
            abort(403, 'Unauthorized action.');
        }
        $clients             = Client::where('status', 'active')->get();
        $teamLeaders         = User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        $projectTypes        = Project::whereNotNull('type')->distinct()->pluck('type')->toArray();
        $hostingProviders    = \App\Models\HostingProvider::all();
        $domainRegistrations = \App\Models\DomainRegistration::all();
        return view('projects.create', compact('clients', 'teamLeaders', 'projectTypes', 'hostingProviders', 'domainRegistrations'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('projects.create')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can create projects.');
        }
        $request->validate([
            'name'           => 'required|string|max:255',
            'client_id'      => 'nullable|exists:clients,id',
            'team_leader_id' => 'nullable|exists:users,id',
            'start_date'     => 'nullable|date',
            'deadline'       => 'nullable|date|after_or_equal:start_date',
            'budget'         => 'nullable|numeric|min:0',
            'priority'       => 'required|in:low,medium,high,critical',
            'logo'           => 'nullable|image|max:4096',
            'project_type'   => 'nullable|string|max:255',
            'url'            => 'nullable|string|max:2048',
            'domain_provider' => 'nullable|string|max:255',
            'domain_valid_till' => 'nullable|date',
            'hosting_provider_id' => 'nullable|exists:hosting_providers,id',
            'hosting_valid_till' => 'nullable|date',
            'domain_registration_id' => 'nullable|exists:domain_registrations,id',
            'amc_start_date' => 'nullable|date',
            'amc_end_date'   => 'nullable|date|after_or_equal:amc_start_date',
            'amc_amount'     => 'nullable|numeric|min:0',
            'amc_frequency'  => 'nullable|in:monthly,quarterly,semi-annually,annually',
            'amc_status'     => 'nullable|in:active,pending_renewal,expired',
            'amc_remarks'    => 'nullable|string',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('project_logos', 'public');
        }

        $status = 'planning';
        if ($request->filled('amc_start_date')) {
            $status = 'completed_started_amc';
        }

        $project = Project::create([
            'project_code'    => 'PRJ-' . now()->format('Ymd') . '-' . str_pad(Project::count() + 1, 4, '0', STR_PAD_LEFT),
            'name'            => $request->name,
            'logo_path'       => $logoPath,
            'url'             => $request->url,
            'description'     => $request->description,
            'client_id'       => $request->client_id,
            'team_leader_id'  => $request->team_leader_id,
            'start_date'      => $request->start_date,
            'deadline'        => $request->deadline,
            'project_value'   => $request->budget ?? 0,
            'priority'        => $request->priority,
            'status'          => $status,
            'technologies'    => $request->technologies ? array_map('trim', explode(',', $request->technologies)) : [],
            'project_type'    => $request->project_type ?? 'web',
            'created_by'      => auth()->id(),
            'domain_provider' => $request->domain_provider,
            'domain_valid_till' => $request->domain_valid_till,
            'hosting_provider_id' => $request->hosting_provider_id,
            'hosting_valid_till' => $request->hosting_valid_till,
            'domain_registration_id' => $request->domain_registration_id,
        ]);

        // Add members
        if ($request->members) {
            $project->members()->sync($request->members);
        }

        \App\Models\ActivityLog::log('project_created', "Created project: {$project->name}", $project);

        if ($request->filled('amc_start_date')) {
            \App\Models\ProjectAmc::create([
                'project_id' => $project->id,
                'amount'     => $request->amc_amount ?? 0.00,
                'start_date' => $request->amc_start_date,
                'end_date'   => $request->amc_end_date ?? \Carbon\Carbon::parse($request->amc_start_date)->addYear()->subDay()->toDateString(),
                'frequency'  => $request->amc_frequency ?? 'annually',
                'status'     => $request->amc_status ?? 'active',
                'remarks'    => $request->amc_remarks,
                'company_id' => auth()->user()->company_id,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project created!',
                'project' => $project
            ]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project created!');
    }

    public function show(Project $project)
    {
        $project->load(['client', 'teamLeader', 'members', 'tasks.assignee', 'tasks.meeting', 'bugs', 'invoices']);
        $taskStats = [
            'total'     => $project->tasks->count(),
            'completed' => $project->tasks->where('status', 'completed')->count(),
            'in_progress'=> $project->tasks->where('status', 'in_progress')->count(),
            'pending'   => $project->tasks->where('status', 'pending')->count(),
        ];
        $employees = User::whereHas('role', fn($q) => $q->where('slug', 'employee'))->where('status', 'active')->get();
        return view('projects.show', compact('project', 'taskStats', 'employees'));
    }

    public function edit(Project $project)
    {
        $clients             = Client::where('status', 'active')->get();
        $teamLeaders         = User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        $employees           = User::whereHas('role', fn($q) => $q->where('slug', 'employee'))->where('status', 'active')->get();
        $projectTypes        = Project::whereNotNull('type')->distinct()->pluck('type')->toArray();
        $amc                 = $project->amc;
        $hostingProviders    = \App\Models\HostingProvider::all();
        $domainRegistrations = \App\Models\DomainRegistration::all();
        return view('projects.edit', compact('project', 'clients', 'teamLeaders', 'employees', 'projectTypes', 'amc', 'hostingProviders', 'domainRegistrations'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'priority'     => 'required|in:low,medium,high,critical',
            'budget'       => 'nullable|numeric|min:0',
            'logo'         => 'nullable|image|max:4096',
            'project_type' => 'nullable|string|max:255',
            'url'          => 'nullable|string|max:2048',
            'domain_provider' => 'nullable|string|max:255',
            'domain_valid_till' => 'nullable|date',
            'hosting_provider_id' => 'nullable|exists:hosting_providers,id',
            'hosting_valid_till' => 'nullable|date',
            'domain_registration_id' => 'nullable|exists:domain_registrations,id',
            'amc_start_date' => 'nullable|date',
            'amc_end_date'   => 'nullable|date|after_or_equal:amc_start_date',
            'amc_amount'     => 'nullable|numeric|min:0',
            'amc_frequency'  => 'nullable|in:monthly,quarterly,semi-annually,annually',
            'amc_status'     => 'nullable|in:active,pending_renewal,expired',
            'amc_remarks'    => 'nullable|string',
        ]);

        $data = $request->only([
            'name', 'description', 'client_id', 'team_leader_id', 'start_date',
            'deadline', 'priority', 'status', 'project_type', 'url',
            'domain_provider', 'domain_valid_till', 'hosting_provider_id', 'hosting_valid_till', 'domain_registration_id',
        ]);

        if ($request->filled('amc_start_date')) {
            $data['status'] = 'completed_started_amc';
        }

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($project->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($project->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('project_logos', 'public');
        }

        if ($request->has('budget')) {
            $data['project_value'] = $request->budget ?? 0;
        }

        $data['technologies'] = $request->technologies ? array_map('trim', explode(',', $request->technologies)) : [];

        $project->update($data);

        if ($request->members) {
            $project->members()->sync($request->members);
        } else {
            $project->members()->sync([]);
        }

        if ($request->filled('amc_start_date')) {
            \App\Models\ProjectAmc::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'amount'     => $request->amc_amount ?? 0.00,
                    'start_date' => $request->amc_start_date,
                    'end_date'   => $request->amc_end_date ?? \Carbon\Carbon::parse($request->amc_start_date)->addYear()->subDay()->toDateString(),
                    'frequency'  => $request->amc_frequency ?? 'annually',
                    'status'     => $request->amc_status ?? 'active',
                    'remarks'    => $request->amc_remarks,
                    'company_id' => auth()->user()->company_id,
                ]
            );
        } else {
            $project->amc()?->delete();
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project updated!');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    public function updateTeam(Request $request, Project $project)
    {
        if (!auth()->user()->isAdminOrAbove() && $project->team_leader_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id'
        ]);

        $project->members()->sync($request->members ?? []);

        \App\Models\ActivityLog::log('project_team_updated', "Updated team members for project: {$project->name}", $project);

        return back()->with('success', 'Project team updated successfully!');
    }

    public function updateStatus(Request $request, Project $project)
    {
        $request->validate(['status' => 'required|in:not_started,planning,design,development,testing,client_review,rework,completed,delivered,on_hold,cancelled,completed_started_amc,discontinued']);
        $project->update(['status' => $request->status]);
        \App\Models\ActivityLog::log('project_status_changed', "Project '{$project->name}' status changed to {$request->status}", $project);
        return response()->json(['success' => true]);
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
            'Project Name',
            'Project Type',
            'Description',
            'Client Company Name',
            'Team Leader Email',
            'Project Budget',
            'Priority',
            'Project Start Date',
            'Deadline',
            'Technologies',
            'Project Code',
            'Project URL',
            'Completed Date',
            'Advance Amount',
            'Balance Amount',
            'Manager Email',
            'Progress Percentage',
            'Notes',
            'Amc Start Date',
            'AMC Billing Frequency',
            'AMC Value',
            'AMC Due Date',
            'AMC Contract Status',
            'AMC Remarks',
            'Domain Provider',
            'Domain Valid Till',
            'Hosting Provider',
            'Hosting Valid Till'
        ];

        // Write Headers
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Sample Data
        $project = Project::with(['client', 'teamLeader', 'manager', 'amc', 'hostingProvider'])->first();

        if ($project) {
            $sampleData = [
                $project->name,
                $project->project_type ?? 'web',
                $project->description,
                $project->client ? $project->client->company_name : '',
                $project->teamLeader ? $project->teamLeader->email : '',
                number_format($project->project_value, 2, '.', ''),
                $project->priority ?? 'medium',
                $project->start_date ? $project->start_date->format('Y-m-d') : '',
                $project->deadline ? $project->deadline->format('Y-m-d') : '',
                $project->technologies ? implode(', ', $project->technologies) : '',
                $project->project_code,
                $project->url,
                $project->completed_date ? $project->completed_date->format('Y-m-d') : '',
                number_format($project->advance_amount, 2, '.', ''),
                number_format($project->balance_amount, 2, '.', ''),
                $project->manager ? $project->manager->email : '',
                (string)$project->progress_percentage,
                $project->notes,
                $project->amc && $project->amc->start_date ? \Carbon\Carbon::parse($project->amc->start_date)->format('Y-m-d') : '',
                $project->amc ? $project->amc->frequency : 'annually',
                $project->amc ? number_format($project->amc->amount, 2, '.', '') : '0.00',
                $project->amc && $project->amc->end_date ? \Carbon\Carbon::parse($project->amc->end_date)->format('Y-m-d') : '',
                $project->amc ? $project->amc->status : 'active',
                $project->amc ? $project->amc->remarks : '',
                $project->domain_provider,
                $project->domain_valid_till ? $project->domain_valid_till->format('Y-m-d') : '',
                $project->hostingProvider ? $project->hostingProvider->name : '',
                $project->hosting_valid_till ? $project->hosting_valid_till->format('Y-m-d') : ''
            ];
        } else {
            $sampleData = [
                'E-Commerce Site',
                'web',
                'Develop an online storefront',
                'Acme Company',
                'leader@example.com',
                '75000.00',
                'high', // low, medium, high, critical
                '2026-07-05', // YYYY-MM-DD
                '2026-12-31', // YYYY-MM-DD
                'Laravel, Vue.js, Tailwind',
                'PRJ-CUSTOM-123',
                'https://myproject.com',
                '2026-12-25', // YYYY-MM-DD
                '25000.00',
                '10000.00',
                'manager@example.com',
                '80',
                'This is a sample project note.',
                '2026-08-01', // YYYY-MM-DD
                'annually', // annually, semi-annually, quarterly, monthly
                '5000.00',
                '2027-07-31', // YYYY-MM-DD
                'active', // active, pending_renewal, expired
                'Includes hosting and support.',
                'GoDaddy', // Domain Provider
                '2027-07-05', // Domain Valid Till
                'Hostinger', // Hosting Provider
                '2027-07-05' // Hosting Valid Till
            ];
        }

        foreach ($sampleData as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $value);
        }

        // Style the headers (Bold, Slate Indigo background)
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastColLetter}1";
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
                'Content-Disposition' => 'attachment; filename="projects_import_template.xlsx"',
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

            $projectName   = isset($row[0]) ? trim($row[0]) : '';
            $projectType   = isset($row[1]) ? trim($row[1]) : 'web';
            $description   = isset($row[2]) ? trim($row[2]) : null;
            $clientName    = isset($row[3]) ? trim($row[3]) : '';
            $leaderEmail   = isset($row[4]) ? trim($row[4]) : '';
            $budget        = isset($row[5]) ? trim($row[5]) : '';
            $priority      = isset($row[6]) ? strtolower(trim($row[6])) : 'medium';
            $startDate     = isset($row[7]) ? trim($row[7]) : null;
            $deadline      = isset($row[8]) ? trim($row[8]) : null;
            $technologies  = isset($row[9]) ? trim($row[9]) : '';

            // New fields
            $projectCode   = isset($row[10]) ? trim($row[10]) : '';
            $projectUrl    = isset($row[11]) ? trim($row[11]) : '';
            $completedDate = isset($row[12]) ? trim($row[12]) : null;
            $advanceAmount = isset($row[13]) ? trim($row[13]) : '0.00';
            $balanceAmount = isset($row[14]) ? trim($row[14]) : '0.00';
            $managerEmail  = isset($row[15]) ? trim($row[15]) : '';
            $progressPct   = isset($row[16]) ? trim($row[16]) : '0';
            $notes         = isset($row[17]) ? trim($row[17]) : null;

            // AMC fields
            $amcStartDate  = isset($row[18]) ? trim($row[18]) : null;
            $amcFrequency  = isset($row[19]) ? strtolower(trim($row[19])) : 'annually';
            $amcAmount     = isset($row[20]) ? trim($row[20]) : '0.00';
            $amcEndDate    = isset($row[21]) ? trim($row[21]) : null;
            $amcStatus     = isset($row[22]) ? strtolower(trim($row[22])) : 'active';
            $amcRemarks    = isset($row[23]) ? trim($row[23]) : null;

            // Domain & Hosting fields
            $domainProviderName  = isset($row[24]) ? trim($row[24]) : '';
            $domainValidTill     = isset($row[25]) ? trim($row[25]) : null;
            $hostingProviderName = isset($row[26]) ? trim($row[26]) : '';
            $hostingValidTill    = isset($row[27]) ? trim($row[27]) : null;

            $errors = [];
            if (empty($projectName)) {
                $errors[] = 'Project Name is required.';
            }

            // Find client (for display / warnings)
            $client = null;
            $clientWarning = '';
            if (!empty($clientName)) {
                $client = Client::where('company_name', $clientName)->first();
                if (!$client) {
                    $clientWarning = "Client '{$clientName}' not found (defaults to none).";
                }
            }

            // Find leader (for display / warnings)
            $teamLeader = null;
            $leaderWarning = '';
            if (!empty($leaderEmail)) {
                $teamLeader = User::where('email', $leaderEmail)
                    ->whereHas('role', fn($q) => $q->where('slug', 'team-leader'))
                    ->first();
                if (!$teamLeader) {
                    $leaderWarning = "Team Leader '{$leaderEmail}' not found (defaults to none).";
                }
            }

            // Find manager (for display / warnings)
            $manager = null;
            $managerWarning = '';
            if (!empty($managerEmail)) {
                $manager = User::where('email', $managerEmail)->first();
                if (!$manager) {
                    $managerWarning = "Manager '{$managerEmail}' not found (defaults to none).";
                }
            }

            // Find Domain Registration (for display / warnings)
            $domainReg = null;
            $domainWarning = '';
            if (!empty($domainProviderName)) {
                $domainReg = \App\Models\DomainRegistration::where('name', $domainProviderName)->first();
                if (!$domainReg) {
                    $domainWarning = "Domain Provider '{$domainProviderName}' not found (defaults to none).";
                }
            }

            // Find Hosting Provider (for display / warnings)
            $hostingProv = null;
            $hostingWarning = '';
            if (!empty($hostingProviderName)) {
                $hostingProv = \App\Models\HostingProvider::where('name', $hostingProviderName)->first();
                if (!$hostingProv) {
                    $hostingWarning = "Hosting Provider '{$hostingProviderName}' not found (defaults to none).";
                }
            }

            // Find existing project (for display / warnings / update)
            $project = null;
            if (!empty($projectCode)) {
                $project = Project::where('project_code', $projectCode)->first();
            }
            if (!$project && !empty($projectName)) {
                $project = Project::where('name', $projectName)->first();
            }

            $projectExistsWarning = '';
            if ($project) {
                $projectExistsWarning = "Project already exists and will be updated.";
            }

            $warnings = array_filter([$clientWarning, $leaderWarning, $managerWarning, $projectExistsWarning, $domainWarning, $hostingWarning]);

            $rowData = [
                'name'                => $projectName,
                'project_type'        => $projectType,
                'description'         => $description,
                'client_name'         => $clientName,
                'client_id'           => $client ? $client->id : null,
                'leader_email'        => $leaderEmail,
                'team_leader_id'      => $teamLeader ? $teamLeader->id : null,
                'budget'              => is_numeric($budget) ? floatval($budget) : 0,
                'priority'            => in_array($priority, ['low', 'medium', 'high', 'critical']) ? $priority : 'medium',
                'start_date'          => $startDate,
                'deadline'            => $deadline,
                'technologies'        => $technologies,
                'project_code'        => $projectCode,
                'url'                 => $projectUrl,
                'completed_date'      => $completedDate,
                'advance_amount'      => is_numeric($advanceAmount) ? floatval($advanceAmount) : 0.00,
                'balance_amount'      => is_numeric($balanceAmount) ? floatval($balanceAmount) : 0.00,
                'manager_email'       => $managerEmail,
                'manager_id'          => $manager ? $manager->id : null,
                'progress_percentage' => is_numeric($progressPct) ? intval($progressPct) : 0,
                'notes'               => $notes,
                'amc_start_date'      => $amcStartDate,
                'amc_frequency'       => in_array($amcFrequency, ['monthly', 'quarterly', 'semi-annually', 'annually']) ? $amcFrequency : 'annually',
                'amc_amount'          => is_numeric($amcAmount) ? floatval($amcAmount) : 0.00,
                'amc_end_date'        => $amcEndDate,
                'amc_status'          => in_array($amcStatus, ['active', 'expired', 'pending_renewal']) ? $amcStatus : 'active',
                'amc_remarks'         => $amcRemarks,
                'domain_provider'        => $domainProviderName,
                'domain_registration_id' => $domainReg ? $domainReg->id : null,
                'domain_valid_till'      => $domainValidTill,
                'hosting_provider'       => $hostingProviderName,
                'hosting_provider_id'    => $hostingProv ? $hostingProv->id : null,
                'hosting_valid_till'     => $hostingValidTill,
                'warnings'            => implode(' ', $warnings),
                'exists'              => $project ? true : false,
            ];

            if (empty($errors)) {
                $validRows[] = $rowData;
            } else {
                $rowData['errors'] = implode(' ', $errors);
                $invalidRows[] = $rowData;
            }
        }

        return view('projects.import_preview', [
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
            return redirect()->route('projects.index')->withErrors(['file' => 'The uploaded file session expired. Please upload again.']);
        }

        try {
            $spreadsheet = IOFactory::load($tempFilePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            File::delete($tempFilePath);
            return redirect()->route('projects.index')->withErrors(['file' => 'Error parsing stored temporary file.']);
        }

        $successCount = 0;
        $totalProjectsCount = Project::count();

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

            $projectName   = isset($row[0]) ? trim($row[0]) : '';
            $projectType   = isset($row[1]) ? trim($row[1]) : 'web';
            $description   = isset($row[2]) ? trim($row[2]) : null;
            $clientName    = isset($row[3]) ? trim($row[3]) : '';
            $leaderEmail   = isset($row[4]) ? trim($row[4]) : '';
            $budget        = isset($row[5]) ? trim($row[5]) : '';
            $priority      = isset($row[6]) ? strtolower(trim($row[6])) : 'medium';
            $startDate     = isset($row[7]) ? trim($row[7]) : null;
            $deadline      = isset($row[8]) ? trim($row[8]) : null;
            $technologies  = isset($row[9]) ? trim($row[9]) : '';

            // New fields
            $projectCode   = isset($row[10]) ? trim($row[10]) : '';
            $projectUrl    = isset($row[11]) ? trim($row[11]) : '';
            $completedDate = isset($row[12]) ? trim($row[12]) : null;
            $advanceAmount = isset($row[13]) ? trim($row[13]) : '0.00';
            $balanceAmount = isset($row[14]) ? trim($row[14]) : '0.00';
            $managerEmail  = isset($row[15]) ? trim($row[15]) : '';
            $progressPct   = isset($row[16]) ? trim($row[16]) : '0';
            $notes         = isset($row[17]) ? trim($row[17]) : null;

            // AMC fields
            $amcStartDate  = isset($row[18]) ? trim($row[18]) : null;
            $amcFrequency  = isset($row[19]) ? strtolower(trim($row[19])) : 'annually';
            $amcAmount     = isset($row[20]) ? trim($row[20]) : '0.00';
            $amcEndDate    = isset($row[21]) ? trim($row[21]) : null;
            $amcStatus     = isset($row[22]) ? strtolower(trim($row[22])) : 'active';
            $amcRemarks    = isset($row[23]) ? trim($row[23]) : null;

            // Domain & Hosting fields
            $domainProviderName  = isset($row[24]) ? trim($row[24]) : '';
            $domainValidTill     = isset($row[25]) ? trim($row[25]) : null;
            $hostingProviderName = isset($row[26]) ? trim($row[26]) : '';
            $hostingValidTill    = isset($row[27]) ? trim($row[27]) : null;

            if (empty($projectName)) {
                continue;
            }

            $client = null;
            if (!empty($clientName)) {
                $client = Client::where('company_name', $clientName)->first();
            }

            $teamLeader = null;
            if (!empty($leaderEmail)) {
                $teamLeader = User::where('email', $leaderEmail)
                    ->whereHas('role', fn($q) => $q->where('slug', 'team-leader'))
                    ->first();
            }

            $manager = null;
            if (!empty($managerEmail)) {
                $manager = User::where('email', $managerEmail)->first();
            }

            $domainReg = null;
            if (!empty($domainProviderName)) {
                $domainReg = \App\Models\DomainRegistration::where('name', $domainProviderName)->first();
            }

            $hostingProv = null;
            if (!empty($hostingProviderName)) {
                $hostingProv = \App\Models\HostingProvider::where('name', $hostingProviderName)->first();
            }

            if (!in_array($priority, ['low', 'medium', 'high', 'critical'])) {
                $priority = 'medium';
            }

            $project = null;
            if (!empty($projectCode)) {
                $project = Project::where('project_code', $projectCode)->first();
            }
            if (!$project && !empty($projectName)) {
                $project = Project::where('name', $projectName)->first();
            }

            if (empty($projectCode)) {
                if ($project) {
                    $projectCode = $project->project_code;
                } else {
                    $totalProjectsCount++;
                    $projectCode = 'PRJ-' . now()->format('Ymd') . '-' . str_pad($totalProjectsCount, 4, '0', STR_PAD_LEFT);
                }
            }

            $projectFields = [
                'project_code'           => $projectCode,
                'name'                   => $projectName,
                'description'            => $description,
                'client_id'              => $client ? $client->id : null,
                'team_leader_id'         => $teamLeader ? $teamLeader->id : null,
                'manager_id'             => $manager ? $manager->id : null,
                'start_date'             => empty($startDate) ? null : $startDate,
                'deadline'               => empty($deadline) ? null : $deadline,
                'completed_date'         => empty($completedDate) ? null : $completedDate,
                'project_value'          => is_numeric($budget) ? floatval($budget) : 0,
                'advance_amount'         => is_numeric($advanceAmount) ? floatval($advanceAmount) : 0.00,
                'balance_amount'         => is_numeric($balanceAmount) ? floatval($balanceAmount) : 0.00,
                'progress_percentage'    => is_numeric($progressPct) ? intval($progressPct) : 0,
                'notes'                  => $notes,
                'priority'               => $priority,
                'status'                 => !empty($amcStartDate) ? 'completed_started_amc' : ($project ? $project->status : 'planning'),
                'technologies'           => $technologies ? array_map('trim', explode(',', $technologies)) : [],
                'project_type'           => $projectType,
                'url'                    => $projectUrl,
                'domain_provider'        => $domainProviderName ?: null,
                'domain_registration_id' => $domainReg ? $domainReg->id : null,
                'domain_valid_till'      => empty($domainValidTill) ? null : $domainValidTill,
                'hosting_provider_id'    => $hostingProv ? $hostingProv->id : null,
                'hosting_valid_till'     => empty($hostingValidTill) ? null : $hostingValidTill,
            ];

            if ($project) {
                $project->update($projectFields);
                \App\Models\ActivityLog::log('project_updated', "Updated imported project: {$project->name}", $project);
            } else {
                $projectFields['created_by'] = auth()->id();
                $projectFields['company_id'] = auth()->user()->company_id;
                $project = Project::create($projectFields);
                \App\Models\ActivityLog::log('project_created', "Imported project: {$project->name}", $project);
            }

            if (!empty($amcStartDate)) {
                try {
                    $parsedAmcStart = \Carbon\Carbon::parse($amcStartDate);
                    
                    if (empty($amcEndDate)) {
                        $parsedAmcEnd = $parsedAmcStart->copy();
                        if ($amcFrequency === 'annually') {
                            $parsedAmcEnd->addYear();
                        } elseif ($amcFrequency === 'semi-annually') {
                            $parsedAmcEnd->addMonths(6);
                        } elseif ($amcFrequency === 'quarterly') {
                            $parsedAmcEnd->addMonths(3);
                        } elseif ($amcFrequency === 'monthly') {
                            $parsedAmcEnd->addMonth();
                        }
                        $parsedAmcEnd->subDay();
                        $finalEndDate = $parsedAmcEnd->toDateString();
                    } else {
                        $finalEndDate = \Carbon\Carbon::parse($amcEndDate)->toDateString();
                    }

                    if (!in_array($amcFrequency, ['monthly', 'quarterly', 'semi-annually', 'annually'])) {
                        $amcFrequency = 'annually';
                    }

                    if (!in_array($amcStatus, ['active', 'expired', 'pending_renewal'])) {
                        $amcStatus = 'active';
                    }

                    \App\Models\ProjectAmc::updateOrCreate(
                        ['project_id' => $project->id],
                        [
                            'amount'     => is_numeric($amcAmount) ? floatval($amcAmount) : 0.00,
                            'start_date' => $parsedAmcStart->toDateString(),
                            'end_date'   => $finalEndDate,
                            'frequency'  => $amcFrequency,
                            'status'     => $amcStatus,
                            'remarks'    => $amcRemarks,
                            'company_id' => auth()->user()->company_id,
                        ]
                    );
                } catch (\Exception $e) {
                }
            }

            $successCount++;
        }

        File::delete($tempFilePath);

        return redirect()->route('projects.index')->with('success', "Import completed! Successfully imported {$successCount} projects.");
    }

    public function discontinue(Project $project)
    {
        if (!auth()->user()->isAdminOrAbove()) {
            abort(403, 'Unauthorized action.');
        }

        $project->update(['status' => 'discontinued']);

        \App\Models\ActivityLog::log('project_discontinued', "Discontinued project: {$project->name}", $project);

        return redirect()->route('projects.index')->with('success', 'Project discontinued successfully!');
    }

    public function discontinuedProjects()
    {
        if (!auth()->user()->isAdminOrAbove()) {
            abort(403, 'Unauthorized action.');
        }

        $projects = Project::withoutGlobalScope('not_discontinued')
            ->where('status', 'discontinued')
            ->latest()
            ->paginate(12);

        return view('settings.discontinued_projects', compact('projects'));
    }

    public function showDiscontinuedProject($id)
    {
        if (!auth()->user()->isAdminOrAbove()) {
            abort(403, 'Unauthorized action.');
        }

        $project = Project::withoutGlobalScope('not_discontinued')
            ->with(['client', 'teamLeader', 'tasks'])
            ->findOrFail($id);

        return view('settings.discontinued_project_show', compact('project'));
    }

    public function reactivateProject($id)
    {
        if (!auth()->user()->isAdminOrAbove()) {
            abort(403, 'Unauthorized action.');
        }

        $project = Project::withoutGlobalScope('not_discontinued')->findOrFail($id);
        $project->update(['status' => 'planning']); // Reactivate as planning status

        \App\Models\ActivityLog::log('project_reactivated', "Reactivated discontinued project: {$project->name}", $project);

        return redirect()->route('settings.discontinued-projects')->with('success', 'Project reactivated successfully!');
    }
}
