<?php

namespace Database\Seeders;

use App\Models\MemberPrayer;
use App\Models\User;
use Illuminate\Database\Seeder;

class MemberPrayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = User::where('role', 'member')->get();

        MemberPrayer::factory(50)
            ->recycle($members)
            ->create();
    }
}
