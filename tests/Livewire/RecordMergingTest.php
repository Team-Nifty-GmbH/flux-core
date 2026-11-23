<?php

use FluxErp\Livewire\RecordMerging;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\NotificationSetting;
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

test('shows the user why a merge failed instead of reporting success', function (): void {
    $contact = Contact::factory()->create();
    [$main, $merge] = Address::factory()
        ->count(2)
        ->create([
            'contact_id' => $contact->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'is_main_address' => false,
        ])
        ->all();

    foreach ([$main, $merge] as $address) {
        NotificationSetting::query()->create([
            'notifiable_type' => $address->getMorphClass(),
            'notifiable_id' => $address->getKey(),
            'notification_type' => 'conflict',
            'channel' => 'mail',
        ]);
    }

    Livewire::test(RecordMerging::class)
        ->dispatch('show-record-merging', recordIds: [$main->getKey(), $merge->getKey()], modelClass: Address::class)
        ->call('toggleRecord', $main->getKey())
        ->call('merge')
        ->assertReturned(false)
        ->assertHasErrors('notificationSettings');

    expect(Address::query()->whereKey($merge->getKey())->exists())->toBeTrue();
});
