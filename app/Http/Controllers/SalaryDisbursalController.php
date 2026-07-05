<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\SalaryDisbursal;
use App\Models\Expense;
use App\Models\Setting;
use App\Models\Holiday;
use App\Models\Bank;
use Carbon\Carbon;

class SalaryDisbursalController extends Controller
{
    private function getEarliestPendingTerm()
    {
        $company = auth()->user()->company;
        
        $start = null;
        if ($company && $company->salary_dispersal_start_date) {
            $start = Carbon::parse($company->salary_dispersal_start_date);
        } else {
            $earliestJoining = Employee::where('status', 'active')
                ->where('is_applicable_for_salary', true)
                ->whereNotNull('joining_date')
                ->min('joining_date');
            
            if ($earliestJoining) {
                $start = Carbon::parse($earliestJoining);
            } else {
                $start = now()->subMonths(6);
            }
        }

        $start = $start->copy()->startOfMonth();
        $end = now()->startOfMonth();

        $maxIterations = 120;
        $iterations = 0;

        while ($start->lte($end) && $iterations < $maxIterations) {
            $iterations++;
            $m = $start->month;
            $y = $start->year;

            $cycles = ($company && $company->salary_cycle === 'twice_monthly') ? [1, 2] : [1];

            foreach ($cycles as $c) {
                $totalDaysInMonth = Carbon::create($y, $m, 1)->daysInMonth;
                $termEndDate = Carbon::create($y, $m, $c === 1 && count($cycles) > 1 ? 15 : $totalDaysInMonth);

                $eligibleEmployees = Employee::where('status', 'active')
                    ->where('is_applicable_for_salary', true)
                    ->where(function($q) use ($termEndDate) {
                        $q->whereNull('joining_date')
                          ->orWhere('joining_date', '<=', $termEndDate->toDateString());
                    })
                    ->get();

                if ($company && $company->salary_dispersal_start_date) {
                    $compStart = Carbon::parse($company->salary_dispersal_start_date);
                    $eligibleEmployees = $eligibleEmployees->filter(function($emp) use ($compStart, $termEndDate) {
                        return $compStart->lte($termEndDate);
                    });
                }

                foreach ($eligibleEmployees as $emp) {
                    $paid = SalaryDisbursal::where('employee_id', $emp->id)
                        ->where('month', $m)
                        ->where('year', $y)
                        ->where('cycle', $c)
                        ->exists();

                    if (!$paid) {
                        return [
                            'month' => $m,
                            'year' => $y,
                            'cycle' => $c
                        ];
                    }
                }
            }

            $start->addMonth();
        }

        return null;
    }

