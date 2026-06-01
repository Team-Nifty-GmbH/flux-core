<?php

use FluxErp\Models\Mention;
use FluxErp\Services\Mentions\MentionSync;
use FluxErp\Tests\Fixtures\CommentLikeFixture;
use FluxErp\Tests\Fixtures\MentionableFixture;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    if (! Schema::hasTable('comment_like_fixtures')) {
        Schema::create('comment_like_fixtures', function ($table): void {
            $table->id();
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('mentionable_fixtures')) {
        Schema::create('mentionable_fixtures', function ($table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    CommentLikeFixture::register('comment_like_fixture');
    MentionableFixture::register('ticket');
});

it('inserts new mention rows on first sync', function (): void {
    $source = CommentLikeFixture::createWithText('Hi @user:' . $this->user->getKey());
    app(MentionSync::class)->sync($source);

    expect(Mention::count())->toBe(1);
});

it('is idempotent when content is unchanged', function (): void {
    $source = CommentLikeFixture::createWithText('Hi @user:' . $this->user->getKey());
    app(MentionSync::class)->sync($source);
    app(MentionSync::class)->sync($source);

    expect(Mention::count())->toBe(1);
});

it('removes obsolete rows when a mention is edited away', function (): void {
    $source = CommentLikeFixture::createWithText('#ticket:1 #ticket:2');
    app(MentionSync::class)->sync($source);

    $source->setText('#ticket:1');
    app(MentionSync::class)->sync($source);

    expect(Mention::pluck('mention_target_id')->all())->toEqual([1]);
});

it('returns the delta of added rows', function (): void {
    $source = CommentLikeFixture::withoutEvents(
        fn () => CommentLikeFixture::createWithText('#ticket:1'),
    );
    app(MentionSync::class)->sync($source);

    CommentLikeFixture::withoutEvents(
        fn () => $source->setText('#ticket:1 #ticket:2'),
    );
    $result = app(MentionSync::class)->sync($source);

    expect($result->added)->toHaveCount(1);
    expect($result->added[0]['mention_target_id'])->toBe(2);
});
