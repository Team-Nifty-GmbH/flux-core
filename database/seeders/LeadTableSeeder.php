<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Lead;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class LeadTableSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = Address::all(['id']);

        foreach (User::all(['id']) as $user) {
            Lead::factory()->create([
                'address_id' => $addresses->random()->getKey(),
                'user_id' => $user->getKey(),
            ]);
        }
    }
}
