<?php

use FluxErp\Livewire\RecordMerging;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecordMerging::class)
        ->assertOk();
});

test('dispatches record merging from trait without backslash issues', function (): void {
    $contacts = Contact::factory()->count(2)->create();

    Livewire::test(RecordMerging::class)
        ->dispatch('show-record-merging', recordIds: $contacts->pluck('id')->toArray(), modelClass: Contact::class)
        ->assertOk()
        ->assertSet('mergeRecords.model_type', morph_alias(Contact::class));
});

test('handles malformed model class gracefully', function (): void {
    Livewire::test(RecordMerging::class)
        ->dispatch('show-record-merging', recordIds: [1, 2], modelClass: 'FluxErpModelsContact')
        ->assertOk();
});
