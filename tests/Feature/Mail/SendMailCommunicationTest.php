<?php

use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Livewire\Contact\Communication as ContactCommunication;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Notifications\Communication\SendingFailedNotification;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use function Livewire\invade;

beforeEach(function (): void {
    $this->transport = new class() extends AbstractTransport
    {
        public bool $reject = true;

        public function __toString(): string
        {
            return 'rejecting://';
        }

        protected function doSend(SentMessage $message): void
        {
            if ($this->reject) {
                throw new TransportException(
                    'Expected response code "250/251/252" but got code "550", with message "550 5.1.1 User unknown".'
                );
            }
        }
    };

    Mail::extend('rejecting', fn () => $this->transport);
    config([
        'mail.mailers.rejecting' => ['transport' => 'rejecting'],
        'mail.default' => 'rejecting',
    ]);
    Mail::purge('rejecting');
});

function sendInvoiceMail(array $data = []): array
{
    return SendMail::make(array_merge([
        'to' => ['unknown@example.com'],
        'subject' => 'Invoice 4711',
        'html_body' => '<p>Your invoice</p>',
    ], $data))
        ->validate()
        ->execute();
}

test('a rejected mail leaves exactly one communication carrying the error', function (): void {
    $result = sendInvoiceMail();

    expect(data_get($result, 'success'))->toBeFalse()
        ->and(resolve_static(Communication::class, 'query')->sole()->send_error)
        ->toContain('550 5.1.1 User unknown');
});

test('a delivered mail leaves exactly one communication without error', function (): void {
    $this->transport->reject = false;

    $result = sendInvoiceMail();

    $communication = resolve_static(Communication::class, 'query')->sole();

    expect(data_get($result, 'success'))->toBeTrue()
        ->and($communication->send_error)->toBeNull()
        ->and($communication->message_id)->not->toBeNull();
});

test('a rejected queued mail leaves exactly one communication carrying the error', function (): void {
    config(['queue.default' => 'sync']);

    sendInvoiceMail(['queue' => true]);

    expect(resolve_static(Communication::class, 'query')->sole()->send_error)
        ->toContain('550 5.1.1 User unknown');
});

test('a retried mail reuses its communication and clears the error', function (): void {
    Queue::fake();

    sendInvoiceMail(['queue' => true]);

    $payload = serialize(Queue::pushed(SendQueuedMailable::class)->sole());

    $attempt = unserialize($payload);
    try {
        $attempt->handle(app(MailFactory::class));
    } catch (TransportException $e) {
        $attempt->failed($e);
    }

    expect(resolve_static(Communication::class, 'query')->sole()->send_error)
        ->toContain('550 5.1.1 User unknown');

    $this->transport->reject = false;
    unserialize($payload)->handle(app(MailFactory::class));

    expect(resolve_static(Communication::class, 'query')->sole()->send_error)->toBeNull();
});

test('a send mail call cannot target an existing communication', function (): void {
    $this->transport->reject = false;

    $imported = Communication::factory()->create([
        'communication_type_enum' => CommunicationTypeEnum::Mail,
        'message_id' => 'imported@example.com',
        'subject' => 'Imported',
    ]);

    sendInvoiceMail([
        'id' => $imported->getKey(),
        'communication_id' => $imported->getKey(),
        'communicationId' => $imported->getKey(),
    ]);

    $imported->refresh();

    expect(resolve_static(Communication::class, 'query')->count())->toBe(2)
        ->and($imported->message_id)->toBe('imported@example.com')
        ->and($imported->subject)->toBe('Imported');
});

test('a bulk failure notifies the sender once', function (): void {
    config(['queue.default' => 'sync']);
    Notification::fake();

    foreach (range(1, 3) as $i) {
        sendInvoiceMail(['queue' => true, 'subject' => 'Invoice ' . $i]);
    }

    expect(resolve_static(Communication::class, 'query')->whereNotNull('send_error')->count())->toBe(3);

    Notification::assertSentToTimes($this->user, SendingFailedNotification::class, 1);
});

test('sending from a record tab keeps a single communication', function (): void {
    $this->transport->reject = false;
    $contact = Contact::factory()->create();

    Livewire::test(ContactCommunication::class, ['modelId' => $contact->getKey()])
        ->set('communication.communication_type_enum', 'mail')
        ->set('communication.to', ['customer@example.com'])
        ->set('communication.subject', 'Invoice 4711')
        ->set('communication.html_body', '<p>Your invoice</p>')
        ->call('send')
        ->assertReturned(true);

    $communication = resolve_static(Communication::class, 'query')->sole();

    expect($communication->subject)->toBe('Invoice 4711')
        ->and($communication->message_id)->not->toBeNull()
        ->and($communication->send_error)->toBeNull();
});

test('sending from a record tab reports a rejection', function (): void {
    $contact = Contact::factory()->create();

    $component = Livewire::test(ContactCommunication::class, ['modelId' => $contact->getKey()])
        ->set('communication.communication_type_enum', 'mail')
        ->set('communication.to', ['unknown@example.com'])
        ->set('communication.subject', 'Invoice 4711')
        ->set('communication.html_body', '<p>Your invoice</p>')
        ->call('send')
        ->assertReturned(false)
        ->assertDispatched('ts-ui:toast', type: 'error');

    $communication = resolve_static(Communication::class, 'query')->sole();
    $list = invade($component->instance());

    expect($communication->send_error)->toContain('550 5.1.1 User unknown')
        ->and(data_get($list->getResultFromQuery($list->buildSearch())->items(), '0.send_status.raw'))
        ->toBe(__('Sending failed'));

    $component->call('edit', $communication->getKey())
        ->assertSet('communication.send_error', $communication->send_error);
});
