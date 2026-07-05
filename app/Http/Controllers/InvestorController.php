<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investor;
use App\Models\InvestorTransaction;
use App\Models\SalaryDisbursal;
use Carbon\Carbon;

class InvestorController extends Controller
{
    private function checkAccess()
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdminOrAbove() && !$user->isAccounts())) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->checkAccess();
        $investors = Investor::latest()->paginate(15);
        return view('investors.index', compact('investors'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        Investor::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'description'     => $request->description,
            'opening_balance' => $request->opening_balance ?? 0.00,
            'status'          => 'active',
            'company_id'      => auth()->user()->company_id,
        ]);

        return redirect()->route('investors.index')->with('success', 'Investor profile created successfully!');
    }

    public function show(Investor $investor)
    {
        $this->checkAccess();

        // 1. Fetch Salary Disbursals paid using this investor's fund
        $salaries = SalaryDisbursal::with('employee.user')
            ->where('payment_method', 'Investor: ' . $investor->name)
            ->where('status', 'paid')
            ->get()
            ->map(function($s) {
                $monthName = date('F', mktime(0, 0, 0, $s->month, 1));
                return (object) [
                    'date'        => $s->payment_date,
                    'type'        => 'Debit',
                    'category'    => 'Salary Disbursal',
                    'reference'   => 'SLIP-' . $s->id,
                    'description' => 'Salary disbursed to ' . ($s->employee->user->name ?? 'Employee') . ' for ' . $monthName . ' ' . $s->year,
                    'in'          => 0,
                    'out'         => $s->net_salary,
                ];
            });

        // 2. Fetch manual transactions (Credits and Debits)
        $txns = InvestorTransaction::where('investor_id', $investor->id)
            ->get()
            ->map(function($t) {
                return (object) [
                    'date'        => $t->date,
                    'type'        => $t->type,
                    'category'    => $t->type === 'Credit' ? 'Deposit' : 'Withdrawal',
                    'reference'   => $t->reference ?? ('TXN-' . $t->id),
                    'description' => $t->description ?? 'Manual Transaction',
                    'in'          => $t->type === 'Credit' ? $t->amount : 0,
                    'out'         => $t->type === 'Debit' ? $t->amount : 0,
                ];
            });

        // Combine all statements
        $transactions = $txns->concat($salaries);

        // Sort chronologically (date ascending) to calculate a running balance
        $sorted = $transactions->sortBy(function($txn) {
            if (!$txn->date) {
                return '1970-01-01_' . $txn->reference;
            }
            $dt = ($txn->date instanceof Carbon) ? $txn->date : Carbon::parse($txn->date);
            return $dt->format('Y-m-d') . '_' . $txn->reference;
        })->values();

        $runningBalance = floatval($investor->opening_balance);
        foreach ($sorted as $txn) {
            $runningBalance += floatval($txn->in) - floatval($txn->out);
            $txn->running_balance = $runningBalance;
        }

        // Current balance is the final running balance
        $currentBalance = $runningBalance;

        // Reverse to show latest transaction first
        $statement = $sorted->reverse()->values();

        return view('investors.show', compact('investor', 'statement', 'currentBalance'));
    }

    public function edit(Investor $investor)
    {
        $this->checkAccess();
        return response()->json($investor);
    }

    public function update(Request $request, Investor $investor)
    {
        $this->checkAccess();
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
            'status'          => 'required|in:active,inactive',
        ]);

        $investor->update([
            'name'            => $request->name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'description'     => $request->description,
            'opening_balance' => $request->opening_balance ?? 0.00,
            'status'          => $request->status,
        ]);

        return redirect()->route('investors.index')->with('success', 'Investor profile updated successfully!');
    }

    public function destroy(Investor $investor)
    {
        $this->checkAccess();
        $investor->delete();
        return redirect()->route('investors.index')->with('success', 'Investor deleted successfully!');
    }

    public function storeTransaction(Request $request, Investor $investor)
    {
        $this->checkAccess();
        $request->validate([
            'type'        => 'required|in:Credit,Debit',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'reference'   => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        InvestorTransaction::create([
            'investor_id' => $investor->id,
            'type'        => $request->type,
            'amount'      => $request->amount,
            'date'        => $request->date,
            'reference'   => $request->reference,
            'description' => $request->description,
        ]);

        $msg = $request->type === 'Credit' ? 'Money added successfully!' : 'Money deducted successfully!';
        return redirect()->route('investors.show', $investor)->with('success', $msg);
    }
}
