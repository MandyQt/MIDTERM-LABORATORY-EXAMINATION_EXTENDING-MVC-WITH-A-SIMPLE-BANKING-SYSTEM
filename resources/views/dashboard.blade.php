<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Bank Dashboard</h1>

        <div class="grid grid-cols-3 gap-4">
            <div class="border rounded p-4 bg-white">
                <div class="text-gray-500">Total Accounts</div>
                <div id="total-accounts" class="text-3xl font-bold">...</div>
            </div>
            <div class="border rounded p-4 bg-white">
                <div class="text-gray-500">Total Money in Bank</div>
                <div id="total-money" class="text-3xl font-bold">...</div>
            </div>
            <div class="border rounded p-4 bg-white">
                <div class="text-gray-500">Transactions Today</div>
                <div id="total-transactions" class="text-3xl font-bold">...</div>
            </div>
        </div>

        <p class="text-sm text-gray-500 mt-4">
            Last updated: <span id="last-updated">never</span>
        </p>
    </div>

    <script>
        async function fetchStats() {
            try {
                const response = await fetch('/api/dashboard/stats', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Request failed');

                const data = await response.json();

                document.getElementById('total-accounts').textContent = data.total_accounts;
                document.getElementById('total-money').textContent =
                    Number(data.total_money).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                document.getElementById('total-transactions').textContent = data.total_transactions_today;
                document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();
            } catch (error) {
                console.error('Polling error:', error);
            }
        }

        fetchStats();                     // load immediately
        setInterval(fetchStats, 30000);   // then every 30 seconds
    </script>
</x-app-layout>