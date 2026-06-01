<?php

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Services\Mentions\MentionParser;
use FluxErp\Tests\Fixtures\MentionableFixture;

beforeEach(function (): void {
    $this->parser = new MentionParser();
});

it('parses #key:id record tokens', function (): void {
    MentionableFixture::register('ticket');

    $result = $this->parser->parse('Schau #ticket:1234 an', collect());

    expect($result)->toHaveCount(1);
    expect($result[0]['type'])->toBe(MentionTypeEnum::Record);
    expect($result[0]['mentionable_type'])->toBe('ticket');
    expect($result[0]['mentionable_id'])->toBe(1234);
});

it('parses #key:id as a record mention', function (): void {
    MentionableFixture::register('ticket');

    $out = $this->parser->parse('see #ticket:7', collect());

    expect($out)->toHaveCount(1);
    expect($out[0]['type'])->toBe(MentionTypeEnum::Record);
    expect($out[0]['mentionable_type'])->toBe('ticket');
    expect($out[0]['mentionable_id'])->toBe(7);
});

it('parses @user:id as an explicit user mention', function (): void {
    $out = $this->parser->parse('hi @user:42', collect());

    expect($out)->toHaveCount(1);
    expect($out[0]['type'])->toBe(MentionTypeEnum::User);
    expect($out[0]['user_id'])->toBe(42);
    expect($out[0]['mentionable_type'])->toBeNull();
});

it('ignores #user:id (users are @, not #)', function (): void {
    MentionableFixture::register('ticket');

    expect($this->parser->parse('nope #user:1', collect()))->toBe([]);
});

it('parses @firstname user tokens via member scope', function (): void {
    $member = (object) ['id' => 42, 'firstname' => 'Martin', 'user_code' => 'MS'];

    $result = $this->parser->parse('Hallo @martin', collect([$member]));

    expect($result)->toHaveCount(1);
    expect($result[0]['type'])->toBe(MentionTypeEnum::User);
    expect($result[0]['user_id'])->toBe(42);
});

it('parses @all and @channel into Channel mentions', function (): void {
    foreach (['all', 'channel'] as $token) {
        $result = $this->parser->parse("@{$token}", collect());

        expect($result[0]['type'])->toBe(MentionTypeEnum::Channel);
    }
});

it('parses @here into a Here mention', function (): void {
    $result = $this->parser->parse('@here', collect());

    expect($result[0]['type'])->toBe(MentionTypeEnum::Here);
});

it('ignores mentions inside code fences', function (): void {
    MentionableFixture::register('ticket');

    $text = "Normal #ticket:1\n```\n#ticket:2\n```\n#ticket:3";

    $result = $this->parser->parse($text, collect());

    expect(collect($result)->pluck('mentionable_id')->all())->toEqual([1, 3]);
});

it('ignores escaped \\# tokens', function (): void {
    MentionableFixture::register('ticket');

    $result = $this->parser->parse('\\#ticket:1 ist nicht gemeint, aber #ticket:2 schon', collect());

    expect($result)->toHaveCount(1);
    expect($result[0]['mentionable_id'])->toBe(2);
});

it('does not double-parse a record token as a user token', function (): void {
    MentionableFixture::register('ticket');
    $member = (object) ['id' => 5, 'firstname' => 'ticket', 'user_code' => 'TKT'];

    $result = $this->parser->parse('#ticket:1', collect([$member]));

    expect($result)->toHaveCount(1);
    expect($result[0]['type'])->toBe(MentionTypeEnum::Record);
});
