<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\LeadFollowUp;
use Carbon\Carbon;

class PerformanceReportController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();

        // Admin/Super Admin/HR/Team Leader can view reports of all telecallers
        $isManager = $currentUser->isAdminOrAbove() || $currentUser->isHR() || $currentUser->isTeamLeader();

        $telecallers = [];
        if ($isManager) {
            $telecallers = User::whereHas('role', fn($q) => $q->where('slug', 'telecaller'))->get();
        }

        $targetUserId = $request->input('telecaller_id', $currentUser->id);

        if (!$isManager && $targetUserId != $currentUser->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        $telecaller = User::findOrFail($targetUserId);

        // Date filter
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();

        // Calculate performance stats
        $totalLeads = Lead::forUser($telecaller)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $callsCount = LeadCall::where('telecaller_id', $telecaller->id)
            ->whereBetween('call_date_time', [$startDate, $endDate])
            ->count();

        $convertedCount = Lead::forUser($telecaller)
            ->where('status', 'converted')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $interestedCount = Lead::forUser($telecaller)
            ->where('status', 'interested')
            ->count();

        $pendingLeadsCount = Lead::forUser($telecaller)
            ->whereIn('status', ['new', 'following_up', 'call_back_later', 'follow_up_required'])
            ->count();

        $conversionRate = $totalLeads > 0 ? round(($convertedCount / $totalLeads) * 100, 2) : 0;

        // Follow ups stats
        $totalFollowUps = LeadFollowUp::where('user_id', $telecaller->id)
            ->whereBetween('next_follow_up', [$startDate->toDateString(), $endDate->toDateString()])
            ->count();

        $completedFollowUps = LeadFollowUp::where('user_id', $telecaller->id)
            ->where('status', 'completed')
            ->whereBetween('next_follow_up', [$startDate->toDateString(), $endDate->toDateString()])
            ->count();

        $followUpCompletionRate = $totalFollowUps > 0 ? round(($completedFollowUps / $totalFollowUps) * 100, 2) : 0;

        // Calls logged per day chart data
        $callsPerDay = LeadCall::where('telecaller_id', $telecaller->id)
            ->whereBetween('call_date_time', [$startDate, $endDate])
            ->selectRaw('DATE(call_date_time) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.performance', compact(
            'telecallers',
            'telecaller',
            'isManager',
            'startDate',
            'endDate',
            'totalLeads',
            'callsCount',
            'convertedCount',
            'interestedCount',
            'pendingLeadsCount',
            'conversionRate',
            'totalFollowUps',
            'completedFollowUps',
            'followUpCompletionRate',
            'callsPerDay'
        ));
    }
}
