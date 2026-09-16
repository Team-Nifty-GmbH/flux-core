<?php

use FluxErp\Support\Mentions\MentionRenderer;
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

    $this->renderer = app(MentionRenderer::class);
});

test('replaces #key:id tokens with anchor pills', function (): void {
    MentionableFixture::register('mentionable_fixture');
    $record = MentionableFixture::create(['name' => 'RMA 1234']);
    $key = $record::mentionTypeKey();

    $html = $this->renderer->tokensToHtml("Schau #{$key}:{$record->getKey()} an");

    expect($html)->toContain('<a');
    expect($html)->toContain("data-mention=\"{$key}:{$record->getKey()}\"");
    expect($html)->toContain('class="mention');
    expect($html)->toContain('RMA 1234');
});

test('includes the type label on the record pill', function (): void {
    MentionableFixture::register('mentionable_fixture');
    $record = MentionableFixture::create(['name' => 'RMA 1234']);
    $key = $record::mentionTypeKey();

    $html = $this->renderer->tokensToHtml("#{$key}:{$record->getKey()}");

    expect($html)->toContain('data-mention-type="' . $record::mentionTypeLabel() . '"');
    expect($html)->toContain('RMA 1234');
});

test('omits the type label on user pills', function (): void {
    $user = FluxErp\Models\User::factory()->create();

    $html = $this->renderer->tokensToHtml("@user:{$user->getKey()}");

    expect($html)->not->toContain('data-mention-type');
});

test('replaces @user:id tokens with non-linking user pills', function (): void {
    $user = FluxErp\Models\User::factory()->create();

    $html = $this->renderer->tokensToHtml("Ping @user:{$user->getKey()} bitte");

    expect($html)->toContain('<span class="mention mention--user');
    expect($html)->toContain("data-user-id=\"{$user->getKey()}\"");
    expect($html)->not->toContain('<a');
});

test('falls back to plain text for deleted targets', function (): void {
    MentionableFixture::register('mentionable_fixture');

    $html = $this->renderer->tokensToHtml('#mentionable_fixture:999999');

    expect($html)->toContain(__('@deleted entry'));
    expect($html)->not->toContain('<a');
});

test('leaves unknown type keys untouched', function (): void {
    $html = $this->renderer->tokensToHtml('#unknowntype:1');

    expect($html)->toBe('#unknowntype:1');
});

test('escapes label HTML', function (): void {
    MentionableFixture::register('mentionable_fixture');
    $record = MentionableFixture::create(['name' => '<script>alert(1)</script>']);
    $key = $record::mentionTypeKey();

    $html = $this->renderer->tokensToHtml("#{$key}:{$record->getKey()}");

    expect($html)->not->toContain('<script>');
    expect($html)->toContain('&lt;script&gt;');
});

test('adds the state dot attributes for a record with a state', function (): void {
    MentionableFixture::register('mentionable_fixture');
    $record = MentionableFixture::create([
        'name' => 'RMA 1234',
        'state_label' => 'In Progress',
        'state_color' => 'violet',
    ]);
    $key = $record::mentionTypeKey();

    $html = $this->renderer->tokensToHtml("#{$key}:{$record->getKey()}");

    expect($html)->toContain('data-mention-state="In Progress"');
    expect($html)->toContain('title="In Progress"');
    expect($html)->toContain('style="--mention-state-color: var(--color-violet-500)"');
});

test('omits the state dot attributes for a record without a state', function (): void {
    MentionableFixture::register('mentionable_fixture');
    $record = MentionableFixture::create(['name' => 'RMA 1234']);
    $key = $record::mentionTypeKey();

    $html = $this->renderer->tokensToHtml("#{$key}:{$record->getKey()}");

    expect($html)->not->toContain('data-mention-state');
});
