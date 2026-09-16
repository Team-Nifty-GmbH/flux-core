<?php

use FluxErp\Models\MailAccount;
use Webklex\PHPIMAP\Client;

function setImapClient(MailAccount $mailAccount, ?Client $client): void
{
    $property = new ReflectionProperty(MailAccount::class, 'imapClient');
    $property->setValue($mailAccount, $client);
}

function getImapClientProperty(MailAccount $mailAccount): ?Client
{
    return (new ReflectionProperty(MailAccount::class, 'imapClient'))->getValue($mailAccount);
}

test('disconnecting logs the client out and drops it', function (): void {
    $mailAccount = MailAccount::factory()->create();

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('disconnect')->once();

    setImapClient($mailAccount, $client);

    $mailAccount->disconnectImapClient();

    expect(getImapClientProperty($mailAccount))->toBeNull();
});

test('a logout on a dead socket does not escape the disconnect', function (): void {
    $mailAccount = MailAccount::factory()->create();

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('disconnect')
        ->once()
        ->andThrow(new ErrorException('fwrite(): SSL: Broken pipe'));

    setImapClient($mailAccount, $client);

    $mailAccount->disconnectImapClient();

    expect(getImapClientProperty($mailAccount))->toBeNull();
});

test('disconnecting without a client does nothing', function (): void {
    $mailAccount = MailAccount::factory()->create();

    $mailAccount->disconnectImapClient();

    expect(getImapClientProperty($mailAccount))->toBeNull();
});

test('reconnecting closes the old client instead of leaving it to the collector', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'host' => '127.0.0.1',
        'port' => 1,
    ]);

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('disconnect')->once();

    setImapClient($mailAccount, $client);

    try {
        $mailAccount->reconnectImapClient();
    } catch (Throwable) {
    }

    expect(getImapClientProperty($mailAccount))->not->toBe($client);
});
