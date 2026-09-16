<?php

use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Models\MailAccount;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

function sendThroughMailAccount(array $data = []): ?string
{
    Event::fake([MessageSent::class]);

    $mailAccount = MailAccount::factory()->create([
        'smtp_mailer' => 'array',
        'smtp_email' => 'noreply@example.com',
        'smtp_from_name' => null,
    ]);

    SendMail::make(
        array_merge(
            [
                'mail_account_id' => $mailAccount->getKey(),
                'to' => ['guest@example.com'],
                'subject' => 'Reservation',
                'html_body' => '<p>Table booked</p>',
            ],
            $data
        )
    )
        ->validate()
        ->execute();

    $name = null;
    Event::assertDispatched(MessageSent::class, function (MessageSent $event) use (&$name): bool {
        $name = $event->message->getFrom()[0]->getName();

        return true;
    });

    return $name;
}

test('a given from name becomes the sender name', function (): void {
    expect(sendThroughMailAccount(['from_name' => 'Manne Pahl']))->toBe('Manne Pahl');
});

test('without a from name the logged in user still wins', function (): void {
    expect(sendThroughMailAccount())->toBe($this->user->name);
});

test('without a from name and without a user it falls back to the account address', function (): void {
    Auth::guard('web')->logout();

    expect(sendThroughMailAccount())->toBe('noreply@example.com');
});
