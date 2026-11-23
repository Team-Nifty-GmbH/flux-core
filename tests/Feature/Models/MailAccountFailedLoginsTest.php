<?php

use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Livewire\Settings\MailAccounts;
use FluxErp\Models\MailAccount;
use Livewire\Livewire;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;

function rejectingImapClient(): Client
{
    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')
        ->andThrow(new ImapServerErrorException('NO [AUTHENTICATIONFAILED] Invalid credentials (Failure)'));

    return $client;
}

function acceptingImapClient(): Client
{
    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->andReturnSelf();
    $client->shouldReceive('disconnect');

    return $client;
}

function failLogin(MailAccount $mailAccount): void
{
    try {
        $mailAccount->connect();
    } catch (ImapServerErrorException) {
    }
}

test('the server is not asked again once the login failed too often in a row', function (): void {
    $mailAccount = MailAccount::factory()->create(['host' => 'imap.example.com']);

    ImapClient::shouldReceive('make')
        ->times(MailAccount::MAX_FAILED_LOGINS)
        ->andReturn(rejectingImapClient());

    foreach (range(1, MailAccount::MAX_FAILED_LOGINS) as $attempt) {
        expect(fn () => $mailAccount->connect())->toThrow(ImapServerErrorException::class);
    }

    expect($mailAccount->connect())->toBeNull()
        ->and($mailAccount->refresh()->failed_logins)->toBe(MailAccount::MAX_FAILED_LOGINS);
});

test('a successful login clears the failed logins', function (): void {
    $mailAccount = MailAccount::factory()->create(['host' => 'imap.example.com']);

    ImapClient::shouldReceive('make')->andReturn(rejectingImapClient(), rejectingImapClient(), acceptingImapClient());

    failLogin($mailAccount);
    failLogin($mailAccount);

    expect($mailAccount->connect())->not->toBeNull()
        ->and($mailAccount->refresh()->failed_logins)->toBe(0);
});

test('an unreachable server does not count as a failed login', function (): void {
    $mailAccount = MailAccount::factory()->create(['host' => 'imap.example.com']);

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->andThrow(new ConnectionFailedException('connection setup failed'));
    ImapClient::shouldReceive('make')->andReturn($client);

    expect(fn () => $mailAccount->connect())->toThrow(ConnectionFailedException::class)
        ->and($mailAccount->refresh()->failed_logins)->toBe(0);
});

test('a temporarily unavailable server does not count as a failed login', function (): void {
    $mailAccount = MailAccount::factory()->create(['host' => 'imap.example.com']);

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')
        ->andThrow(new ImapServerErrorException('NO [UNAVAILABLE] Temporary authentication failure.'));
    ImapClient::shouldReceive('make')->andReturn($client);

    expect(fn () => $mailAccount->connect())->toThrow(ImapServerErrorException::class)
        ->and($mailAccount->refresh()->failed_logins)->toBe(0);
});

test('saving the account resumes a paused sync', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'host' => 'imap.example.com',
        'failed_logins' => MailAccount::MAX_FAILED_LOGINS,
    ]);

    UpdateMailAccount::make([
        'id' => $mailAccount->getKey(),
        'password' => 'new-secret',
    ])
        ->validate()
        ->execute();

    ImapClient::shouldReceive('make')->once()->andReturn(acceptingImapClient());

    expect($mailAccount->refresh()->connect())->not->toBeNull();
});

test('an update without connection changes keeps the failed logins', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'host' => 'imap.example.com',
        'failed_logins' => MailAccount::MAX_FAILED_LOGINS,
    ]);

    UpdateMailAccount::make([
        'id' => $mailAccount->getKey(),
        'has_auto_assign' => ! $mailAccount->has_auto_assign,
    ])
        ->validate()
        ->execute();

    expect($mailAccount->refresh()->failed_logins)->toBe(MailAccount::MAX_FAILED_LOGINS);
});

test('an update with only the id keeps the failed logins', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'host' => 'imap.example.com',
        'failed_logins' => MailAccount::MAX_FAILED_LOGINS,
    ]);

    UpdateMailAccount::make(['id' => $mailAccount->getKey()])
        ->validate()
        ->execute();

    expect($mailAccount->refresh()->failed_logins)->toBe(MailAccount::MAX_FAILED_LOGINS);
});

test('changing the server settings resets the failed logins', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'host' => 'imap.example.com',
        'failed_logins' => MailAccount::MAX_FAILED_LOGINS,
    ]);

    $mailAccount->update(['host' => 'imap.example.org']);

    expect($mailAccount->refresh()->failed_logins)->toBe(0);
});

test('the settings form knows that the sync is paused', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'failed_logins' => MailAccount::MAX_FAILED_LOGINS,
    ]);

    Livewire::test(MailAccounts::class)
        ->call('edit', $mailAccount->getKey())
        ->assertSet('isSyncPaused', true)
        ->assertSee(__('The login failed several times in a row, so the sync is paused. Check the credentials and save the account to resume it.'));
});
