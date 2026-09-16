<?php

use FluxErp\Models\MailAccount;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

function smtpStreamOptions(MailAccount $mailAccount): array
{
    $transport = $mailAccount->mailer()->getSymfonyTransport();

    expect($transport)->toBeInstanceOf(EsmtpTransport::class);

    return $transport->getStream()->getStreamOptions();
}

test('smtp certificate validation stays on by default', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'smtp_mailer' => 'smtp',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_email' => 'sender@example.com',
    ]);

    expect($mailAccount->fresh()->smtp_has_valid_certificate)->toBeTrue()
        ->and(smtpStreamOptions($mailAccount))->toBe([]);
});

test('smtp certificate validation can be turned off per mail account', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'smtp_mailer' => 'smtp',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_email' => 'sender@example.com',
        'smtp_has_valid_certificate' => false,
    ]);

    expect(smtpStreamOptions($mailAccount))->toBe([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ]);
});

test('an unknown smtp certificate setting keeps validation on', function (): void {
    $mailAccount = app(MailAccount::class)->fill([
        'smtp_mailer' => 'smtp',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_email' => 'sender@example.com',
    ]);

    expect(smtpStreamOptions($mailAccount))->toBe([]);
});

test('turning off smtp certificate validation does not touch imap certificate validation', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'has_valid_certificate' => true,
        'smtp_has_valid_certificate' => false,
    ]);

    expect($mailAccount->fresh()->has_valid_certificate)->toBeTruthy();
});
