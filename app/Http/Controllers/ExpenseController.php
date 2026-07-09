<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Bank;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with(['project', 'addedBy'])
            ->when($request->project, fn($q) => $q->where('project_id', $request->project))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search, function($q) use ($request) {
                $q->where(function($sq) use ($request) {
                    $sq->where('title', 'like', '%' . $request->search . '%')
                       ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()->paginate(20);
        $projects = Project::all();
        $banks = Bank::all();
        return view('expenses.index', compact('expenses', 'projects', 'banks'));
    }

    public function create()
    {
        $projects = Project::whereNotIn('status', ['cancelled'])->get();
        $banks = Bank::where('status', 'active')->get();
        $investors = \App\Models\Investor::where('status', 'active')->get();
        return view('expenses.create', compact('projects', 'banks', 'investors'));
    }

    public function store(Request $request)
    {
        if ($request->has('expenses')) {
            $request->validate([
                'project_id'            => 'nullable|exists:projects,id',
                'date'                  => 'required|date',
                'expenses'              => 'required|array|min:1',
                'expenses.*.title'      => 'required_without:expenses.*.custom_title|nullable|string|max:255',
                'expenses.*.custom_title'=> 'nullable|string|max:255',
                'expenses.*.description'=> 'nullable|string',
                'expenses.*.amount'     => 'required|numeric|min:0',
                'expenses.*.payment_mode'=> 'required|string|max:255',
            ]);

            $projectId = $request->input('project_id');
            $date = $request->input('date');

            foreach ($request->input('expenses') as $item) {
                $title = $item['title'] ?? '';
                if ($title === 'add_new' || empty($title)) {
                    $title = $item['custom_title'] ?? 'Other Expense';
                }
                
                // Set category based on title, or map it
                $category = 'other';
                $lowerTitle = strtolower($title);
                if (str_contains($lowerTitle, 'incentive')) {
                    $category = 'salary';
                } elseif (str_contains($lowerTitle, 'food')) {
                    $category = 'office_supplies';
                } elseif (str_contains($lowerTitle, 'travel') || str_contains($lowerTitle, 'allowance')) {
                    $category = 'travel';
                } elseif (str_contains($lowerTitle, 'freight') || str_contains($lowerTitle, 'charges')) {
                    $category = 'office_supplies';
                } elseif (str_contains($lowerTitle, 'supplies')) {
                    $category = 'office_supplies';
                } elseif (str_contains($lowerTitle, 'salary')) {
                    $category = 'salary';
                } elseif (str_contains($lowerTitle, 'hosting') || str_contains($lowerTitle, 'server')) {
                    $category = 'hosting';
                } elseif (str_contains($lowerTitle, 'marketing')) {
                    $category = 'marketing';
                }

                Expense::create([
                    'project_id'   => $projectId ?: null,
                    'date'         => $date,
                    'title'        => $title,
                    'category'     => $category,
                    'description'  => $item['description'] ?? null,
                    'amount'       => $item['amount'],
                    'payment_mode' => $item['payment_mode'],
                    'added_by'     => auth()->id(),
                    'status'       => 'approved',
                ]);
            }

            return redirect()->route('expenses.index')->with('success', 'Expenses filed successfully!');
        }

        $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'date'         => 'required|date',
            'category'     => 'required|string',
            'payment_mode' => 'required|string|max:255',
        ]);

        Expense::create(array_merge(
            $request->only(['project_id', 'category', 'title', 'description', 'amount', 'payment_mode', 'date']),
            ['added_by' => auth()->id(), 'status' => 'approved']
        ));

        return redirect()->route('expenses.index')->with('success', 'Expense added!');
    }

    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $projects = Project::all();
        $banks = Bank::where('status', 'active')->get();
        $investors = \App\Models\Investor::where('status', 'active')->get();
        return view('expenses.edit', compact('expense', 'projects', 'banks', 'investors'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'date'         => 'required|date',
            'category'     => 'required|string',
            'payment_mode' => 'required|string|max:255',
        ]);

        $expense->update($request->only(['project_id', 'category', 'title', 'description', 'amount', 'payment_mode', 'date']));

        return redirect()->route('expenses.index')->with('success', 'Expense updated!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
