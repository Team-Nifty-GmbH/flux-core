<?php

use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Listeners\MailMessage\CreateMailExecutedSubscriber;
use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\Tenant;
use FluxErp\Models\Ticket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    $this->address = Address::factory()->create([
        'contact_id' => $contact->id,
    ]);

    $this->mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->state(['can_create_ticket' => true]))
        ->create();
});

test('add comment to ticket from mail message', function (): void {
    Event::fake('action.executed: ' . CreateMailMessage::class);
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => $this->address->getMorphClass(),
        'authenticatable_id' => $this->address->getKey(),
    ]);

    $action = CreateMailMessage::make([
        'mail_account_id' => $this->mailAccount->id,
        'mail_folder_id' => $this->mailAccount->mailFolders->first()->id,
        'from' => 'Tester McTestFace <' . $this->address->email_primary . '>',
        'to' => [$this->mailAccount->email],
        'subject' => Str::uuid()->toString(),
        'text_body' => $textBody = faker()->text(),
        'html_body' => '<p>'
            . faker()->text()
            . '[flux:comment:' . $ticket->getMorphClass() . ':' . $ticket->getKey() . ']</p>',
        'communication_type_enum' => 'mail',
        'date' => now()->format('Y-m-d H:i:s'),
        'tags' => [],
    ]);
    $result = $action->validate()->execute();

    expect($result)->not->toBeNull();
    expect($result->id)->not->toBeNull();
    Event::assertDispatched('action.executed: ' . CreateMailMessage::class);

    $listener = new CreateMailExecutedSubscriber();
    $listener->handle($action);

    $this->assertDatabaseHas('comments', [
        'comment' => $textBody,
        'is_internal' => false,
        'created_by' => $this->address->getMorphClass() . ':' . $this->address->getKey(),
    ]);

    expect($ticket->communications()->where('communications.id', $result->id)->exists())->toBeTrue();
});

test('strips quoted conversation from comment created from mail reply', function (): void {
    Event::fake('action.executed: ' . CreateMailMessage::class);
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => $this->address->getMorphClass(),
        'authenticatable_id' => $this->address->getKey(),
    ]);

    $reply = 'habs freigegeben.' . PHP_EOL . 'Nächstes mal schreib ich den Mandanten dazu.';
    $token = '[flux:comment:' . $ticket->getMorphClass() . ':' . $ticket->getKey() . ']';
    $quotedHistory = 'Mit freundlichen Grüßen' . PHP_EOL . 'Alexander' . PHP_EOL
        . '[flux:quote]' . PHP_EOL
        . 'Hallo, es gibt eine neue Antwort auf Ihr Ticket.' . PHP_EOL
        . 'Vorherige Kommentare: Hier der Videolink' . PHP_EOL
        . 'Ursprüngliches Ticket: ...' . PHP_EOL
        . $token;

    $action = CreateMailMessage::make([
        'mail_account_id' => $this->mailAccount->id,
        'mail_folder_id' => $this->mailAccount->mailFolders->first()->id,
        'from' => 'Tester McTestFace <' . $this->address->email_primary . '>',
        'to' => [$this->mailAccount->email],
        'subject' => Str::uuid()->toString(),
        'text_body' => $reply . PHP_EOL . $quotedHistory,
        'html_body' => '<p>' . $reply . '</p>'
            . '<span style="display: none">[flux:quote]</span>'
            . '<p>Vorherige Kommentare: Hier der Videolink' . $token . '</p>',
        'communication_type_enum' => 'mail',
        'date' => now()->format('Y-m-d H:i:s'),
        'tags' => [],
    ]);
    $action->validate()->execute();

    $listener = new CreateMailExecutedSubscriber();
    $listener->handle($action);

    $comment = Comment::query()
        ->where('model_type', $ticket->getMorphClass())
        ->where('model_id', $ticket->getKey())
        ->latest('id')
        ->first();

    expect($comment)->not->toBeNull()
        ->and($comment->comment)->toContain('habs freigegeben')
        ->and($comment->comment)->not->toContain('Vorherige Kommentare')
        ->and($comment->comment)->not->toContain('Ursprüngliches Ticket')
        ->and($comment->comment)->not->toContain('flux:comment')
        ->and($comment->comment)->not->toContain('[flux:quote]');
});

