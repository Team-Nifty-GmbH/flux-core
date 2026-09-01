<?php

use FluxErp\Contracts\MailSyncDriver;
use FluxErp\Mail\ImapMailSyncDriver;
use FluxErp\Mail\MailDriverManager;

test('it returns the imap driver as default', function (): void {
    $manager = app(MailDriverManager::class);

    expect($manager->getDefaultDriver())->toBe('imap');
    expect($manager->driver())->toBeInstanceOf(ImapMailSyncDriver::class);
});

test('it returns the same driver instance for imap, pop3 and nntp', function (): void {
    $manager = app(MailDriverManager::class);

    expect($manager->driver('imap'))->toBeInstanceOf(ImapMailSyncDriver::class);
    expect($manager->driver('pop3'))->toBeInstanceOf(ImapMailSyncDriver::class);
    expect($manager->driver('nntp'))->toBeInstanceOf(ImapMailSyncDriver::class);
});

test('it exposes the built-in driver names', function (): void {
    $names = app(MailDriverManager::class)->driverNames();

    expect($names)->toContain('imap', 'pop3', 'nntp');
});

test('extended drivers are resolvable and reported in driverNames', function (): void {
    $manager = app(MailDriverManager::class);

    $custom = new class() implements MailSyncDriver
    {
        public function syncFolders(FluxErp\Models\MailAccount $account): array
        {
            return [];
        }

        public function syncMessages(FluxErp\Models\MailFolder $folder): void {}

        public function testConnection(FluxErp\Models\MailAccount $account): bool
        {
            return true;
        }
    };

    $manager->extend('mock-driver', fn () => $custom);

    expect($manager->driverNames())->toContain('mock-driver');
    expect($manager->driver('mock-driver'))->toBe($custom);
});
