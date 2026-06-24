<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use App\Models\Employee;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Project::with(['client', 'teamLeader', 'tasks'])
            ->when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id)
                ->orWhereHas('tasks', fn($t) => $t->where('assigned_to', $user->id)))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->client, fn($q) => $q->where('client_id', $request->client))
            ->when($request->filter === 'delayed', fn($q) => $q->whereNotIn('status', ['completed','delivered','cancelled'])->whereDate('deadline', '<', today()));

        $projects = $query->latest()->paginate(12);
        $clients  = Client::where('status', 'active')->get();

        return view('projects.index', compact('projects', 'clients'));
    }

    public function create()
    {
        if (!auth()->user()->isLeaderOrAbove()) {
            abort(403, 'Unauthorized action.');
        }
        $clients      = Client::where('status', 'active')->get();
        $teamLeaders  = User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        $projectTypes = Project::whereNotNull('type')->distinct()->pluck('type')->toArray();
        return view('projects.create', compact('clients', 'teamLeaders', 'projectTypes'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isLeaderOrAbove()) {
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
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('project_logos', 'public');
        }

        $project = Project::create([
            'project_code'    => 'PRJ-' . now()->format('Ymd') . '-' . str_pad(Project::count() + 1, 4, '0', STR_PAD_LEFT),
            'name'            => $request->name,
            'logo_path'       => $logoPath,
            'description'     => $request->description,
            'client_id'       => $request->client_id,
            'team_leader_id'  => $request->team_leader_id,
            'start_date'      => $request->start_date,
            'deadline'        => $request->deadline,
            'project_value'   => $request->budget ?? 0,
            'priority'        => $request->priority,
            'status'          => 'planning',
            'technologies'    => $request->technologies ? array_map('trim', explode(',', $request->technologies)) : [],
            'project_type'    => $request->project_type ?? 'web',
            'created_by'      => auth()->id(),
        ]);

        // Add members
        if ($request->members) {
            $project->members()->sync($request->members);
        }

        \App\Models\ActivityLog::log('project_created', "Created project: {$project->name}", $project);

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
        $clients      = Client::where('status', 'active')->get();
        $teamLeaders  = User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        $employees    = User::whereHas('role', fn($q) => $q->where('slug', 'employee'))->where('status', 'active')->get();
        $projectTypes = Project::whereNotNull('type')->distinct()->pluck('type')->toArray();
        return view('projects.edit', compact('project', 'clients', 'teamLeaders', 'employees', 'projectTypes'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'priority'     => 'required|in:low,medium,high,critical',
            'budget'       => 'nullable|numeric|min:0',
            'logo'         => 'nullable|image|max:4096',
            'project_type' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'name', 'description', 'client_id', 'team_leader_id', 'start_date',
            'deadline', 'priority', 'status', 'project_type',
        ]);

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
        $request->validate(['status' => 'required|in:not_started,planning,design,development,testing,client_review,rework,completed,delivered,on_hold,cancelled']);
        $project->update(['status' => $request->status]);
        \App\Models\ActivityLog::log('project_status_changed', "Project '{$project->name}' status changed to {$request->status}", $project);
        return response()->json(['success' => true]);
    }
}
