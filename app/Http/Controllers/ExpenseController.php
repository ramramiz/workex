<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Project;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with(['project', 'addedBy'])
            ->when($request->project, fn($q) => $q->where('project_id', $request->project))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->latest()->paginate(20);
        $projects = Project::all();
        return view('expenses.index', compact('expenses', 'projects'));
    }
    public function create() { $projects = Project::whereNotIn('status', ['cancelled'])->get(); return view('expenses.create', compact('projects')); }
    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255', 'amount' => 'required|numeric|min:0', 'date' => 'required|date', 'category' => 'required|string']);
        Expense::create(array_merge($request->only(['project_id','category','title','description','amount','date']), ['added_by' => auth()->id(), 'status' => 'approved']));
        return redirect()->route('expenses.index')->with('success', 'Expense added!');
    }
    public function show(Expense $expense) { return view('expenses.show', compact('expense')); }
    public function edit(Expense $expense) { $projects = Project::all(); return view('expenses.edit', compact('expense', 'projects')); }
    public function update(Request $request, Expense $expense) { $expense->update($request->only(['project_id','category','title','description','amount','date'])); return back()->with('success', 'Expense updated!'); }
    public function destroy(Expense $expense) { $expense->delete(); return redirect()->route('expenses.index')->with('success', 'Expense deleted.'); }
}
