<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Department;

class DesignationController extends Controller
{
    public function index() { $designations = Designation::with('department')->paginate(20); return view('settings.designations.index', compact('designations')); }
    public function create() { $departments = Department::where('status','active')->get(); return view('settings.designations.create', compact('departments')); }
    public function store(Request $request) { $request->validate(['name' => 'required|string|max:255', 'department_id' => 'required|exists:departments,id']); Designation::create(['name' => $request->name, 'department_id' => $request->department_id, 'status' => 'active']); return redirect()->route('designations.index')->with('success', 'Designation created!'); }
    public function edit(Designation $designation) { $departments = Department::where('status','active')->get(); return view('settings.designations.edit', compact('designation', 'departments')); }
    public function update(Request $request, Designation $designation) { $designation->update(['name' => $request->name, 'department_id' => $request->department_id, 'status' => $request->status ?? 'active']); return redirect()->route('designations.index')->with('success', 'Designation updated!'); }
    public function destroy(Designation $designation) { $designation->delete(); return redirect()->route('designations.index')->with('success', 'Designation deleted.'); }
}
