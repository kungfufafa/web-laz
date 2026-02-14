<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): array
    {
        $bankBsi = PaymentMethod::factory()->create([
            'name' => 'Bank BSI',
            'type' => 'bank_transfer',
        ]);

        $qris = PaymentMethod::factory()->create([
            'name' => 'QRIS',
            'type' => 'qris',
            'qris_static_payload' => '0002010102115204541153033605802ID5910TEST STORE6007JAKARTA63048E18',
        ]);

        return [$bankBsi->id, $qris->id];
    }
}
