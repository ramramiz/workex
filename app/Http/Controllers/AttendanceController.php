<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        if ($user->isLeaderOrAbove() || $user->isHR()) {
            $records = Attendance::with('user')
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
                ->orderBy('date', 'desc')->paginate(30);
            $employees = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee','team-leader']))->where('status','active')->get();
        } else {
            $records = Attendance::where('user_id', $user->id)->whereMonth('date', $month)->whereYear('date', $year)->orderBy('date','desc')->paginate(31);
            $employees = collect();
        }
        return view('attendance.index', compact('records', 'month', 'year', 'employees'));
    }
    public function show(Attendance $attendance) { return view('attendance.show', compact('attendance')); }
    public function edit(Attendance $attendance) { return view('attendance.edit', compact('attendance')); }
    public function update(Request $request, Attendance $attendance)
    {
        $attendance->update($request->only(['login_time','logout_time','status','notes']));
        if ($attendance->logout_time && $attendance->login_time) {
            $mins = Carbon::parse($attendance->login_time)->diffInMinutes(Carbon::parse($attendance->logout_time));
            $attendance->update(['total_minutes' => $mins]);
        }
        return back()->with('success', 'Attendance updated!');
    }
    public function report(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;
        $records = Attendance::with('user')->whereMonth('date', $month)->whereYear('date', $year)->get()->groupBy('user_id');
        return view('attendance.report', compact('records', 'month', 'year'));
    }
}
