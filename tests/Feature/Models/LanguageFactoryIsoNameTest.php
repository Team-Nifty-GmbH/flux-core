<?php

use FluxErp\Livewire\Settings\Languages;
use FluxErp\Models\Language;
use Livewire\Livewire;

test('the factory ties the iso name to the language code so it cannot collide', function (): void {
    $first = Language::factory()->create();
    $second = Language::factory()->create();

    expect($first->iso_name)->toEndWith($first->language_code)
        ->and($second->iso_name)->toEndWith($second->language_code)
        ->and($first->iso_name)->not->toBe($second->iso_name);
});

test('a language can be saved while another language exists', function (): void {
    $language = Language::factory()->create();
    Language::factory()->count(5)->create();

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
