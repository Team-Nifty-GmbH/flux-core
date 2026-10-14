<?php

use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Livewire\Contact\Communication as ContactCommunication;
use FluxErp\Livewire\DataTables\CommunicationList;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Notifications\Communication\SendingFailedNotification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

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

test('a rejected mail marks its communication as failed', function (): void {
    $result = sendInvoiceMail();

    $communication = resolve_static(Communication::class, 'query')->sole();

    expect(data_get($result, 'success'))->toBeFalse()
        ->and($communication->send_error)->toContain('550 5.1.1 User unknown');
});

test('a delivered mail leaves its communication without send error', function (): void {
    $this->transport->reject = false;

    $result = sendInvoiceMail();

    expect(data_get($result, 'success'))->toBeTrue()
        ->and(resolve_static(Communication::class, 'query')->sole()->send_error)->toBeNull();
});

test('a queued mail that is rejected notifies the sender', function (): void {
    Notification::fake();
    Context::add('user', $this->user->getMorphClass() . ':' . $this->user->getKey());

    sendInvoiceMail(['queue' => true]);

    $communication = resolve_static(Communication::class, 'query')->sole();

    expect($communication->send_error)->toContain('550 5.1.1 User unknown');

    Notification::assertSentTo(
        $this->user,
        SendingFailedNotification::class,
        fn (SendingFailedNotification $notification): bool => str_contains(
            data_get($notification->toArray($this->user), 'description'),
            '550 5.1.1 User unknown'
        )
    );
});

test('a retried mail that succeeds clears the failure on the same communication', function (): void {
    $mail = GenericMail::make([
        'subject' => 'Invoice 4711',
        'html_body' => '<p>Your invoice</p>',
    ]);
    $retry = unserialize(serialize($mail));

    try {
        Mail::to('unknown@example.com')->send($mail);
    } catch (TransportException $e) {
        $mail->failed($e);
    }

    expect(resolve_static(Communication::class, 'query')->sole()->send_error)->not->toBeNull();

    $this->transport->reject = false;
    Mail::to('unknown@example.com')->send($retry);

    expect(resolve_static(Communication::class, 'query')->sole()->send_error)->toBeNull();
});

test('the communication list flags a failed mail', function (): void {
    sendInvoiceMail();

    Livewire::test(CommunicationList::class)
        ->call('loadData')
        ->assertSee(__('Sending failed'));
});

test('sending from a record tab reports the rejection instead of success', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(ContactCommunication::class, ['modelId' => $contact->getKey()])
        ->set('communication.communication_type_enum', 'mail')
        ->set('communication.to', ['unknown@example.com'])
        ->set('communication.subject', 'Invoice 4711')
        ->set('communication.html_body', '<p>Your invoice</p>')
        ->call('send')
        ->assertReturned(false)
        ->assertDispatched('ts-ui:toast', type: 'error');

    expect(resolve_static(Communication::class, 'query')->latest('id')->first()->send_error)
        ->toContain('550 5.1.1 User unknown');
});
