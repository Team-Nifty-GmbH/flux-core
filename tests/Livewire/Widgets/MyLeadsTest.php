<?php

use FluxErp\Livewire\Widgets\MyLeads;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $lead = Lead::factory()
        ->for(
            LeadState::factory()
                ->state([
                    'is_won' => false,
                    'is_lost' => false,
                ])
        )
        ->create([
            'user_id' => $this->user->getKey(),
        ]);

    Livewire::test(MyLeads::class)
        ->assertOk()
        ->assertSee($lead->name);
});
