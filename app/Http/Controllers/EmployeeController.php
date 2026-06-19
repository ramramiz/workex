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
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'role_id'       => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
            'designation_id'=> 'nullable|exists:designations,id',
            'joining_date'  => 'required|date',
            'employee_code' => 'nullable|unique:employees,employee_code',
            'salary'        => 'nullable|numeric',
            'avatar'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
                'salary'         => $request->salary ?? 0,
                'salary_type'    => $request->salary_type ?? 'monthly',
                'status'         => 'active',
            ]
        );

        \App\Models\ActivityLog::log('employee_created', "Created employee: {$user->name}");

        return redirect()->route('employees.index')->with('success', 'Employee created successfully!');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user.role', 'department', 'designation', 'teamLeader', 'user.workSessions', 'user.leaves', 'user.dailyReports']);
        return view('employees.show', compact('employee'));
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
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $employee->user_id,
            'department_id' => 'required|exists:departments,id',
            'password'      => 'nullable|string|min:8',
            'avatar'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'salary'         => $request->salary ?? 0,
            'joining_date'   => $request->joining_date,
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
}
