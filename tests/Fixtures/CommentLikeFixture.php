<?php

namespace FluxErp\Tests\Fixtures;

use FluxErp\Contracts\MentionsContent;
use FluxErp\Traits\Model\RecordsMentions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class CommentLikeFixture extends Model implements MentionsContent
{
    use RecordsMentions;

    protected $table = 'comment_like_fixtures';

    protected $guarded = [];

    /**
     * Register the fixture in the morph map under the given type key.
     */
    public static function register(string $typeKey = 'comment_like_fixture'): void
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
    public function mentionableTextFields(): array
    {
        return ['body'];
    }

    public function setText(string $text): static
    {
        $this->forceFill(['body' => $text])->save();

        return $this;
    }
}