test('purchase invoice from mail message skips supplier on tenant domain', function (
    array $tenantAttributes,
    string $senderDomain,
    bool $expectsContact
): void {
    Event::fake('action.executed: ' . CreateMailMessage::class);

    Tenant::factory()->create($tenantAttributes);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'rechnung@' . $senderDomain,
    ]);

    $mailFolder = MailFolder::factory()->create([
        'mail_account_id' => $this->mailAccount->getKey(),
        'can_create_purchase_invoice' => true,
    ]);

    $action = CreateMailMessage::make([
        'mail_account_id' => $this->mailAccount->getKey(),
        'mail_folder_id' => $mailFolder->getKey(),
        'from' => 'Tester McTestFace <' . $address->email_primary . '>',
        'to' => [$this->mailAccount->email],
        'subject' => Str::uuid()->toString(),
        'text_body' => faker()->text(),
        'html_body' => '<p>' . faker()->text() . '</p>',
        'communication_type_enum' => 'mail',
        'date' => now()->format('Y-m-d H:i:s'),
        'tags' => [],
    ]);
    $message = $action->validate()->execute();

    $message->addMedia(UploadedFile::fake()->create('invoice.pdf', 10, 'application/pdf'))
        ->toMediaCollection('attachments');
    $message->refresh();

    (new CreateMailExecutedSubscriber())->handle($action);

    $purchaseInvoice = PurchaseInvoice::query()->latest('id')->first();

    expect($purchaseInvoice)->not->toBeNull()
        ->and($purchaseInvoice->contact_id)->toBe($expectsContact ? $contact->getKey() : null);
})->with([
    'tenant email domain' => [['email' => 'buchhaltung@team-nifty.test'], 'team-nifty.test', false],
    'tenant website domain' => [['website' => 'https://www.team-nifty.test/impressum'], 'team-nifty.test', false],
    'tenant email subdomain' => [['email' => 'buchhaltung@team-nifty.test'], 'mail.team-nifty.test', false],
    'foreign domain' => [['email' => 'buchhaltung@team-nifty.test'], 'supplier.test', true],
    'domain suffix lookalike' => [['email' => 'buchhaltung@team-nifty.test'], 'notteam-nifty.test', true],
    'tenant website without a dot' => [['website' => 'test'], 'supplier.test', true],
]);

test('create ticket from mail message', function (): void {
    Event::fake('action.executed: ' . CreateMailMessage::class);

    $action = CreateMailMessage::make([
        'mail_account_id' => $this->mailAccount->id,
        'mail_folder_id' => $this->mailAccount->mailFolders->first()->id,
        'from' => 'Tester McTestFace <' . $this->address->email_primary . '>',
        'to' => [$this->mailAccount->email],
        'subject' => $subject = Str::uuid()->toString(),
        'text_body' => faker()->text(),
        'html_body' => '<p>' . faker()->text() . '</p>',
        'communication_type_enum' => 'mail',
        'date' => now()->format('Y-m-d H:i:s'),
        'tags' => [],
    ]);
    $result = $action->validate()->execute();

    expect($result)->not->toBeNull();
    expect($result->id)->not->toBeNull();
    Event::assertDispatched('action.executed: ' . CreateMailMessage::class);

    $listener = new CreateMailExecutedSubscriber();
    $listener->handle($action);

    $this->assertDatabaseHas('tickets', [
        'title' => $subject,
        'description' => $action->getData('text_body'),
        'created_by' => $this->address->getMorphClass() . ':' . $this->address->getKey(),
    ]);
});
