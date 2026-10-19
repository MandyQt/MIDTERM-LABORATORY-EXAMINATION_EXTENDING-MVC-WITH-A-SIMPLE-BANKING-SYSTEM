<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientFundsException;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $accountIds = auth()->user()->accounts()->pluck('id');

        $transactions = Transaction::with('account')
            ->whereIn('account_id', $accountIds)
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        $accounts = auth()->user()->accounts;

        return view('transactions.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => ['required', 'exists:accounts,id'],
            'type'       => ['required', 'in:deposit,withdrawal'],
            'amount'     => ['required', 'numeric', 'min:1'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        // Custom validation: sufficient funds check for withdrawals
        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $account = Account::find($request->account_id);

            // Make sure the account belongs to the logged-in user
            if ($account->user_id !== auth()->id()) {
                $validator->errors()->add('account_id', 'This account does not belong to you.');
                return;
            }

            if ($request->type === 'withdrawal') {
                $remaining = $account->balance - $request->amount;

                if ($remaining < $account->minimum_balance) {
                    $validator->errors()->add(
                        'amount',
                        'Insufficient funds. Your balance is ' . number_format($account->balance, 2) .
                        ' and the minimum balance is ' . number_format($account->minimum_balance, 2) . '.'
                    );
                }
            }
        });

        $validated = $validator->validate();

        try {
            DB::transaction(function () use ($validated) {
                Transaction::create([
                    'account_id'       => $validated['account_id'],
                    'type'             => $validated['type'],
                    'amount'           => $validated['amount'],
                    'reference_number' => 'TXN-' . strtoupper(Str::random(10)),
                    'notes'            => $validated['notes'] ?? null,
                ]);
                // The observer updates the balance here.
                // If it throws, the whole DB::transaction rolls back.
            });
        } catch (InsufficientFundsException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction completed successfully.');
    }
}
