<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\WorkSession;
use App\Models\DailyReport;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\Bug;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isReseller()) {
            return redirect()->route('reseller.dashboard');
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $this->adminDashboard($user);
        } elseif ($user->isTeamLeader()) {
            return $this->teamLeaderDashboard($user);
        } elseif ($user->isHR()) {
            return $this->hrDashboard($user);
        } elseif ($user->isAccounts()) {
            return $this->accountsDashboard($user);
        } elseif ($user->isClient()) {
            return $this->clientDashboard($user);
        } elseif ($user->isTelecaller()) {
            return $this->telecallerDashboard($user);
        } else {
            return $this->employeeDashboard($user);
        }
    }

    private function adminDashboard($user)
    {
        $today = Carbon::today();

        $stats = [
            'total_employees' => \App\Models\Employee::where('status', 'active')->count(),
            'working_today'   => WorkSession::whereDate('date', $today)->where('status', 'active')->count(),
            'total_projects'  => Project::count(),
            'active_projects' => Project::whereIn('status', ['planning', 'design', 'development', 'testing', 'client_review'])->count(),
            'delayed_projects' => Project::whereNotIn('status', ['completed', 'delivered', 'cancelled'])->whereDate('deadline', '<', $today)->count(),
            'completed_projects' => Project::where('status', 'completed')->orWhere('status', 'delivered')->count(),
            'pending_tasks'   => Task::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completed_tasks' => Task::where('status', 'completed')->whereDate('updated_at', $today)->count(),
            'pending_leaves'  => Leave::where('status', 'pending')->count(),
            'pending_reports' => DailyReport::where('status', 'pending')->whereDate('date', $today)->count(),
            'open_bugs'       => Bug::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'pending_invoices' => Invoice::whereIn('status', ['pending', 'partially_paid'])->count(),
        ];

        $recentProjects = Project::with(['client', 'teamLeader'])->latest()->take(6)->get();
        $activeEmployees = WorkSession::with(['user.role', 'timeLogs' => fn($q) => $q->where('status', 'running')->with('task')])
            ->whereDate('date', $today)->where('status', 'active')->take(8)->get();
        $pendingReports = DailyReport::with('user')->where('status', 'pending')->latest()->take(5)->get();
        $employees = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee', 'team-leader']))
            ->where('status', 'active')
            ->with([
                'role',
                'employee.department',
                'todayWorkSession.timeLogs' => fn($q) => $q->where('status', 'running')->with('task.project'),
            ])
            ->get();

        return view('dashboard.admin', compact('stats', 'recentProjects', 'activeEmployees', 'pendingReports', 'user', 'employees'));
    }

    private function teamLeaderDashboard($user)
    {
        $today = Carbon::today();

        $myProjects = Project::where('team_leader_id', $user->id)
            ->whereNotIn('status', ['completed', 'delivered', 'cancelled'])->get();

        $myTasks = Task::whereHas('project', fn($q) => $q->where('team_leader_id', $user->id))
            ->whereNotIn('status', ['completed', 'cancelled'])->with(['project', 'assignee'])->latest()->take(10)->get();

        $teamMembers = User::whereHas('employee', fn($q) => $q->where('team_leader_id', $user->id))
            ->with(['todayWorkSession'])->get();

        $pendingReports = DailyReport::where('status', 'pending')
            ->whereHas('user.employee', fn($q) => $q->where('team_leader_id', $user->id))
            ->with('user')->latest()->take(5)->get();

        $pendingLeaves = Leave::where('team_leader_status', null)
            ->whereHas('user.employee', fn($q) => $q->where('team_leader_id', $user->id))
            ->with('user')->latest()->take(5)->get();

        return view('dashboard.team-leader', compact('user', 'myProjects', 'myTasks', 'teamMembers', 'pendingReports', 'pendingLeaves'));
    }

    private function employeeDashboard($user)
    {
        $today = Carbon::today();
        $todaySession = $user->todayWorkSession;

        $myTasks = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['project', 'timeLogs' => fn($q) => $q->where('status', 'running')])
            ->orderBy('deadline')->take(10)->get();

        $completedToday = Task::where('assigned_to', $user->id)
            ->where('status', 'completed')->whereDate('updated_at', $today)->count();

        $todayReport = DailyReport::where('user_id', $user->id)->whereDate('date', $today)->first();

        $upcomingDeadlines = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereBetween('deadline', [$today, $today->copy()->addDays(7)])
            ->with('project')->orderBy('deadline')->get();

        return view('dashboard.employee', compact('user', 'todaySession', 'myTasks', 'completedToday', 'todayReport', 'upcomingDeadlines'));
    }

    private function hrDashboard($user)
    {
        $today = Carbon::today();
        $stats = [
            'total_employees' => \App\Models\Employee::where('status', 'active')->count(),
            'present_today' => Attendance::whereDate('date', $today)->where('status', 'present')->count(),
            'absent_today' => Attendance::whereDate('date', $today)->where('status', 'absent')->count(),
            'pending_leaves' => Leave::where('status', 'pending')->orWhere('status', 'team_leader_approved')->count(),
        ];
        $pendingLeaves = Leave::whereIn('status', ['pending', 'team_leader_approved'])->with('user')->latest()->take(8)->get();
        return view('dashboard.hr', compact('user', 'stats', 'pendingLeaves'));
    }

    private function accountsDashboard($user)
    {
        $stats = [
            'pending_invoices' => Invoice::whereIn('status', ['pending', 'partially_paid'])->count(),
            'overdue_invoices' => Invoice::where('status', '!=', 'paid')->whereDate('due_date', '<', now())->count(),
            'total_pending_amount' => Invoice::whereIn('status', ['pending', 'partially_paid', 'overdue'])->sum('balance_amount'),
            'this_month_income' => \App\Models\Payment::whereMonth('payment_date', now()->month)->sum('amount'),
        ];
        $pendingInvoices = Invoice::with(['client', 'project'])->whereIn('status', ['pending', 'partially_paid'])->latest()->take(8)->get();
        return view('dashboard.accounts', compact('user', 'stats', 'pendingInvoices'));
    }

    private function clientDashboard($user)
    {
        // Find client linked to this user
        $client = \App\Models\Client::where('email', $user->email)->first();
        $projects = $client ? Project::where('client_id', $client->id)->get() : collect();
        $invoices = $client ? Invoice::where('client_id', $client->id)->latest()->get() : collect();
        $tickets = $client ? \App\Models\SupportTicket::where('client_id', $client->id)->latest()->get() : collect();
        return view('dashboard.client', compact('user', 'client', 'projects', 'invoices', 'tickets'));
    }

    private function telecallerDashboard($user)
    {
        $today = Carbon::today();

        // Total leads assigned in their rooms
        $totalLeads = \App\Models\Lead::forUser($user)->count();

        // Calls completed (logged by this telecaller)
        $callsCompleted = \App\Models\LeadCall::where('telecaller_id', $user->id)->count();

        // Pending calls (leads in their rooms with no calls logged)
        $pendingCalls = \App\Models\Lead::forUser($user)
            ->whereDoesntHave('calls')
            ->count();

        // Follow-up calls today
        $followUpsToday = \App\Models\LeadFollowUp::where('user_id', $user->id)
            ->whereDate('next_follow_up', $today)
            ->count();

        // Converted leads
        $convertedLeads = \App\Models\Lead::forUser($user)
            ->where('status', 'converted')
            ->count();

        // Missed/failed calls (Logged calls with status != Connected)
        $failedCalls = \App\Models\LeadCall::where('telecaller_id', $user->id)
            ->where('status', '!=', 'Connected')
            ->count();

        $stats = [
            'total_leads' => $totalLeads,
            'calls_completed' => $callsCompleted,
            'pending_calls' => $pendingCalls,
            'follow_ups_today' => $followUpsToday,
            'converted_leads' => $convertedLeads,
            'failed_calls' => $failedCalls,
        ];

        // Upcoming followups for today/future
        $upcomingFollowUps = \App\Models\LeadFollowUp::with('lead')
            ->where('user_id', $user->id)
            ->whereDate('next_follow_up', '>=', $today)
            ->whereHas('lead', fn($q) => $q->forUser($user))
            ->orderBy('next_follow_up')
            ->take(5)
            ->get();

        // Recent calls logged
        $recentCalls = \App\Models\LeadCall::with('lead')
            ->where('telecaller_id', $user->id)
            ->whereHas('lead', fn($q) => $q->forUser($user))
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.telecaller', compact('user', 'stats', 'upcomingFollowUps', 'recentCalls'));
    }
}
