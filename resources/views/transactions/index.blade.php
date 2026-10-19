<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex justify-between mb-4">
            <h1 class="text-2xl font-bold">Transactions</h1>
            <a href="{{ route('transactions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">New</a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2">Reference</th>
                    <th class="p-2">Account</th>
                    <th class="p-2">Type</th>
                    <th class="p-2">Amount</th>
                    <th class="p-2">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $t)
                    <tr class="border-t">
                        <td class="p-2">{{ $t->reference_number }}</td>
                        <td class="p-2">{{ $t->account->account_number }}</td>
                        <td class="p-2">{{ ucfirst($t->type) }}</td>
                        <td class="p-2">{{ number_format($t->amount, 2) }}</td>
                        <td class="p-2">{{ $t->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
</x-app-layout>
