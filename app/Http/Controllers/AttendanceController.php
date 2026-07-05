<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\TaskTimeLog;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Setting;
use Carbon\Carbon;
 
class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('attendance.view-own') && !$user->hasPermission('attendance.view-all')) {
            abort(403, 'Unauthorized');
        }

        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        // Determine if we are filtering by a single user
        $targetUserId = null;
        if ($user->hasPermission('attendance.view-all')) {
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
            // View all employee logs list (exclude super admin)
            $records = Attendance::with('user')
                ->whereHas('user', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', '!=', 'super-admin')))
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->orderBy('date', 'desc')->paginate(30);
        }

        // Attach task-based IN/OUT times to every record
        // Collect user_ids and dates from the current page
        $taskInOut = $this->buildTaskInOutMap($records);

        return view('attendance.index', compact('records', 'month', 'year', 'employees', 'taskInOut'));
    }
    public function show(Attendance $attendance)
    {
        $user = auth()->user();
        if (!$user->hasPermission('attendance.view-all')) {
            if (!$user->hasPermission('attendance.view-own') || $attendance->user_id !== $user->id) {
                abort(403, 'Unauthorized');
            }
        }
        return view('attendance.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        if (!auth()->user()->hasPermission('attendance.edit')) {
            abort(403, 'Unauthorized');
        }
        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        if (!auth()->user()->hasPermission('attendance.edit')) {
            abort(403, 'Unauthorized');
        }
        $attendance->update($request->only(['login_time','logout_time','status','notes']));
        if ($attendance->logout_time && $attendance->login_time) {
            $mins = Carbon::parse($attendance->login_time)->diffInMinutes(Carbon::parse($attendance->logout_time));
            $attendance->update(['total_minutes' => $mins]);
        }
        return back()->with('success', 'Attendance updated!');
    }

    public function report(Request $request)
    {
        if (!auth()->user()->hasPermission('attendance.view-all')) {
            abort(403, 'Unauthorized');
        }
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;
        $records = Attendance::with('user')
            ->whereHas('user', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', '!=', 'super-admin')))
            ->whereMonth('date', $month)->whereYear('date', $year)->get()->groupBy('user_id');
        return view('attendance.report', compact('records', 'month', 'year'));
    }

    /**
     * Build a map of task-based IN/OUT times keyed by "{user_id}_{date}".
     * IN  = earliest started_at among all task_time_logs for that user/day.
     * OUT = latest   ended_at   among all task_time_logs for that user/day.
     */
    private function buildTaskInOutMap($records): array
    {
        // Gather pairs of (user_id, date) from the records
        $pairs = [];
        foreach ($records as $rec) {
            $userId  = $rec->user_id ?? null;
            $dateStr = ($rec->date instanceof \Carbon\Carbon)
                ? $rec->date->format('Y-m-d')
                : \Carbon\Carbon::parse($rec->date)->format('Y-m-d');
            if ($userId) {
                $pairs[] = ['user_id' => $userId, 'date' => $dateStr];
            }
        }

        if (empty($pairs)) {
            return [];
        }

        $userIds = array_unique(array_column($pairs, 'user_id'));
        $dates   = array_unique(array_column($pairs, 'date'));

        // Single query: get min started_at and max ended_at per user per date
        $logs = TaskTimeLog::selectRaw(
                'user_id, DATE(started_at) as log_date, MIN(started_at) as first_start, MAX(ended_at) as last_end'
            )
            ->whereIn('user_id', $userIds)
            ->whereIn(\DB::raw('DATE(started_at)'), $dates)
            ->whereNotNull('started_at')
            ->groupBy('user_id', 'log_date')
            ->get();

        $map = [];
        foreach ($logs as $log) {
            $key = $log->user_id . '_' . $log->log_date;
            $map[$key] = [
                'in'  => $log->first_start ? \Carbon\Carbon::parse($log->first_start) : null,
                'out' => $log->last_end    ? \Carbon\Carbon::parse($log->last_end)    : null,
            ];
        }

        return $map;
    }
}
