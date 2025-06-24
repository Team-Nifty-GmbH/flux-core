<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyLeads;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class MyLeadsTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
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
            ->assertStatus(200)
            ->assertSee($lead->name);
    }
}
