<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\WorkSession;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function dailyWork(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $sessions = WorkSession::with(['user', 'timeLogs.task'])->whereDate('date', $date)->get();
        return view('reports.daily-work', compact('sessions', 'date'));
    }

    public function projectProgress(Request $request)
    {
        $projects = Project::with(['tasks', 'client', 'teamLeader'])->get();
        return view('reports.project-progress', compact('projects'));
    }

    public function attendance(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;
        $records = Attendance::with('user')->whereMonth('date', $month)->whereYear('date', $year)->get();
        return view('reports.attendance', compact('records', 'month', 'year'));
    }

    public function leaves(Request $request)
    {
        $leaves = Leave::with('user')->latest()->paginate(30);
        return view('reports.leaves', compact('leaves'));
    }

    public function payments(Request $request)
    {
        $payments = Payment::with(['client', 'invoice'])->latest()->paginate(30);
        $total = $payments->sum('amount');
        return view('reports.payments', compact('payments', 'total'));
    }

    public function profitLoss(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;
        $income   = Payment::whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount');
        $expenses = Expense::whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
        return view('reports.profit-loss', compact('income', 'expenses', 'month', 'year'));
    }

    public function export(Request $request, string $type)
    {
        // Placeholder — will implement with PhpSpreadsheet
        return back()->with('info', 'Export coming soon!');
    }
}
