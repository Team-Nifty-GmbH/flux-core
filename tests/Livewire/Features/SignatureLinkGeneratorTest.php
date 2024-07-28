<?php

namespace Tests\Feature\Livewire\Features;

use FluxErp\Livewire\Features\SignatureLinkGenerator;
use Livewire\Livewire;
use Tests\TestCase;

class SignatureLinkGeneratorTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SignatureLinkGenerator::class)
            ->assertStatus(200);
    }
}
