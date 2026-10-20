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

function makeFolderAt(string $path): Webklex\PHPIMAP\Folder
{
    $folder = Mockery::mock(Webklex\PHPIMAP\Folder::class);
    $folder->path = $path;

    return $folder;
}

test('looks up every folder from one folder listing per connection', function (): void {
    $mailAccount = MailAccount::factory()->create();
    $inbox = makeFolderAt('INBOX');
    $archive = makeFolderAt('INBOX.Archiv &AMQ-lter');

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('getFolders')
        ->once()
        ->with(false)
        ->andReturn(new Webklex\PHPIMAP\Support\FolderCollection([$inbox, $archive]));
    setImapClient($mailAccount, $client);

    expect($mailAccount->getImapFolder('INBOX'))->toBe($inbox)
        ->and($mailAccount->getImapFolder('INBOX.Archiv &AMQ-lter'))->toBe($archive)
        ->and($mailAccount->getImapFolder('Missing'))->toBeNull();
});

test('lists the folders again on a new connection', function (): void {
    $mailAccount = MailAccount::factory()->create();
    $stale = makeFolderAt('INBOX');
    $fresh = makeFolderAt('INBOX');

    $old = Mockery::mock(Client::class);
    $old->shouldReceive('getFolders')->once()->andReturn(new Webklex\PHPIMAP\Support\FolderCollection([$stale]));
    $old->shouldReceive('disconnect')->once();
    setImapClient($mailAccount, $old);
    $mailAccount->getImapFolder('INBOX');

    $mailAccount->disconnectImapClient();

    $new = Mockery::mock(Client::class);
    $new->shouldReceive('getFolders')->once()->andReturn(new Webklex\PHPIMAP\Support\FolderCollection([$fresh]));
    setImapClient($mailAccount, $new);

    expect($mailAccount->getImapFolder('INBOX'))->toBe($fresh);
});
