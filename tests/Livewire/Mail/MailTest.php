<?php

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Livewire\Mail\Mail;
use FluxErp\Models\Communication;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(Mail::class)
        ->assertOk();
});

test('compose mail dispatches create event to edit mail', function (): void {
    Livewire::actingAs($this->user)
        ->test(Mail::class)
        ->call('composeMail')
        ->assertDispatchedTo('edit-mail', 'create', values: []);
});

test('lists mails sent without a mail account', function (): void {
    $own = Communication::factory()->create([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
        'mail_account_id' => null,
    ]);

    Communication::query()
        ->whereKey($own->getKey())
        ->update(['created_by' => $this->user->getMorphClass() . ':' . $this->user->getKey()]);

    $unowned = Communication::factory()->create([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
        'mail_account_id' => null,
    ]);

    Communication::query()->whereKey($unowned->getKey())->update(['created_by' => null]);

    $foreign = Communication::factory()->create([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
        'mail_account_id' => null,
    ]);

    Communication::query()
        ->whereKey($foreign->getKey())
        ->update(['created_by' => $this->user->getMorphClass() . ':' . ($this->user->getKey() + 1000)]);

    $component = Livewire::actingAs($this->user)
        ->test(Mail::class)
        ->instance();

    $getBuilder = new ReflectionMethod($component, 'getBuilder');
    $ids = $getBuilder->invoke($component, Communication::query())->pluck('id');

    expect($ids)->toContain($own->getKey())
        ->and($ids)->toContain($unowned->getKey())
        ->and($ids)->not->toContain($foreign->getKey());
});
