<?php

namespace App\Observers;

use App\Exceptions\InsufficientFundsException;
use App\Models\Account;
use App\Models\Transaction;
use App\Notifications\LowBalanceNotification;
use Illuminate\Support\Facades\DB;

class AccountObserver
{
    /**
     * Runs BEFORE the transaction row is inserted.
     * Updates the balance; throwing here stops the insert.
     */
    public function creating(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // lockForUpdate prevents two simultaneous requests from
            // reading the same balance (race condition)
            $account = Account::whereKey($transaction->account_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->type === 'deposit') {
                $newBalance = bcadd((string) $account->balance, (string) $transaction->amount, 2);
            } else {
                // withdrawal or transfer subtracts
                $newBalance = bcsub((string) $account->balance, (string) $transaction->amount, 2);

                // Critical rule: negative OR below minimum_balance
                if (bccomp($newBalance, '0', 2) < 0
                    || bccomp($newBalance, (string) $account->minimum_balance, 2) < 0) {
                    throw new InsufficientFundsException();
                }
            }

            $account->update(['balance' => $newBalance]);
        });
    }

    /**
     * Runs AFTER the transaction row is saved.
     * Sends the low balance alert once (no duplicates) and resets the flag on recovery.
     */
    public function created(Transaction $transaction): void
    {
        $account = $transaction->account()->with('user')->first();
        $isLow = bccomp((string) $account->balance, (string) $account->minimum_balance, 2) <= 0;

        if (in_array($transaction->type, ['withdrawal', 'transfer'])
            && $isLow
            && $account->low_balance_notified_at === null) {
            $account->user->notify(new LowBalanceNotification($account));
            $account->update(['low_balance_notified_at' => now()]);
        }

        // Reset the flag once a deposit brings the balance back above the threshold
        if ($transaction->type === 'deposit' && ! $isLow) {
            $account->update(['low_balance_notified_at' => null]);
        }
    }
}
