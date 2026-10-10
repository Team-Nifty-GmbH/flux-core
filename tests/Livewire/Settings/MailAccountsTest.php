<?php

use FluxErp\Livewire\Settings\MailAccounts;
use FluxErp\Support\Mail\MailServerDiscovery;
use Livewire\Livewire;

function fakeMailServerDiscovery(?array $result): void
{
    app()->bind(
        MailServerDiscovery::class,
        fn () => new class($result) extends MailServerDiscovery
        {
            public function __construct(private readonly ?array $result) {}

            public function discover(string $email): ?array
            {
                return $this->result;
            }
        }
    );
}

function discoveredMailSettings(): array
{
    return [
        'host' => 'imap.example.com',
        'port' => 143,
        'encryption' => 'tls',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 465,
        'smtp_encryption' => 'ssl',
        'smtp_user' => 'john@example.com',
        'source' => 'autoconfig.example.com',
    ];
}

test('renders successfully', function (): void {
    Livewire::test(MailAccounts::class)
        ->assertOk();
});

test('discover settings overwrites both blocks', function (): void {
    fakeMailServerDiscovery(discoveredMailSettings());

    Livewire::test(MailAccounts::class)
        ->set('mailAccount.email', 'john@example.com')
        ->set('mailAccount.host', 'old.example.com')
        ->set('mailAccount.smtp_host', 'old-smtp.example.com')
        ->call('discoverSettings')
        ->assertOk()
        ->assertSet('mailAccount.host', 'imap.example.com')
        ->assertSet('mailAccount.port', 143)
        ->assertSet('mailAccount.encryption', 'tls')
        ->assertSet('mailAccount.smtp_host', 'smtp.example.com')
        ->assertSet('mailAccount.smtp_port', 465)
        ->assertSet('mailAccount.smtp_encryption', 'ssl')
        ->assertSet('mailAccount.smtp_user', 'john@example.com')
        ->assertDispatched('ts-ui:toast', fn (string $name, array $params) => $params['type'] === 'success');
});

test('discover settings only empty keeps a filled block', function (): void {
    fakeMailServerDiscovery(discoveredMailSettings());

    Livewire::test(MailAccounts::class)
        ->set('mailAccount.email', 'john@example.com')
        ->set('mailAccount.host', 'own.example.com')
        ->call('discoverSettings', true)
        ->assertOk()
        ->assertSet('mailAccount.host', 'own.example.com')
        ->assertSet('mailAccount.port', 993)
        ->assertSet('mailAccount.encryption', 'ssl')
        ->assertSet('mailAccount.smtp_host', 'smtp.example.com')
        ->assertSet('mailAccount.smtp_port', 465)
        ->assertSet('mailAccount.smtp_encryption', 'ssl')
        ->assertSet('mailAccount.smtp_user', 'john@example.com');
});

test('discover settings only empty fills the imap block when smtp is set', function (): void {
    fakeMailServerDiscovery(discoveredMailSettings());

    Livewire::test(MailAccounts::class)
        ->set('mailAccount.email', 'john@example.com')
        ->set('mailAccount.smtp_host', 'own-smtp.example.com')
        ->call('discoverSettings', true)
        ->assertSet('mailAccount.host', 'imap.example.com')
        ->assertSet('mailAccount.port', 143)
        ->assertSet('mailAccount.smtp_host', 'own-smtp.example.com')
        ->assertSet('mailAccount.smtp_port', 587)
        ->assertSet('mailAccount.smtp_user', null);
});

test('discover settings warns when nothing is found', function (): void {
    fakeMailServerDiscovery(null);

    Livewire::test(MailAccounts::class)
        ->set('mailAccount.email', 'john@example.com')
        ->call('discoverSettings')
        ->assertOk()
        ->assertSet('mailAccount.host', null)
        ->assertDispatched('ts-ui:toast', fn (string $name, array $params) => $params['type'] === 'warning');
});

test('discover settings does nothing for an invalid email', function (): void {
    fakeMailServerDiscovery(discoveredMailSettings());

    Livewire::test(MailAccounts::class)
        ->set('mailAccount.email', 'not-an-email')
        ->call('discoverSettings', true)
        ->assertOk()
        ->assertSet('mailAccount.host', null)
        ->assertSet('mailAccount.smtp_host', null);
});
