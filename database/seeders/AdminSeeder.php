<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::where('role', 'owner')->exists()) {
            $owner = User::firstOrNew(['email' => 'admin@safebuy.com.bd']);
            $owner->forceFill([
                'name' => 'SafeBuy Owner',
                'phone' => '01700000000',
                'password' => 'SafeBuy@1234',
                'role' => 'owner',
                'is_blocked' => false,
            ])->save();
        }

        if (ShippingZone::count() === 0) {
            ShippingZone::create([
                'name' => 'Inside Dhaka',
                'districts' => ['Dhaka'],
                'charge' => (int) config('safebuy.shipping.inside_dhaka', 70),
                'estimated_days' => '1–2 days',
                'is_active' => true,
                'sort_order' => 1,
            ]);

            ShippingZone::create([
                'name' => 'Outside Dhaka (All Bangladesh)',
                'districts' => [],
                'charge' => (int) config('safebuy.shipping.outside_dhaka', 130),
                'estimated_days' => '2–4 days',
                'is_active' => true,
                'sort_order' => 2,
            ]);
        }
    }
}
