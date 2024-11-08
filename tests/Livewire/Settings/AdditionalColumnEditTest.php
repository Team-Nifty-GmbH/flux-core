<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\AdditionalColumnEdit;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AdditionalColumnEditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(AdditionalColumnEdit::class)
            ->assertStatus(200);
    }

    public function test_create_additional_column()
    {
        Livewire::test(AdditionalColumnEdit::class)
            ->call('show')
            ->assertSet('isNew', true)
            ->assertSet('hideModel', false)
            ->set('additionalColumn.name', 'Test')
            ->set('additionalColumn.field_type', 'text')
            ->set('additionalColumn.label', 'Test label')
            ->set('additionalColumn.model_type', 'order')
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertDispatched('closeModal')
            ->assertWireuiNotification(icon: 'success');
    }
}
