<?php

namespace Tests\Feature\Livewire\Features;

use FluxErp\Livewire\Features\SignaturePublicLink;
use Livewire\Livewire;
use Tests\TestCase;

class SignaturePublicLinkTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SignaturePublicLink::class)
            ->assertStatus(200);
    }
}
