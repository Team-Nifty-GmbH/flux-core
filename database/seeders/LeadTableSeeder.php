<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Models\RecordOrigin;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class LeadTableSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = Address::all(['id']);
        $leadStates = LeadState::all(['id']);
        $leadRecordOrigins = RecordOrigin::all()->where('model_type', morph_alias(Lead::class))->get(['id']);

        foreach (User::all(['id']) as $user) {
            Lead::factory()->create([
                'lead_state_id' => $leadStates->random()->getKey(),
                'address_id' => $addresses->random()->getKey(),
                'record_origin_id' => $leadRecordOrigins->random()->getKey(),
                'recommended_by_address_id' => $addresses->random()->getKey(),
                'user_id' => $user->getKey(),
            ]);
        }
    }
}
