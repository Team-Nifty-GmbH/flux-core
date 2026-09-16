<?php

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Models\User;
use FluxErp\Support\Collection\UserCollection;
use FluxErp\Support\Mentions\MentionParser;
use FluxErp\Tests\Fixtures\MentionableFixture;
use FluxErp\Tests\Fixtures\UserMentionableFixture;

beforeEach(function (): void {
    $this->parser = new MentionParser();
});

test('parses #key:id record tokens', function (): void {
    MentionableFixture::register('ticket');

    $result = $this->parser->parse('Schau #ticket:1234 an', app(UserCollection::class));

    expect($result)->toHaveCount(1);
    expect($result[0]['type'])->toBe(MentionTypeEnum::Record);
    expect($result[0]['mentionable_type'])->toBe('ticket');
    expect($result[0]['mentionable_id'])->toBe(1234);
});

test('parses #key:id as a record mention', function (): void {
    MentionableFixture::register('ticket');

    $out = $this->parser->parse('see #ticket:7', app(UserCollection::class));

    expect($out)->toHaveCount(1);
    expect($out[0]['type'])->toBe(MentionTypeEnum::Record);
    expect($out[0]['mentionable_type'])->toBe('ticket');
    expect($out[0]['mentionable_id'])->toBe(7);
});

test('parses @user:id as an explicit user mention', function (): void {
    $out = $this->parser->parse('hi @user:42', app(UserCollection::class));

    expect($out)->toHaveCount(1);
    expect($out[0]['type'])->toBe(MentionTypeEnum::User);
    expect($out[0]['user_id'])->toBe(42);
    expect($out[0]['mentionable_type'])->toBe('user');
    expect($out[0]['mentionable_id'])->toBe(42);
});

test('parses multiple registered user types under their own keys', function (): void {
    UserMentionableFixture::register('agent');

    $out = $this->parser->parse('hi @user:42 and @agent:7', app(UserCollection::class));

    $agent = collect($out)->firstWhere('mentionable_type', 'agent');
    expect($agent)->not->toBeNull();
    expect($agent['type'])->toBe(MentionTypeEnum::User);
    expect($agent['mentionable_id'])->toBe(7);
    expect($agent['user_id'])->toBeNull();

    $user = collect($out)->firstWhere('mentionable_type', 'user');
    expect($user['user_id'])->toBe(42);
});

test('ignores #user:id (users are @, not #)', function (): void {
    MentionableFixture::register('ticket');

    expect($this->parser->parse('nope #user:1', app(UserCollection::class)))->toBe([]);
});

test('parses @firstname user tokens via member scope', function (): void {
    $member = User::factory()->make(['id' => 42, 'firstname' => 'Martin', 'user_code' => 'MS']);

    $result = $this->parser->parse('Hallo @martin', app(UserCollection::class, ['items' => [$member]]));

    expect($result)->toHaveCount(1);
    expect($result[0]['type'])->toBe(MentionTypeEnum::User);
    expect($result[0]['user_id'])->toBe(42);
});

test('ignores @here, @all and @channel outside a chat context', function (): void {
    foreach (['here', 'all', 'channel'] as $token) {
        expect($this->parser->parse("@{$token}", app(UserCollection::class)))->toBe([]);
    }
});

test('ignores mentions inside code fences', function (): void {
    MentionableFixture::register('ticket');

    $text = "Normal #ticket:1\n```\n#ticket:2\n```\n#ticket:3";

    $result = $this->parser->parse($text, app(UserCollection::class));

    expect(collect($result)->pluck('mentionable_id')->all())->toEqual([1, 3]);
});

test('ignores escaped \\# tokens', function (): void {
    MentionableFixture::register('ticket');

    $result = $this->parser->parse('\\#ticket:1 ist nicht gemeint, aber #ticket:2 schon', app(UserCollection::class));

    expect($result)->toHaveCount(1);
    expect($result[0]['mentionable_id'])->toBe(2);
});

test('does not double-parse a record token as a user token', function (): void {
    MentionableFixture::register('ticket');
    $member = User::factory()->make(['id' => 5, 'firstname' => 'ticket', 'user_code' => 'TKT']);

    $result = $this->parser->parse('#ticket:1', app(UserCollection::class, ['items' => [$member]]));

    expect($result)->toHaveCount(1);
    expect($result[0]['type'])->toBe(MentionTypeEnum::Record);
});
