<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Designation;

class DepartmentController extends Controller
{
    public function index() { $departments = Department::withCount('designations')->paginate(20); return view('settings.departments.index', compact('departments')); }
    public function create() { return view('settings.departments.create'); }
    public function store(Request $request) { $request->validate(['name' => 'required|string|max:255|unique:departments,name']); Department::create(['name' => $request->name, 'description' => $request->description, 'status' => 'active']); return redirect()->route('departments.index')->with('success', 'Department created!'); }
    public function edit(Department $department) { return view('settings.departments.edit', compact('department')); }
    public function update(Request $request, Department $department) { $request->validate(['name' => 'required|string|max:255|unique:departments,name,' . $department->id]); $department->update(['name' => $request->name, 'description' => $request->description, 'status' => $request->status ?? 'active']); return redirect()->route('departments.index')->with('success', 'Department updated!'); }
    public function destroy(Department $department) { $department->delete(); return redirect()->route('departments.index')->with('success', 'Department deleted.'); }
}

