<?php

use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Support\FolderCollection;

test('a folder flagged noselect is synced inactive while its children stay active', function (): void {
    $mailAccount = MailAccount::factory()->create();

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('getDefaultEvents')->andReturn([]);

    $publicFolders = new Folder($client, 'Public Folders', '/', ['\Noselect', '\HasChildren']);
    $publicFolders->setChildren(new FolderCollection([
        new Folder($client, 'Public Folders/Team', '/', ['\HasNoChildren']),
    ]));

    $client->shouldReceive('getFolders')->andReturn(new FolderCollection([
        new Folder($client, 'INBOX', '/', ['\HasNoChildren']),
        $publicFolders,
    ]));

    (new ReflectionProperty(MailAccount::class, 'imapClient'))->setValue($mailAccount, $client);

    $mailAccount->syncFolders();

    expect(
        MailFolder::query()
            ->where('mail_account_id', $mailAccount->getKey())
            ->pluck('is_active', 'slug')
            ->all()
    )->toEqual([
        'INBOX' => true,
        'Public Folders' => false,
        'Public Folders/Team' => true,
    ]);
});

test('an active folder that turns out to be noselect is deactivated on the next folder sync', function (): void {
    $mailAccount = MailAccount::factory()->create();
    $mailFolder = MailFolder::factory()->create([
        'mail_account_id' => $mailAccount->getKey(),
        'name' => 'Public Folders',
        'slug' => 'Public Folders',
        'is_active' => true,
    ]);

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('getDefaultEvents')->andReturn([]);
    $client->shouldReceive('getFolders')->andReturn(new FolderCollection([
        new Folder($client, 'Public Folders', '/', ['\Noselect', '\HasChildren']),
    ]));

    (new ReflectionProperty(MailAccount::class, 'imapClient'))->setValue($mailAccount, $client);

    $mailAccount->syncFolders();

    expect($mailFolder->refresh()->is_active)->toEqual(false);
});

function syncFoldersFromServer(MailAccount $mailAccount, array $paths): void
{
    $client = Mockery::mock(Client::class);
    $client->shouldReceive('getDefaultEvents')->andReturn([]);
    $client->shouldReceive('getFolders')->andReturn(new FolderCollection(
        array_map(fn (string $path) => new Folder($client, $path, '.', ['\HasNoChildren']), $paths)
    ));

    (new ReflectionProperty(MailAccount::class, 'imapClient'))->setValue($mailAccount, $client);

    $mailAccount->syncFolders();
}

test('the folder sync marks an english sent folder', function (): void {
    $mailAccount = MailAccount::factory()->create();

    syncFoldersFromServer($mailAccount, ['INBOX', 'INBOX.Sent Items']);

    expect(
        MailFolder::query()
            ->where('mail_account_id', $mailAccount->getKey())
            ->pluck('is_sent', 'slug')
            ->all()
    )->toEqual([
        'INBOX' => false,
        'INBOX.Sent Items' => true,
    ]);
});

test('the folder sync leaves a german sent folder to be marked by hand', function (): void {
    $mailAccount = MailAccount::factory()->create();

    syncFoldersFromServer($mailAccount, ['INBOX', 'Gesendete Objekte']);

    expect(MailFolder::query()->where('mail_account_id', $mailAccount->getKey())->where('is_sent', true)->exists())
        ->toBeFalse();
});

test('the folder sync keeps a sent folder marked by hand', function (): void {
    $mailAccount = MailAccount::factory()->create();
    $outbox = MailFolder::factory()->create([
        'mail_account_id' => $mailAccount->getKey(),
        'name' => 'Postausgang',
        'slug' => 'Postausgang',
        'is_sent' => true,
    ]);

    syncFoldersFromServer($mailAccount, ['Postausgang', 'Sent']);

    expect($outbox->refresh()->is_sent)->toBeTrue()
        ->and(MailFolder::query()->where('slug', 'Sent')->value('is_sent'))->toBeFalse();
});
