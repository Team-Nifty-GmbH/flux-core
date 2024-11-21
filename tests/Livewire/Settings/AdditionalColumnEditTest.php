<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\AdditionalColumnEdit;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\TicketType;
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

    public function test_edit_additional_column()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => morph_alias(TicketType::class),
        ]);

        $component = Livewire::test(AdditionalColumnEdit::class)
            ->call('show', $additionalColumn->toArray())
            ->assertSet('isNew', false)
            ->assertSet('hideModel', false)
            ->assertSet('additionalColumn.name', $additionalColumn->name)
            ->assertSet('additionalColumn.field_type', 'text')
            ->assertSet('additionalColumn.label', null)
            ->assertSet('additionalColumn.model_type', $additionalColumn->model_type)
            ->set('additionalColumn.name', 'Test 2');

        $component->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertDispatched('closeModal', $component->get('additionalColumn')->toArray())
            ->assertWireuiNotification(icon: 'success');
    }
}
