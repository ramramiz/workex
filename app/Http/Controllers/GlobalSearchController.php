<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Employee;
use App\Models\Invoice;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q     = trim($request->get('q', ''));
        $user  = auth()->user();
        $today = \Carbon\Carbon::today();

        $results = [];

        // ── DASHBOARD METRICS & STATS ───────────────────────────────────
        $statsData = [];
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isHR() || $user->isAccounts() || $user->isTeamLeader()) {
            $statsData = [
                [
                    'title' => 'Working Today (Attendance)',
                    'value' => \App\Models\WorkSession::whereDate('date', $today)->orWhere('status', 'active')->distinct('user_id')->count() . ' employees',
                    'desc'  => 'Staff members checked in or active today',
                    'tags'  => ['working', 'attendance', 'employees', 'active', 'online', 'checkin', 'today', 'present'],
                    'url'   => route('attendance.index'),
                    'icon'  => 'bi-person-check-fill',
                    'color' => '#10b981',
                ],
                [
                    'title' => 'Active Projects',
                    'value' => Project::whereIn('status', ['planning', 'design', 'development', 'testing', 'client_review'])->count() . ' projects',
                    'desc'  => 'Projects currently in progress',
                    'tags'  => ['active', 'projects', 'planning', 'design', 'development', 'testing'],
                    'url'   => route('projects.index'),
                    'icon'  => 'bi-folder-fill',
                    'color' => '#6366f1',
                ],
                [
                    'title' => 'Delayed Projects',
                    'value' => Project::whereNotIn('status', ['completed', 'delivered', 'cancelled', 'completed_started_amc'])->whereDate('deadline', '<', $today)->count() . ' projects',
                    'desc'  => 'Projects past deadline',
                    'tags'  => ['delayed', 'late', 'projects', 'deadline', 'past'],
                    'url'   => route('projects.index'),
                    'icon'  => 'bi-exclamation-triangle-fill',
                    'color' => '#ef4444',
                ],
                [
                    'title' => 'Pending Leaves',
                    'value' => \App\Models\Leave::where('status', 'pending')->count() . ' requests',
                    'desc'  => 'Leave requests awaiting review',
                    'tags'  => ['pending', 'leaves', 'requests', 'vacation', 'absent'],
                    'url'   => route('leaves.index'),
                    'icon'  => 'bi-calendar-x-fill',
                    'color' => '#f59e0b',
                ],
                [
                    'title' => 'Open Bugs',
                    'value' => \App\Models\Bug::whereIn('status', ['open', 'assigned', 'in_progress'])->count() . ' bugs',
                    'desc'  => 'Bugs currently active',
                    'tags'  => ['open', 'bugs', 'issues', 'errors', 'tracker'],
                    'url'   => route('bugs.index'),
                    'icon'  => 'bi-bug-fill',
                    'color' => '#ef4444',
                ],
                [
                    'title' => 'Pending Invoices',
                    'value' => Invoice::whereIn('status', ['pending', 'partially_paid'])->count() . ' invoices',
                    'desc'  => 'Invoices pending payment',
                    'tags'  => ['pending', 'invoices', 'bills', 'unpaid', 'finance'],
                    'url'   => route('invoices.index'),
                    'icon'  => 'bi-file-earmark-diff-fill',
                    'color' => '#f59e0b',
                ],
                [
                    'title' => 'Tasks In Review',
                    'value' => Task::where('status', 'review')->count() . ' tasks',
                    'desc'  => 'Tasks submitted for review',
                    'tags'  => ['tasks', 'review', 'pending', 'audit'],
                    'url'   => route('tasks.approved'),
                    'icon'  => 'bi-clipboard-check-fill',
                    'color' => '#06b6d4',
                ],
                [
                    'title' => 'Active Staff members',
                    'value' => \App\Models\Employee::where('status', 'active')->count() . ' active',
                    'desc'  => 'Total active employees registered',
                    'tags'  => ['employees', 'staff', 'active', 'hr', 'people'],
                    'url'   => route('employees.index'),
                    'icon'  => 'bi-people-fill',
                    'color' => '#8b5cf6',
                ],
            ];
        }

        // Search through dashboard metrics if query matches
        if (strlen($q) >= 2) {
            $qLower = strtolower($q);
            foreach ($statsData as $stat) {
                $match = false;
                if (str_contains(strtolower($stat['title']), $qLower) || str_contains(strtolower($stat['desc']), $qLower)) {
                    $match = true;
                } else {
                    foreach ($stat['tags'] as $tag) {
                        if (str_contains($tag, $qLower)) {
                            $match = true;
                            break;
                        }
                    }
                }
                if ($match) {
                    $results[] = [
                        'type'  => 'dashboard_metric',
                        'icon'  => $stat['icon'],
                        'color' => $stat['color'],
                        'bg'    => 'rgba(99,102,241,0.12)',
                        'title' => $stat['title'] . ': ' . $stat['value'],
                        'desc'  => $stat['desc'],
                        'badge' => 'Dashboard Metric',
                        'url'   => $stat['url'],
                    ];
                }
            }
        } else {
            foreach ($statsData as $stat) {
                $results[] = [
                    'type'  => 'dashboard_metric',
                    'icon'  => $stat['icon'],
                    'color' => $stat['color'],
                    'bg'    => 'rgba(99,102,241,0.12)',
                    'title' => $stat['title'] . ': ' . $stat['value'],
                    'desc'  => $stat['desc'],
                    'badge' => 'Dashboard Metric',
                    'url'   => $stat['url'],
                ];
            }
        }

        if (strlen($q) < 2) {
            return response()->json(['results' => $results]);
        }

        $isStaff = !$user->isClient();

        // ── TASKS ──────────────────────────────────────────────────────
        if ($isStaff) {
            $taskQuery = Task::where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhereHas('project', function($p) use ($q) {
                          $p->where('name', 'like', "%{$q}%")
                            ->orWhereHas('client', function($c) use ($q) {
                                $c->where('company_name', 'like', "%{$q}%")
                                  ->orWhere('contact_person', 'like', "%{$q}%");
                            });
                      });
            })->withoutGlobalScopes();

            if (!$user->isLeaderOrAbove()) {
                $taskQuery->where('assigned_to', $user->id);
            } elseif ($user->isTeamLeader()) {
                $taskQuery->where(function ($sq) use ($user) {
                    $sq->where('assigned_to', $user->id)
                       ->orWhereHas('assignee.role', fn($r) => $r->where('slug', '!=', 'telecaller'));
                });
            }

            $tasks = $taskQuery->with('project:id,name', 'assignee:id,name')
                               ->select('id', 'title', 'status', 'priority', 'project_id', 'assigned_to')
                               ->limit(6)
                               ->get();

            foreach ($tasks as $task) {
                $statusColor = match ($task->status) {
                    'completed'       => '#10b981',
                    'in_progress'     => '#6366f1',
                    'pending'         => '#f59e0b',
                    'cancelled'       => '#ef4444',
                    default           => '#94a3b8',
                };
                $results[] = [
                    'type'    => 'task',
                    'icon'    => 'bi-check2-square',
                    'color'   => $statusColor,
                    'bg'      => 'rgba(99,102,241,0.12)',
                    'title'   => $task->title,
                    'desc'    => ($task->project?->name ?? 'No Project') . ' · ' . ucfirst(str_replace('_', ' ', $task->status)),
                    'badge'   => 'Task',
                    'url'     => route('tasks.show', $task->id),
                ];
            }
        }

        // ── PROJECTS ───────────────────────────────────────────────────
        if ($isStaff) {
            $projectQuery = Project::withoutGlobalScope('not_discontinued')->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('project_code', 'like', "%{$q}%")
                      ->orWhere('url', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhereHas('client', function($c) use ($q) {
                          $c->where('company_name', 'like', "%{$q}%")
                            ->orWhere('contact_person', 'like', "%{$q}%");
                      });
            });

            if ($user->isEmployee()) {
                $projectQuery->where(function($sq) use ($user) {
                    $sq->where('manager_id', $user->id)
                       ->orWhere('team_leader_id', $user->id)
                       ->orWhereHas('tasks', fn($t) => $t->where('assigned_to', $user->id));
                });
            }

            $projects = $projectQuery->with('client:id,company_name')
                ->select('id', 'name', 'project_code', 'status', 'client_id')
                ->limit(6)
                ->get();

            foreach ($projects as $project) {
                $statusColor = match ($project->status) {
                    'completed'      => '#10b981',
                    'in_progress'    => '#6366f1',
                    'pending'        => '#f59e0b',
                    'cancelled'      => '#ef4444',
                    default          => '#94a3b8',
                };
                $results[] = [
                    'type'  => 'project',
                    'icon'  => 'bi-kanban-fill',
                    'color' => $statusColor,
                    'bg'    => 'rgba(99,102,241,0.12)',
                    'title' => $project->name,
                    'desc'  => ($project->client?->company_name ?? 'No Client') . ' · ' . ucfirst(str_replace('_', ' ', $project->status)),
                    'badge' => 'Project',
                    'url'   => route('projects.show', $project->id),
                ];
            }
        }

        // ── CLIENTS ────────────────────────────────────────────────────
        if ($isStaff) {
            $clients = Client::where(function ($query) use ($q) {
                $query->where('company_name', 'like', "%{$q}%")
                      ->orWhere('contact_person', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->select('id', 'company_name', 'contact_person', 'phone', 'status')
            ->limit(5)
            ->get();

            foreach ($clients as $client) {
                $results[] = [
                    'type'  => 'client',
                    'icon'  => 'bi-building',
                    'color' => '#8b5cf6',
                    'bg'    => 'rgba(139,92,246,0.12)',
                    'title' => $client->company_name,
                    'desc'  => ($client->contact_person ?? '') . ($client->phone ? ' · ' . $client->phone : ''),
                    'badge' => 'Client',
                    'url'   => route('clients.show', $client->id),
                ];
            }
        }

        // ── LEADS ──────────────────────────────────────────────────────
        if ($user->isAdminOrAbove() || $user->isTelecaller() || $user->isTeamLeader()) {
            $leads = Lead::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('company', 'like', "%{$q}%");
            })
            ->select('id', 'name', 'phone', 'status', 'company')
            ->limit(4)
            ->get();

            foreach ($leads as $lead) {
                $results[] = [
                    'type'  => 'lead',
                    'icon'  => 'bi-funnel-fill',
                    'color' => '#f59e0b',
                    'bg'    => 'rgba(245,158,11,0.12)',
                    'title' => $lead->name,
                    'desc'  => ($lead->company ? $lead->company . ' · ' : '') . $lead->phone . ' · ' . ucfirst($lead->status ?? 'New'),
                    'badge' => 'Lead',
                    'url'   => route('leads.show', $lead->id),
                ];
            }
        }

        // ── EMPLOYEES ──────────────────────────────────────────────────
        if ($isStaff) {
            $employees = \App\Models\User::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->with('role:id,name')
            ->select('id', 'name', 'email', 'role_id')
            ->limit(4)
            ->get();

            foreach ($employees as $emp) {
                $results[] = [
                    'type'  => 'employee',
                    'icon'  => 'bi-person-fill',
                    'color' => '#06b6d4',
                    'bg'    => 'rgba(6,182,212,0.12)',
                    'title' => $emp->name,
                    'desc'  => $emp->email . ' · ' . ($emp->role?->name ?? 'User'),
                    'badge' => 'Employee',
                    'url'   => route('employees.show', $emp->id),
                ];
            }
        }

        // ── INVOICES ───────────────────────────────────────────────────
        if ($user->isAdminOrAbove() || $user->isAccounts()) {
            $invoices = Invoice::where(function ($query) use ($q) {
                $query->where('invoice_number', 'like', "%{$q}%")
                      ->orWhereHas('client', fn($c) => $c->where('company_name', 'like', "%{$q}%"));
            })
            ->with('client:id,company_name')
            ->select('id', 'invoice_number', 'total_amount', 'status', 'client_id')
            ->limit(4)
            ->get();

            foreach ($invoices as $inv) {
                $results[] = [
                    'type'  => 'invoice',
                    'icon'  => 'bi-receipt',
                    'color' => '#10b981',
                    'bg'    => 'rgba(16,185,129,0.12)',
                    'title' => 'Invoice #' . $inv->invoice_number,
                    'desc'  => ($inv->client?->company_name ?? '') . ' · ₹' . number_format($inv->total_amount, 0) . ' · ' . ucfirst($inv->status),
                    'badge' => 'Invoice',
                    'url'   => route('invoices.show', $inv->id),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
