<?php

use FluxErp\Livewire\Settings\Industries;
use FluxErp\Models\Industry;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Industries::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Industries::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('industryForm.id', null)
        ->assertSet('industryForm.name', null)
        ->assertOpensModal('edit-industry-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $industry = Industry::factory()->create();

    Livewire::test(Industries::class)
        ->call('edit', $industry->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('industryForm.id', $industry->getKey())
        ->assertSet('industryForm.name', $industry->name)
        ->assertOpensModal('edit-industry-modal');
});

test('can create via save', function (): void {
    Livewire::test(Industries::class)
        ->call('edit')
        ->set('industryForm.name', 'Test Industry')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('industries', ['name' => 'Test Industry']);
});

test('can update via save', function (): void {
    $industry = Industry::factory()->create();

    Livewire::test(Industries::class)
        ->call('edit', $industry->getKey())
        ->set('industryForm.name', 'Updated Industry')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('industries', [
        'id' => $industry->getKey(),
        'name' => 'Updated Industry',
    ]);
});

test('can delete', function (): void {
    $industry = Industry::factory()->create();

    Livewire::test(Industries::class)
        ->call('delete', $industry->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('industries', ['id' => $industry->getKey()]);
});

test('save validates required fields', function (): void {
    Livewire::test(Industries::class)
        ->call('edit')
        ->set('industryForm.name', null)
        ->call('save')
        ->assertReturned(false);
});
