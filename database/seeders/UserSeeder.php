<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): array
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@lazalazhar5.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $members = User::factory(10)->create([
            'role' => 'member',
        ]);

        return $members->pluck('id')->toArray();
    }
}
