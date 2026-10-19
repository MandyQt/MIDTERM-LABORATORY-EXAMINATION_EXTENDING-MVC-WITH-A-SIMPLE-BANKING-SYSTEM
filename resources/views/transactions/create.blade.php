<x-app-layout>
    <div class="max-w-xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">New Transaction</h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-4">
            @csrf

            <div>
                <label>Account</label>
                <select name="account_id" class="border rounded w-full p-2">
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                            {{ $account->account_number }} (Balance: {{ number_format($account->balance, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Type</label>
                <select name="type" class="border rounded w-full p-2">
                    <option value="deposit" @selected(old('type') === 'deposit')>Deposit</option>
                    <option value="withdrawal" @selected(old('type') === 'withdrawal')>Withdrawal</option>
                </select>
            </div>

            <div>
                <label>Amount</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="border rounded w-full p-2">
            </div>

            <div>
                <label>Notes</label>
                <textarea name="notes" class="border rounded w-full p-2">{{ old('notes') }}</textarea>
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
        </form>
    </div>
</x-app-layout>
