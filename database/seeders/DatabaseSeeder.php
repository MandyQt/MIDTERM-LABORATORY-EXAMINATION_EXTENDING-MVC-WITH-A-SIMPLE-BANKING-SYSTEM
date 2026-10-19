<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Login: test@example.com / password
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Account::create([
            'user_id' => $user->id,
            'account_number' => 'ACC-' . random_int(10000000, 99999999),
            'balance' => 1000.00,
        ]);
    }
}
