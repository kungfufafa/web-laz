<?php

namespace Database\Seeders;

use App\Models\Donation;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class DonationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = User::where('role', 'member')->get();
        $paymentMethods = PaymentMethod::all();

        // User donations
        Donation::factory(30)
            ->recycle($members)
            ->recycle($paymentMethods)
            ->create();

        // Guest donations
        Donation::factory(10)
            ->state([
                'user_id' => null,
                'guest_token' => 'seed-guest-token',
            ])
            ->recycle($paymentMethods)
            ->create();
    }
}
