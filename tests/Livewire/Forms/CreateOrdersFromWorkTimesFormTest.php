<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\CreateOrdersFromWorkTimesForm;
use Livewire\Livewire;
use Tests\TestCase;

class CreateOrdersFromWorkTimesFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CreateOrdersFromWorkTimesForm::class)
            ->assertStatus(200);
    }
}
