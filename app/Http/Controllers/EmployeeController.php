<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with(['user.role', 'department', 'designation', 'teamLeader'])
            ->when($request->search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%")))
            ->when($request->department, fn($q) => $q->where('department_id', $request->department))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        $departments = Department::where('status', 'active')->get();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->with('designations')->get();
        $roles = Role::all();
        $teamLeaders = User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        return view('employees.create', compact('departments', 'roles', 'teamLeaders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'personal_email' => 'nullable|email|max:255',
            'role_id'        => 'required|exists:roles,id',
            'department_id'  => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'joining_date'   => 'required|date',
            'employee_code'  => 'nullable|unique:employees,employee_code',
            'salary'         => 'nullable|numeric',
            'avatar'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'google_drive_link' => 'nullable|url',
        ]);

        $plainPassword = $request->password ?? 'Password@123';

        $userData = [
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($plainPassword),
            'role_id'  => $request->role_id,
            'status'   => 'active',
            'email_verified_at' => now(),
        ];

        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($userData);

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\WelcomeEmployeeMail($user, $plainPassword)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to send welcome email to employee: " . $e->getMessage());
        }

        Employee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code'  => $request->employee_code ?? 'EMP' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'department_id'  => $request->department_id,
                'designation_id' => $request->designation_id,
                'joining_date'   => $request->joining_date,
                'team_leader_id' => $request->team_leader_id,
                'phone'          => $request->phone,
                'personal_email' => $request->personal_email,
                'salary'         => $request->salary ?? 0,
                'is_applicable_for_salary' => $request->boolean('is_applicable_for_salary', true),
                'salary_type'    => $request->salary_type ?? 'monthly',
                'google_drive_link' => $request->google_drive_link,
                'status'         => 'active',
            ]
        );

        \App\Models\ActivityLog::log('employee_created', "Created employee: {$user->name}");

        return redirect()->route('employees.index')->with('success', 'Employee created successfully!');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user.role', 'department', 'designation', 'teamLeader', 'user.workSessions.timeLogs', 'user.leaves', 'user.dailyReports']);
        
        $user = $employee->user;
        $month = now()->month;
        $year = now()->year;

        $presentDays = \App\Models\Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->whereIn('status', ['present', 'office'])
            ->count();

        $lateDays = \App\Models\Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->where('status', 'late')
            ->count();

        $absentDays = \App\Models\Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->where('status', 'absent')
            ->count();

        $halfDays = \App\Models\Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->where('status', 'half_day')
            ->count();

        $approvedLeaves = \App\Models\Leave::where('user_id', $user->id)
            ->whereMonth('from_date', $month)->whereYear('from_date', $year)
            ->where('status', 'hr_approved')
            ->count();

        $totalMinutesWorked = \App\Models\Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->sum('total_minutes');
        $totalHoursWorked = round($totalMinutesWorked / 60, 1);

        $myPayslips = \App\Models\SalaryDisbursal::where('employee_id', $employee->id)->orderBy('year', 'desc')->orderBy('month', 'desc')->take(5)->get();

        return view('employees.show', compact(
            'employee', 'presentDays', 'lateDays', 'absentDays', 'halfDays', 'approvedLeaves', 'totalHoursWorked', 'myPayslips'
        ));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('status', 'active')->with('designations')->get();
        $roles = Role::all();
        $teamLeaders = User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        return view('employees.edit', compact('employee', 'departments', 'roles', 'teamLeaders'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $employee->user_id,
            'personal_email' => 'nullable|email|max:255',
            'department_id'  => 'required|exists:departments,id',
            'password'       => 'nullable|string|min:8',
            'avatar'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'google_drive_link' => 'nullable|url',
        ]);

        $userData = [
            'name'    => $request->name,
            'email'   => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($employee->user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->user->avatar);
            }
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $employee->user->update($userData);

         $employee->update([
            'department_id'  => $request->department_id,
            'designation_id' => $request->designation_id,
            'team_leader_id' => $request->team_leader_id,
            'phone'          => $request->phone,
            'personal_email' => $request->personal_email,
            'salary'         => $request->salary ?? 0,
            'is_applicable_for_salary' => $request->boolean('is_applicable_for_salary', true),
            'joining_date'   => $request->joining_date,
            'google_drive_link' => $request->google_drive_link,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
    }

    public function destroy(Employee $employee)
    {
        $employee->user->update(['status' => 'inactive']);
        $employee->update(['status' => 'inactive']);
        return redirect()->route('employees.index')->with('success', 'Employee deactivated.');
    }

    public function toggleStatus(Employee $employee)
    {
        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        $employee->update(['status' => $newStatus]);
        $employee->user->update(['status' => $newStatus]);
        return back()->with('success', 'Employee status updated!');
    }

    public function getPermissions(Employee $employee)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $user = $employee->user;
        $roles = Role::with('permissions:id')->get(['id', 'name']);
        
        $allPermissions = \App\Models\Permission::all();
        $userDirectPermissionIds = $user->permissions()->pluck('permissions.id')->toArray();
        $rolePermissionIds = $user->role ? $user->role->permissions()->pluck('permissions.id')->toArray() : [];

        $grouped = [];
        foreach ($allPermissions as $permission) {
            $module = $permission->module ?? 'Other';
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            
            $checked = $user->has_custom_permissions
                ? in_array($permission->id, $userDirectPermissionIds)
                : in_array($permission->id, $rolePermissionIds);

            $grouped[$module][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'checked' => $checked
            ];
        }

        return response()->json([
            'roles' => $roles,
            'current_role_id' => $user->role_id,
            'permissions' => $grouped
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function updatePermissions(Request $request, Employee $employee)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $user = $employee->user;

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $user->update([
            'role_id' => $request->role_id,
            'has_custom_permissions' => true
        ]);

        $user->permissions()->sync($request->permissions ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Roles and permissions updated successfully!'
        ]);
    }

    public function loginAs(Employee $employee)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can use this feature.');
        }

        $targetUser = $employee->user;

        if (!$targetUser) {
            return back()->with('error', 'No user account linked to this employee.');
        }

        if ($targetUser->isSuperAdmin()) {
            return back()->with('error', 'Cannot impersonate another super admin account.');
        }

        // Store the original admin ID so they can return to their account later
        session(['impersonating_from' => auth()->id()]);

        \App\Models\ActivityLog::log(
            'impersonation_started',
            'Super admin logged in as: ' . $targetUser->name . ' (' . $targetUser->email . ')'
        );

        auth()->login($targetUser);

        return redirect('/dashboard')->with('info', 'You are now logged in as ' . $targetUser->name . '. Use "Return to my account" to switch back.');
    }
    public function returnAccount()
    {
        $originalAdminId = session('impersonating_from');

        if (!$originalAdminId) {
            return redirect('/dashboard')->with('error', 'No impersonation session found.');
        }

        $adminUser = \App\Models\User::find($originalAdminId);

        if (!$adminUser || !$adminUser->isSuperAdmin()) {
            session()->forget('impersonating_from');
            return redirect()->route('login')->with('error', 'Original admin account not found.');
        }

        \App\Models\ActivityLog::log(
            'impersonation_ended',
            'Super admin returned from impersonating: ' . auth()->user()->name . ' (' . auth()->user()->email . ')'
        );

        session()->forget('impersonating_from');

        auth()->login($adminUser);

        return redirect('/dashboard')->with('success', 'Welcome back, ' . $adminUser->name . '!');
    }
}
