<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $reports = DailyReport::with(['user', 'reviewer'])
            ->when(!$user->isAdminOrAbove(), fn($q) => $q->where('user_id', $user->id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->latest('date')->paginate(20);

        $todaySessions = \App\Models\WorkSession::with(['user', 'timeLogs.task'])
            ->where(function($q) {
                $q->whereDate('date', today())
                  ->orWhere('status', 'active');
            })
            ->when(!$user->isAdminOrAbove(), fn($q) => $q->where('user_id', $user->id))
            ->orderBy('started_at', 'desc')
            ->get();

        return view('daily-reports.index', compact('reports', 'todaySessions'));
    }
    public function create() { return view('daily-reports.create', ['today' => now()->toDateString()]); }
    public function store(Request $request)
    {
        $request->validate(['date' => 'required|date', 'completed_work' => 'required|string', 'tomorrow_plan' => 'nullable|string']);
        $existing = DailyReport::where('user_id', auth()->id())->whereDate('date', $request->date)->first();
        if ($existing) return redirect()->route('daily-reports.show', $existing)->with('warning', 'Report for this date already exists!');
        $report = DailyReport::create(array_merge($request->only(['date','completed_work','pending_work','issues_faced','tomorrow_plan','git_commit_link']), ['user_id' => auth()->id(), 'status' => 'pending']));
        return redirect()->route('daily-reports.show', $report)->with('success', 'Daily report submitted!');
    }
    public function show(DailyReport $dailyReport) { $dailyReport->load(['user', 'reviewer']); return view('daily-reports.show', ['report' => $dailyReport]); }
    public function edit(DailyReport $dailyReport) { return view('daily-reports.edit', ['report' => $dailyReport]); }
    public function update(Request $request, DailyReport $dailyReport)
    {
        $dailyReport->update($request->only(['completed_work','pending_work','issues_faced','tomorrow_plan','git_commit_link']));
        return redirect()->route('daily-reports.show', $dailyReport)->with('success', 'Report updated!');
    }
    public function destroy(DailyReport $dailyReport) { $dailyReport->delete(); return redirect()->route('daily-reports.index')->with('success', 'Report deleted.'); }
    public function approve(Request $request, DailyReport $dailyReport)
    {
        $dailyReport->update(['status' => 'approved', 'reviewer_id' => auth()->id(), 'reviewer_comment' => $request->comment, 'reviewed_at' => now()]);
        return back()->with('success', 'Report approved!');
    }
    public function reject(Request $request, DailyReport $dailyReport)
    {
        $request->validate(['comment' => 'required|string']);
        $dailyReport->update(['status' => 'rejected', 'reviewer_id' => auth()->id(), 'reviewer_comment' => $request->comment, 'reviewed_at' => now()]);
        return back()->with('success', 'Report sent back for revision.');
    }
}
