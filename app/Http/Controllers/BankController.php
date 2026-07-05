<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\SalaryDisbursal;
use Carbon\Carbon;

class BankController extends Controller
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
        $banks = Bank::latest()->paginate(15);
        return view('banks.index', compact('banks'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        $request->validate([
            'name'            => 'required|string|max:255',
            'account_name'    => 'nullable|string|max:255',
            'account_number'  => 'nullable|string|max:255',
            'ifsc_code'       => 'nullable|string|max:255',
            'branch'          => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        Bank::create([
            'name'            => $request->name,
            'account_name'    => $request->account_name,
            'account_number'  => $request->account_number,
            'ifsc_code'       => $request->ifsc_code,
            'branch'          => $request->branch,
            'opening_balance' => $request->opening_balance ?? 0.00,
            'status'          => 'active',
        ]);

        return redirect()->route('banks.index')->with('success', 'Bank account created successfully!');
    }

    public function show(Bank $bank)
    {
        $this->checkAccess();

        // 1. Fetch Client payments (Inflows / Credits) matching bank name
        $payments = Payment::with('invoice.client')
            ->where('payment_mode', $bank->name)
            ->get()
            ->map(function($p) {
                return (object) [
                    'date'        => $p->payment_date,
                    'type'        => 'Credit',
                    'category'    => 'Invoice Payment',
                    'reference'   => $p->payment_reference ?? ('PAY-' . $p->id),
                    'description' => 'Invoice payment received from ' . ($p->client->company_name ?? 'Client'),
                    'in'          => $p->amount,
                    'out'         => 0,
                ];
            });

        // 2. Fetch Expenses (Outflows / Debits) matching bank name
        $expenses = Expense::where('payment_mode', $bank->name)
            ->where('status', 'approved')
            ->get()
            ->map(function($e) {
                return (object) [
                    'date'        => $e->date,
                    'type'        => 'Debit',
                    'category'    => 'Expense (' . ucwords(str_replace('_', ' ', $e->category)) . ')',
                    'reference'   => 'EXP-' . $e->id,
                    'description' => $e->title . ($e->description ? ' — ' . $e->description : ''),
                    'in'          => 0,
                    'out'         => $e->amount,
                ];
            });

        // 3. Fetch Salary Disbursals (Outflows / Debits) matching bank name
        $salaries = SalaryDisbursal::with('employee.user')
            ->where('payment_method', $bank->name)
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

        // Combine all statements
        $transactions = $payments->concat($expenses)->concat($salaries);

        // Sort chronologically (date ascending) to calculate a running balance
        $sorted = $transactions->sortBy(function($txn) {
            if (!$txn->date) {
                return '1970-01-01_' . $txn->reference;
            }
            $dt = ($txn->date instanceof Carbon) ? $txn->date : Carbon::parse($txn->date);
            return $dt->format('Y-m-d') . '_' . $txn->reference;
        })->values();

        $runningBalance = floatval($bank->opening_balance);
        foreach ($sorted as $txn) {
            $runningBalance += floatval($txn->in) - floatval($txn->out);
            $txn->running_balance = $runningBalance;
        }

        // Current balance is the final running balance
        $currentBalance = $runningBalance;

        // Reverse to show latest transaction first
        $statement = $sorted->reverse()->values();

        return view('banks.show', compact('bank', 'statement', 'currentBalance'));
    }

    public function edit(Bank $bank)
    {
        $this->checkAccess();
        return response()->json($bank);
    }

    public function update(Request $request, Bank $bank)
    {
        $this->checkAccess();
        $request->validate([
            'name'            => 'required|string|max:255',
            'account_name'    => 'nullable|string|max:255',
            'account_number'  => 'nullable|string|max:255',
            'ifsc_code'       => 'nullable|string|max:255',
            'branch'          => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
            'status'          => 'required|in:active,inactive',
        ]);

        $bank->update([
            'name'            => $request->name,
            'account_name'    => $request->account_name,
            'account_number'  => $request->account_number,
            'ifsc_code'       => $request->ifsc_code,
            'branch'          => $request->branch,
            'opening_balance' => $request->opening_balance ?? 0.00,
            'status'          => $request->status,
        ]);

        return redirect()->route('banks.index')->with('success', 'Bank account updated successfully!');
    }

    public function destroy(Bank $bank)
    {
        $this->checkAccess();
        $bank->delete();
        return redirect()->route('banks.index')->with('success', 'Bank account deleted successfully!');
    }
}
