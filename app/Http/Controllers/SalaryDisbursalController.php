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
use Carbon\Carbon;

class SalaryDisbursalController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

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

        return view('admin.payroll.index', compact('month', 'year', 'totalPaidThisMonth', 'slipsCount', 'activeEmployeesCount', 'history'));
    }

    public function create(Request $request)
    {
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        // 1. Calculate total days in selected month
        $totalDaysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        // 2. Fetch weekly off days setting
        $weekOffDaysSetting = Setting::get('week_off_days', 'sun');
        $weekOffDays = explode(',', $weekOffDaysSetting);

        // 3. Count weekly off days in this month
        $weekOffCount = 0;
        for ($d = 1; $d <= $totalDaysInMonth; $d++) {
            $date = Carbon::create($year, $month, $d);
            $dayKey = substr(strtolower($date->format('D')), 0, 3);
            if (in_array($dayKey, $weekOffDays)) {
                $weekOffCount++;
            }
        }

        // 4. Count holidays in this month (not overlapping with weekly offs)
        $monthHolidays = Holiday::whereMonth('date', $month)->whereYear('date', $year)->get();
        $holidayCount = 0;
        foreach ($monthHolidays as $h) {
            $hDate = Carbon::parse($h->date);
            $dayKey = substr(strtolower($hDate->format('D')), 0, 3);
            if (!in_array($dayKey, $weekOffDays)) {
                $holidayCount++;
            }
        }

        // 5. Compute Total Working Days of the month
        $totalWorkingDays = $totalDaysInMonth - $weekOffCount - $holidayCount;
        if ($totalWorkingDays <= 0) {
            $totalWorkingDays = 26; // safety fallback
        }

        $employees = Employee::where('status', 'active')->with(['user', 'department', 'designation'])->get();
        $payrollList = [];

        foreach ($employees as $emp) {
            if (!$emp->user) continue;

            // Fetch actual attendance stats
            $daysPresent = Attendance::where('user_id', $emp->user_id)
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->whereIn('status', ['present', 'late'])
                ->count();

            $halfDays = Attendance::where('user_id', $emp->user_id)
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->where('status', 'half_day')
                ->count();

            $totalWorkedMins = Attendance::where('user_id', $emp->user_id)
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->sum('total_minutes');

            $workedHours = number_format($totalWorkedMins / 60, 2);

            $leavesCount = Leave::where('user_id', $emp->user_id)
                ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                ->where('status', 'approved')
                ->sum('total_days');

            // Calculation based on payroll type
            $basicSalary = $emp->salary ?? 0;
            $calculatedGross = 0;
            $workedDays = $daysPresent + ($halfDays * 0.5) + $leavesCount;
            $dailyRate = 0;
            $paidDays = 0;

            if ($emp->salary_type === 'hourly') {
                $hourlyRate = $emp->hourly_rate ?? 0;
                $calculatedGross = ($totalWorkedMins / 60) * $hourlyRate;
                $basicSalary = $calculatedGross;
            } else {
                // Per-day rate: (Monthly Salary * 12) / 365
                $dailyRate = ($emp->salary * 12) / 365;

                if ($workedDays >= $totalWorkingDays) {
                    $calculatedGross = $emp->salary;
                    $paidDays = $totalDaysInMonth;
                } elseif ($workedDays == 0) {
                    $calculatedGross = 0;
                    $paidDays = 0;
                } else {
                    $paidDays = $workedDays + $weekOffCount + $holidayCount;
                    if ($paidDays > $totalDaysInMonth) {
                        $paidDays = $totalDaysInMonth;
                    }
                    $calculatedGross = $dailyRate * $paidDays;
                    if ($calculatedGross > $emp->salary) {
                        $calculatedGross = $emp->salary;
                    }
                }
            }

            // Check if already paid
            $existingDisbursal = SalaryDisbursal::where('employee_id', $emp->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            $payrollList[] = (object) [
                'employee' => $emp,
                'days_present' => $daysPresent,
                'half_days' => $halfDays,
                'worked_hours' => $workedHours,
                'leaves_count' => $leavesCount,
                'worked_days' => $workedDays,
                'total_working_days' => $totalWorkingDays,
                'basic_salary' => $basicSalary,
                'calculated_gross' => $calculatedGross,
                'daily_rate' => $dailyRate,
                'paid_days' => $paidDays,
                'weekly_offs' => $weekOffCount,
                'holidays' => $holidayCount,
                'is_paid' => $existingDisbursal ? true : false,
                'disbursal' => $existingDisbursal,
            ];
        }

        return view('admin.payroll.create', compact('month', 'year', 'payrollList', 'totalWorkingDays'));
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
            'payment_method' => 'required|in:bank_transfer,cash,cheque',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Check duplicate
        $exists = SalaryDisbursal::where('employee_id', $validated['employee_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Salary already disbursed for this month.'], 422);
            }
            return back()->with('error', 'Salary already disbursed for this month.');
        }

        $validated['payment_date'] = now();
        $validated['status'] = 'paid';
        $validated['company_id'] = auth()->user()->company_id;

        $disbursal = SalaryDisbursal::create($validated);
        $employee = Employee::with('user')->find($validated['employee_id']);
        $monthName = date('F', mktime(0, 0, 0, $validated['month'], 1));

        // Create Expense Log automatically
        Expense::create([
            'company_id' => auth()->user()->company_id,
            'category' => 'salary',
            'title' => 'Salary Disbursal - ' . $employee->name . ' (' . $monthName . ' ' . $validated['year'] . ')',
            'description' => "Salary disbursed for {$monthName} {$validated['year']}. Method: " . ucwords(str_replace('_', ' ', $validated['payment_method'])) . ". Remarks: " . ($validated['remarks'] ?? 'None'),
            'amount' => $validated['net_salary'],
            'date' => now(),
            'added_by' => auth()->id(),
            'status' => 'paid',
        ]);

        \App\Models\ActivityLog::log('salary_disbursed', "Disbursed salary of ₹" . number_format($validated['net_salary'], 2) . " to {$employee->name} for {$monthName} {$validated['year']}");

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

        $slip->load('employee.user', 'employee.department', 'employee.designation');
        
        // Load settings for payslip presentation
        $companyName = Setting::get('company_name', 'WorkeX');
        $companyEmail = Setting::get('company_email', 'info@company.com');
        $companyPhone = Setting::get('company_phone', '+91-9999999999');
        $companyAddress = Setting::get('company_address', 'Your Company Address');
        $companyLogo = Setting::get('company_logo');

        return view('admin.payroll.show', compact('slip', 'companyName', 'companyEmail', 'companyPhone', 'companyAddress', 'companyLogo'));
    }
}
