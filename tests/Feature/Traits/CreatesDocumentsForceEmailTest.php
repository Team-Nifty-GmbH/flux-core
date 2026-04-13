<?php

use FluxErp\Models\Contact;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\CreatesDocuments;
use Livewire\Component;

it('regenerates email attachment when force is active instead of using cached media', function (): void {
    $contact = Contact::factory()->create();

    // Attach an old "cached" balance-statement media to the contact
    $oldMedia = $contact
        ->addMediaFromString('old PDF content')
        ->usingFileName('old_balance_statement.pdf')
        ->toMediaCollection('balance-statement');

    // Create a test component using the CreatesDocuments trait
    $component = new class() extends Component
    {
        use CreatesDocuments;

        public function render()
        {
            return view('flux::livewire.support.dashboard');
        }

        public function getPrintLayouts(): array
        {
            return [
                'balance-statement' => FluxErp\View\Printing\Contact\BalanceStatement::class,
            ];
        }

        public function createDocuments(): void {}

        public function getTo($item, array $emailDocuments): array
        {
            return ['test@example.com'];
        }

        public function callCollectMailMessages($item): array
        {
            $mailMessages = [];
            $defaultTemplateIds = [];
            $this->collectMailMessages($item, $mailMessages, $defaultTemplateIds);

            return $mailMessages;
        }
    };

    // Set the layout as both "force" and "email"
    $component->selectedPrintLayouts = [
        'force' => ['balance-statement'],
        'email' => ['balance-statement'],
    ];
    $component->forcedPrintLayouts = [
        'force' => ['balance-statement'],
    ];

    $mailMessages = $component->callCollectMailMessages($contact);

    expect($mailMessages)->toHaveCount(1);

    $attachments = data_get($mailMessages, '0.attachments');
    $balanceAttachment = collect($attachments)->first(
        fn ($a) => is_array($a) && data_get($a, 'view') === 'balance-statement'
    );

    // When force is active, attachment should have view/model_type/model_id (fresh generation)
    // NOT just id/name (cached media reference)
    expect($balanceAttachment)->not->toBeNull()
        ->and($balanceAttachment)->toHaveKey('view')
        ->and($balanceAttachment)->toHaveKey('model_type')
        ->and($balanceAttachment)->toHaveKey('model_id')
        ->and($balanceAttachment)->not->toHaveKey('id');
});
