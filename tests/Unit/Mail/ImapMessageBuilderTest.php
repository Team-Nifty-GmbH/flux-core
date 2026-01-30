<?php

use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Models\MailFolder;

it('can be instantiated from a mail folder', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder)->toBeInstanceOf(ImapMessageBuilder::class);
});

it('returns itself for chainable filter methods', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder->unseen())->toBe($builder)
        ->and($builder->seen())->toBe($builder)
        ->and($builder->newSince(100))->toBe($builder);
});

it('returns empty collection before fetch', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder->get())->toBeEmpty()
        ->and($builder->count())->toBe(0);
});
