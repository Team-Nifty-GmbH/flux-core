<?php

use FluxErp\Livewire\Settings\Languages;
use FluxErp\Models\Language;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Languages::class)
        ->assertOk();
});

test('showEditModal with null resets form and opens modal', function (): void {
    Livewire::test(Languages::class)
        ->call('showEditModal')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('selectedLanguage.id', null)
        ->assertSet('selectedLanguage.name', null)
        ->assertSet('selectedLanguage.language_code', null)
        ->assertSet('selectedLanguage.iso_name', null)
        ->assertSet('editModal', true);
});

test('showEditModal with id fills form and opens modal', function (): void {
    $language = Language::factory()->create();

    Livewire::test(Languages::class)
        ->call('showEditModal', $language->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('selectedLanguage.id', $language->getKey())
        ->assertSet('selectedLanguage.name', $language->name)
        ->assertSet('selectedLanguage.language_code', $language->language_code)
        ->assertSet('selectedLanguage.iso_name', $language->iso_name)
        ->assertSet('editModal', true);
});

test('can create via save', function (): void {
    Livewire::test(Languages::class)
        ->call('showEditModal')
        ->set('selectedLanguage.name', 'Test Language')
        ->set('selectedLanguage.iso_name', 'Test ISO Name')
        ->set('selectedLanguage.language_code', 'zz')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('languages', [
        'name' => 'Test Language',
        'language_code' => 'zz',
    ]);
});

test('can update via save', function (): void {
    $language = Language::factory()->create();

    Livewire::test(Languages::class)
        ->call('showEditModal', $language->getKey())
        ->set('selectedLanguage.name', 'Updated Language')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('languages', [
        'id' => $language->getKey(),
        'name' => 'Updated Language',
    ]);
});

test('can delete', function (): void {
    $language = Language::factory()->create();

    Livewire::test(Languages::class)
        ->call('delete', $language->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('languages', ['id' => $language->getKey()]);
});

test('save validates required fields', function (): void {
    Livewire::test(Languages::class)
        ->call('showEditModal')
        ->set('selectedLanguage.name', null)
        ->call('save')
        ->assertReturned(false);
});
