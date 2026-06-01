<?php

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Models\Mention;
use FluxErp\Models\User;
use FluxErp\Notifications\MentionNotification;
use FluxErp\Tests\Fixtures\CommentLikeFixture;
use FluxErp\Tests\Fixtures\MentionableFixture;
use Illuminate\Support\Facades\Notification;
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

it('persists a user mention when a source model is saved', function (): void {
    $u = User::factory()->create();
    CommentLikeFixture::createWithText("Hallo @user:{$u->id}");

    expect(Mention::count())->toBe(1);
    expect(Mention::first()->user_id)->toBe($u->id);
    expect(Mention::first()->mention_type)->toBe(MentionTypeEnum::User);
    expect(Mention::first()->mention_target_type)->toBeNull();
});

it('persists a record mention when a source model is saved', function (): void {
    $fixture = MentionableFixture::query()->create(['name' => 'Acme']);
    CommentLikeFixture::createWithText("Hallo #ticket:{$fixture->id}");

    expect(Mention::count())->toBe(1);
    expect(Mention::first()->mention_target_type)->toBe('ticket');
});

it('fires MentionNotification only for newly-added user mentions on edit', function (): void {
    Notification::fake();
    $u = User::factory()->create();
    $u2 = User::factory()->create();

    $source = CommentLikeFixture::createWithText("Hallo @user:{$u->id}");
    Notification::assertSentTo($u, MentionNotification::class);
    Notification::assertNotSentTo($u2, MentionNotification::class);

    Notification::fake();
    $source->setText("Hallo @user:{$u->id} @user:{$u2->id}");

    Notification::assertNotSentTo($u, MentionNotification::class); // already mentioned
    Notification::assertSentTo($u2, MentionNotification::class);   // new
});

it('removes mention rows when the source is deleted', function (): void {
    $u = User::factory()->create();
    $source = CommentLikeFixture::createWithText("@user:{$u->id}");

    expect(Mention::count())->toBe(1);
    $source->delete();
    expect(Mention::count())->toBe(0);
});
