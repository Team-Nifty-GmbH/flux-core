<?php

namespace FluxErp\Tests\Fixtures;

use FluxErp\Contracts\MentionsContent;
use FluxErp\Contracts\ProvidesMentionNotification;
use FluxErp\Traits\Model\RecordsMentions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Notification;

class NotifyingCommentFixture extends Model implements MentionsContent, ProvidesMentionNotification
{
    use RecordsMentions;

    protected $table = 'comment_like_fixtures';

    protected $guarded = [];

    public static function register(string $typeKey = 'notifying_comment'): void
    {
        Relation::morphMap([
            $typeKey => static::class,
        ]);
    }

    public static function createWithText(string $text): static
    {
        return static::query()->create(['body' => $text]);
    }

    /**
     * @return array<int, string>
     */
    public function mentionableColumns(): array
    {
        return ['body'];
    }

    public function mentionNotification(): Notification
    {
        return new FixtureMentionNotification();
    }
}