    public function index(Request $request)
    {
        $pendingTerm = null;
        if (!$request->has('month') && !$request->has('year')) {
            $pendingTerm = $this->getEarliestPendingTerm();
        }

        $month = (int) ($request->month ?? ($pendingTerm ? $pendingTerm['month'] : now()->month));
        $year  = (int) ($request->year  ?? ($pendingTerm ? $pendingTerm['year'] : now()->year));
        $cycle = 1; // Default cycle to 1 for index stats

        // Stats calculations
        $totalPaidThisMonth = SalaryDisbursal::where('month', $month)->where('year', $year)
            ->where('status', 'paid')
            ->sum('net_salary');

        $slipsCount = SalaryDisbursal::where('month', $month)->where('year', $year)
            ->where('status', 'paid')
            ->count();

        $activeEmployeesCount = Employee::where('status', 'active')->count();

        // Disbursal History
        $history = SalaryDisbursal::with('employee.user')
            ->when($request->search, function($q) use ($request) {
                $q->whereHas('employee.user', function($sq) use ($request) {
                    $sq->where('name', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        $banks = Bank::all();

        return view('admin.payroll.index', compact('month', 'year', 'totalPaidThisMonth', 'slipsCount', 'activeEmployeesCount', 'history', 'banks'));
    }

    public function create(Request $request)
    {
        $pendingTerm = null;
        if (!$request->has('month') && !$request->has('year') && !$request->has('cycle')) {
            $pendingTerm = $this->getEarliestPendingTerm();
        }

        $month = (int) ($request->month ?? ($pendingTerm ? $pendingTerm['month'] : now()->month));
        $year  = (int) ($request->year  ?? ($pendingTerm ? $pendingTerm['year'] : now()->year));
        $cycle = (int) ($request->cycle ?? ($pendingTerm ? $pendingTerm['cycle'] : 1));

        $company = auth()->user()->company;
        $totalDaysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        // Determine start and end day of the payroll period
        $startDay = 1;
        $endDay = $totalDaysInMonth;

        if ($company && $company->salary_cycle === 'twice_monthly') {
            if ($cycle === 1) {
                $startDay = 1;
                $endDay = 15;
            } else {
                $startDay = 16;
                $endDay = $totalDaysInMonth;
            }
        } else {
            $cycle = 1; // Always cycle 1 for monthly
        }

        $totalDaysInPeriod = $endDay - $startDay + 1;

        // 2. Fetch weekly off days setting
        $weekOffDaysSetting = Setting::get('week_off_days', 'sun');
        $weekOffDays = explode(',', $weekOffDaysSetting);

        // 3. Count weekly off days in this period
        $weekOffCount = 0;
        for ($d = $startDay; $d <= $endDay; $d++) {
            $date = Carbon::create($year, $month, $d);
            $dayKey = substr(strtolower($date->format('D')), 0, 3);
            if (in_array($dayKey, $weekOffDays)) {
                $weekOffCount++;
            }
        }

        // 4. Count holidays in this period (not overlapping with weekly offs)
        $monthHolidays = Holiday::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereDay('date', '>=', $startDay)
            ->whereDay('date', '<=', $endDay)
            ->get();
        $holidayCount = 0;
        foreach ($monthHolidays as $h) {
            $hDate = Carbon::parse($h->date);
            $dayKey = substr(strtolower($hDate->format('D')), 0, 3);
            if (!in_array($dayKey, $weekOffDays)) {
                $holidayCount++;
            }
        }

        // 5. Compute Total Working Days of the period
        $totalWorkingDays = $totalDaysInPeriod - $weekOffCount - $holidayCount;
        if ($totalWorkingDays <= 0) {
            $totalWorkingDays = ($company && $company->salary_cycle === 'twice_monthly') ? 13 : 26; // safety fallback
        }

        $employees = Employee::where('status', 'active')
            ->where('is_applicable_for_salary', true)
            ->with(['user', 'department', 'designation'])
            ->get();
        $payrollList = [];

        foreach ($employees as $emp) {
            if (!$emp->user) continue;

            $empStartDay = $startDay;
            $empStartDate = Carbon::create($year, $month, $empStartDay);

            // Check company salary dispersal start date
            if ($company && $company->salary_dispersal_start_date) {
                $compStartDate = Carbon::parse($company->salary_dispersal_start_date);
                if ($compStartDate->isAfter($empStartDate)) {
                    if ($compStartDate->isAfter(Carbon::create($year, $month, $endDay))) {
                        continue;
                    }
                    $empStartDay = max($empStartDay, $compStartDate->day);
                    $empStartDate = Carbon::create($year, $month, $empStartDay);
                }
            }

            // Check employee joining date
            if ($emp->joining_date) {
                $joiningDate = Carbon::parse($emp->joining_date);
                if ($joiningDate->isAfter($empStartDate)) {
                    if ($joiningDate->isAfter(Carbon::create($year, $month, $endDay))) {
                        continue;
                    }
                    $empStartDay = max($empStartDay, $joiningDate->day);
                }
            }

            $empDaysInPeriod = $endDay - $empStartDay + 1;

            // Count weekly off days in this employee's active period
            $empWeekOffCount = 0;
            for ($d = $empStartDay; $d <= $endDay; $d++) {
                $date = Carbon::create($year, $month, $d);
                $dayKey = substr(strtolower($date->format('D')), 0, 3);
                if (in_array($dayKey, $weekOffDays)) {
                    $empWeekOffCount++;
                }
            }

            // Count holidays in this employee's active period
            $empHolidayCount = 0;
            foreach ($monthHolidays as $h) {
                $hDate = Carbon::parse($h->date);
                if ($hDate->day >= $empStartDay && $hDate->day <= $endDay) {
                    $dayKey = substr(strtolower($hDate->format('D')), 0, 3);
                    if (!in_array($dayKey, $weekOffDays)) {
                        $empHolidayCount++;
                    }
                }
            }

            $empWorkingDays = $empDaysInPeriod - $empWeekOffCount - $empHolidayCount;
            if ($empWorkingDays <= 0) {
                $empWorkingDays = 1;
            }

            // Fetch actual attendance stats within the period
            $daysPresent = Attendance::where('user_id', $emp->user_id)
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->whereDay('date', '>=', $empStartDay)->whereDay('date', '<=', $endDay)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $halfDays = Attendance::where('user_id', $emp->user_id)
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->whereDay('date', '>=', $empStartDay)->whereDay('date', '<=', $endDay)
                ->where('status', 'half_day')
                ->count();

            $totalWorkedMins = Attendance::where('user_id', $emp->user_id)
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->whereDay('date', '>=', $empStartDay)->whereDay('date', '<=', $endDay)
                ->sum('total_minutes');

            $workedHours = number_format($totalWorkedMins / 60, 2);

            $leavesList = Leave::where('user_id', $emp->user_id)
                ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                ->whereDay('from_date', '>=', $empStartDay)->whereDay('from_date', '<=', $endDay)
                ->where('status', 'approved')
                ->get();
            $leavesCount = $leavesList->sum('total_days');

            $leavesDetails = $leavesList->map(function($l) {
                return [
                    'type' => ucwords(str_replace('_', ' ', $l->leave_type)),
                    'from' => $l->from_date->format('d M'),
                    'to' => $l->to_date->format('d M Y'),
                    'days' => $l->total_days
                ];
            });

            // Calculation based on payroll type
            $basicSalary = $emp->salary ?? 0;
            if ($company && $company->salary_cycle === 'twice_monthly') {
                $basicSalary = $basicSalary / 2; // Semi-monthly split
            }

            if ($empStartDay > $startDay && $totalDaysInPeriod > 0) {
                $basicSalary = ($basicSalary / $totalDaysInPeriod) * $empDaysInPeriod;
            }

            $calculatedGross = 0;
            $workedDays = $daysPresent + ($halfDays * 0.5) + $leavesCount;
            $dailyRate = 0;
            $paidDays = 0;

            $lopDeduction = 0;
            if ($emp->salary_type === 'hourly') {
                $hourlyRate = $emp->hourly_rate ?? 0;
                $calculatedGross = ($totalWorkedMins / 60) * $hourlyRate;
                $basicSalary = $calculatedGross;
            } else {
                // Standard Daily Rate = Monthly Gross / 26
                $dailyRate = ($emp->salary ?? 0) / 26;
                $lopDeduction = $leavesCount * $dailyRate;
                $calculatedGross = max(0, $basicSalary - $lopDeduction);
                $paidDays = max(0, $empDaysInPeriod - $leavesCount);
            }

            // Check if already paid for this cycle
            $existingDisbursal = SalaryDisbursal::where('employee_id', $emp->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('cycle', $cycle)
                ->first();

            $payrollList[] = (object) [
                'employee' => $emp,
                'days_present' => $daysPresent,
                'half_days' => $halfDays,
                'worked_hours' => $workedHours,
                'leaves_count' => $leavesCount,
                'leaves_details' => $leavesDetails,
                'worked_days' => $workedDays,
                'total_working_days' => $empWorkingDays,
                'basic_salary' => $basicSalary,
                'lop_deduction' => $lopDeduction,
                'calculated_gross' => $calculatedGross,
                'daily_rate' => $dailyRate,
                'paid_days' => $paidDays,
                'weekly_offs' => $empWeekOffCount,
                'holidays' => $empHolidayCount,
                'is_paid' => $existingDisbursal ? true : false,
                'disbursal' => $existingDisbursal,
            ];
        }

        // Sort: pending disbursals first (is_paid === false first)
        usort($payrollList, function($a, $b) {
            return $a->is_paid <=> $b->is_paid;
        });

        $banks = Bank::where('status', 'active')->get();
        $investors = \App\Models\Investor::where('status', 'active')->get();

        return view('admin.payroll.create', compact('month', 'year', 'cycle', 'payrollList', 'totalWorkingDays', 'banks', 'investors', 'company'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
            'cycle' => 'nullable|integer',
        ]);

        $cycle = (int) ($request->cycle ?? 1);
        $validated['cycle'] = $cycle;

        // Check duplicate
        $exists = SalaryDisbursal::where('employee_id', $validated['employee_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->where('cycle', $cycle)
            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Salary already disbursed for this period.'], 422);
            }
            return back()->with('error', 'Salary already disbursed for this period.');
        }

        $validated['payment_date'] = now();
        $validated['status'] = 'paid';
        $validated['company_id'] = auth()->user()->company_id;

        $disbursal = SalaryDisbursal::create($validated);
        $employee = Employee::with('user')->find($validated['employee_id']);
        $monthName = date('F', mktime(0, 0, 0, $validated['month'], 1));

        $company = auth()->user()->company;
        $cycleSuffix = '';
        if ($company && $company->salary_cycle === 'twice_monthly') {
            $cycleSuffix = " (Cycle {$cycle})";
        }

        // Create Expense Log automatically
        Expense::create([
            'company_id' => auth()->user()->company_id,
            'category' => 'salary',
            'title' => 'Salary Disbursal - ' . $employee->name . ' (' . $monthName . ' ' . $validated['year'] . ')' . $cycleSuffix,
            'description' => "Salary disbursed for {$monthName} {$validated['year']}{$cycleSuffix}. Method: " . ucwords(str_replace('_', ' ', $validated['payment_method'])) . ". Remarks: " . ($validated['remarks'] ?? 'None'),
            'amount' => $validated['net_salary'],
            'date' => now(),
            'added_by' => auth()->id(),
            'status' => 'paid',
        ]);

        \App\Models\ActivityLog::log('salary_disbursed', "Disbursed salary of ₹" . number_format($validated['net_salary'], 2) . " to {$employee->name} for {$monthName} {$validated['year']}");

        // Send email to employee
        $targetEmail = ($employee && $employee->personal_email) ? $employee->personal_email : ($employee && $employee->user ? $employee->user->email : null);
        if ($targetEmail) {
            try {
                \Illuminate\Support\Facades\Mail::to($targetEmail)->send(new \App\Mail\SalaryDisbursedMail($disbursal));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send salary disbursal email to employee ' . $employee->name . ': ' . $e->getMessage());
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary disbursed successfully!',
                'disbursal' => $disbursal
            ]);
        }

        return redirect()->route('admin.payroll.index')->with('success', 'Salary disbursed successfully!');
    }

    public function show(SalaryDisbursal $slip)
    {
        $user = auth()->user();
        if (!$user->isAdminOrAbove() && $slip->employee->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this payslip.');
        }

        $slip->load('employee.user', 'employee.department', 'employee.designation', 'company');
        
        // Load settings for payslip presentation
        $company = $slip->company;
        $companyName = $company && $company->name ? $company->name : Setting::get('company_name', 'WorkeX');
        $companyEmail = $company && $company->email ? $company->email : Setting::get('company_email', 'info@company.com');
        $companyPhone = $company && $company->phone ? $company->phone : Setting::get('company_phone', '+91-9999999999');
        $companyAddress = $company && $company->address ? $company->address : Setting::get('company_address', 'Your Company Address');
        $companyLogo = Setting::get('company_logo');

        // Calculate leave days for this payslip
        $month = $slip->month;
        $year = $slip->year;
        $cycle = $slip->cycle ?? 1;
        
        $totalDaysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
        $startDay = 1;
        $endDay = $totalDaysInMonth;

        if ($company && $company->salary_cycle === 'twice_monthly') {
            if ($cycle === 1) {
                $startDay = 1;
                $endDay = 15;
            } else {
                $startDay = 16;
                $endDay = $totalDaysInMonth;
            }
        }

        $leavesCount = \App\Models\Leave::where('user_id', $slip->employee->user_id)
            ->whereMonth('from_date', $month)->whereYear('from_date', $year)
            ->whereDay('from_date', '>=', $startDay)->whereDay('from_date', '<=', $endDay)
            ->where('status', 'approved')
            ->sum('total_days');

        return view('admin.payroll.show', compact('slip', 'companyName', 'companyEmail', 'companyPhone', 'companyAddress', 'companyLogo', 'leavesCount'));
    }

    public function destroy(SalaryDisbursal $slip)
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdminOrAbove())) {
            abort(403, 'Unauthorized');
        }

        // Delete associated expense log
        $monthName = date('F', mktime(0, 0, 0, $slip->month, 1));
        
        // Find expense
        Expense::where('category', 'salary')
            ->where('amount', $slip->net_salary)
            ->where('title', 'like', '%' . $slip->employee->name . '%')
            ->where('title', 'like', '%' . $monthName . '%')
            ->where('title', 'like', '%' . $slip->year . '%')
            ->delete();

        // Log activity
        \App\Models\ActivityLog::log('salary_revoked', "Revoked salary disbursal of ₹" . number_format($slip->net_salary, 2) . " for " . $slip->employee->name . " ({$monthName} {$slip->year})");

        // Delete the disbursal record
        $slip->delete();

        return redirect()->route('admin.payroll.index')->with('success', 'Salary disbursal revoked and deleted successfully!');
    }

    public function attendanceReport(Request $request, Employee $employee)
    {
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);
        $cycle = (int) ($request->cycle ?? 1);

        $company = auth()->user()->company;
        $totalDaysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        $startDay = 1;
        $endDay = $totalDaysInMonth;

        if ($company && $company->salary_cycle === 'twice_monthly') {
            if ($cycle === 1) {
                $startDay = 1;
                $endDay = 15;
            } else {
                $startDay = 16;
                $endDay = $totalDaysInMonth;
            }
        }

        // Fetch weekly off days setting
        $weekOffDaysSetting = Setting::get('week_off_days', 'sun');
        $weekOffDays = explode(',', $weekOffDaysSetting);

        $report = [];

        for ($d = $startDay; $d <= $endDay; $d++) {
            $date = Carbon::create($year, $month, $d)->toDateString();
            $carbonDate = Carbon::parse($date);
            $dayKey = substr(strtolower($carbonDate->format('D')), 0, 3);

            // 1. Check attendance
            $attendance = Attendance::where('user_id', $employee->user_id)
                ->whereDate('date', $date)
                ->first();

            // 2. Check approved leave
            $leave = Leave::where('user_id', $employee->user_id)
                ->where('status', 'approved')
                ->whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->first();

            // 3. Check holiday
            $holiday = Holiday::whereDate('date', $date)->first();

            // Determine status and description
            $status = 'Absent';
            $workedHours = '00:00';
            $details = '';
            $badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';

            if ($attendance) {
                $status = ucwords(str_replace('_', ' ', $attendance->status)); // present, late, half_day
                $workedHours = $attendance->total_hours;
                
                if ($attendance->status === 'present') {
                    $badgeClass = 'bg-success-subtle text-success border-success-subtle';
                } elseif ($attendance->status === 'late') {
                    $badgeClass = 'bg-warning-subtle text-warning border-warning-subtle';
                    $details = 'Late Check-in';
                } elseif ($attendance->status === 'half_day') {
                    $badgeClass = 'bg-info-subtle text-info border-info-subtle';
                    $details = 'Half Day Attendance';
                }
            } elseif ($leave) {
                $status = 'Leave';
                $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                $details = ucwords(str_replace('_', ' ', $leave->leave_type)) . ' (Approved)';
                if ($leave->total_days == 0.5) {
                    $details .= ' - Half Day Leave (' . ($leave->half_day_session ?? 'First Session') . ')';
                }
            } elseif (in_array($dayKey, $weekOffDays)) {
                $status = 'Weekly Off';
                $badgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
            } elseif ($holiday) {
                $status = 'Holiday';
                $badgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                $details = $holiday->name;
            }

            $report[] = [
                'date' => $carbonDate->format('d M Y'),
                'day' => $carbonDate->format('l'),
                'status' => $status,
                'badge_class' => $badgeClass,
                'worked_hours' => $workedHours,
                'details' => $details,
            ];
        }

        return response()->json([
            'success' => true,
            'employee' => [
                'name' => $employee->name,
                'code' => $employee->employee_code,
            ],
            'term' => date('F Y', mktime(0, 0, 0, $month, 1, $year)) . (($company && $company->salary_cycle === 'twice_monthly') ? " (Cycle {$cycle})" : ""),
            'report' => $report,
        ]);
    }
}
