<?php

use FluxErp\Support\Mentions\MentionPillRefresher;
use FluxErp\Tests\Fixtures\MentionableFixture;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    if (! Schema::hasTable('mentionable_fixtures')) {
        Schema::create('mentionable_fixtures', function ($table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('state_label')->nullable();
            $table->string('state_color')->nullable();
            $table->timestamps();
        });
    }

    $this->refresher = app(MentionPillRefresher::class);
    MentionableFixture::register('mentionable_fixture');
});

test('injects current state attributes into a stored record pill', function (): void {
    $record = MentionableFixture::create([
        'name' => 'RMA 1234',
        'state_label' => 'In Progress',
        'state_color' => 'violet',
    ]);
    $key = $record::mentionTypeKey();

    $html = '<p>Schau <a data-id="' . $key . ':' . $record->getKey()
        . '" data-mention-type="Fixture" href="/x">RMA 1234</a> an</p>';

    $out = $this->refresher->refresh($html);

    expect($out)->toContain('data-mention-state="In Progress"');
    expect($out)->toContain('title="In Progress"');
    expect($out)->toContain('style="--mention-state-color: var(--color-violet-500)"');
    expect($out)->toContain('href="/x"');
    expect($out)->toContain('RMA 1234');
    expect($out)->toContain('<p>Schau ');
});

test('reflects the records current state, not a stored one', function (): void {
    $record = MentionableFixture::create([
        'name' => 'RMA 1234',
        'state_label' => 'Done',
        'state_color' => 'green',
    ]);
    $key = $record::mentionTypeKey();

    $html = '<a data-id="' . $key . ':' . $record->getKey()
        . '" data-mention-state="In Progress" title="In Progress"'
        . ' style="--mention-state-color: var(--color-violet-500)" href="/x">RMA 1234</a>';

    $out = $this->refresher->refresh($html);

    expect($out)->toContain('data-mention-state="Done"');
    expect($out)->toContain('var(--color-green-500)');
    expect($out)->not->toContain('In Progress');
    expect(substr_count($out, 'data-mention-state='))->toBe(1);
});

test('drops state attributes for a deleted record', function (): void {
    $html = '<a data-id="mentionable_fixture:999999" data-mention-state="Old"'
        . ' title="Old" style="--mention-state-color: var(--color-red-500)" href="/x">Gone</a>';

    $out = $this->refresher->refresh($html);

    expect($out)->not->toContain('data-mention-state');
    expect($out)->not->toContain('--mention-state-color');
    expect($out)->toContain('href="/x"');
});

test('leaves non-mention html untouched', function (): void {
    $html = '<p>Email foo@bar.com and a <a href="/page">link</a></p>';

    expect($this->refresher->refresh($html))->toBe($html);
});

test('does not touch user pills', function (): void {
    $userKey = morph_alias(FluxErp\Models\User::class);
    $html = '<a data-id="' . $userKey . ':1" href="/users/1">@Bob</a>';

    expect($this->refresher->refresh($html))->toBe($html);
});
