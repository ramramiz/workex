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
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        // Determine if we are filtering by a single user
        $targetUserId = null;
        if ($user->isLeaderOrAbove() || $user->isHR()) {
            $employees = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee','team-leader']))->where('status','active')->get();
            if ($request->user_id) {
                $targetUserId = (int) $request->user_id;
            }
        } else {
            $targetUserId = $user->id;
            $employees = collect();
        }

        if ($targetUserId) {
            // Build calendar sheet for this single user
            $actualRecords = Attendance::where('user_id', $targetUserId)
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->get()
                ->keyBy(fn($item) => $item->date->format('Y-m-d'));

            $monthHolidays = Holiday::whereMonth('date', $month)->whereYear('date', $year)
                ->get()
                ->keyBy(fn($item) => $item->date->format('Y-m-d'));

            $weekOffDaysSetting = Setting::get('week_off_days', 'sun');
            $weekOffDays = explode(',', $weekOffDaysSetting);

            $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
            $recordsList = [];
            $employeeObj = User::find($targetUserId);

            for ($d = $daysInMonth; $d >= 1; $d--) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $date = \Carbon\Carbon::parse($dateStr);
                $dayOfWeek = strtolower($date->format('D'));
                $dayKey = substr($dayOfWeek, 0, 3); // 'mon', 'tue' etc.

                if ($actualRecords->has($dateStr)) {
                    $recordsList[] = $actualRecords->get($dateStr);
                } else {
                    if ($monthHolidays->has($dateStr)) {
                        $h = $monthHolidays->get($dateStr);
                        $recordsList[] = (object) [
                            'id' => null,
                            'user_id' => $targetUserId,
                            'user' => $employeeObj,
                            'date' => $date,
                            'login_time' => null,
                            'logout_time' => null,
                            'total_minutes' => null,
                            'late_minutes' => 0,
                            'type' => 'holiday',
                            'status' => 'holiday',
                            'notes' => $h->name,
                        ];
                    } elseif (in_array($dayKey, $weekOffDays)) {
                        $recordsList[] = (object) [
                            'id' => null,
                            'user_id' => $targetUserId,
                            'user' => $employeeObj,
                            'date' => $date,
                            'login_time' => null,
                            'logout_time' => null,
                            'total_minutes' => null,
                            'late_minutes' => 0,
                            'type' => 'weekly_off',
                            'status' => 'weekly_off',
                            'notes' => 'Weekly Off',
                        ];
                    } else {
                        // Absent if in the past. Pending/not logged if today or future.
                        $status = 'absent';
                        $notes = 'Absent';
                        if ($date->isFuture() || $date->isToday()) {
                            $status = 'pending';
                            $notes = '—';
                        }
                        $recordsList[] = (object) [
                            'id' => null,
                            'user_id' => $targetUserId,
                            'user' => $employeeObj,
                            'date' => $date,
                            'login_time' => null,
                            'logout_time' => null,
                            'total_minutes' => null,
                            'late_minutes' => 0,
                            'type' => 'office',
                            'status' => $status,
                            'notes' => $notes,
                        ];
                    }
                }
            }

            // Wrap in LengthAwarePaginator for view compatibility
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $col = collect($recordsList);
            $perPage = 31;
            $currentPageItems = $col->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $records = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                count($col),
                $perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
        } else {
            // View all employee logs list
            $records = Attendance::with('user')
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->orderBy('date', 'desc')->paginate(30);
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
