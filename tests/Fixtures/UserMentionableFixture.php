<?php

namespace FluxErp\Tests\Fixtures;

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\Facades\MentionableType;
use FluxErp\Traits\Model\Mentionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;

class UserMentionableFixture extends Model
{
    use Mentionable;

    protected $table = 'user_mentionable_fixtures';

    protected $guarded = [];

    public static function mentionType(): string
    {
        return MentionTypeEnum::User;
    }

    public static function register(string $typeKey): void
    {
        Relation::morphMap([
            $typeKey => static::class,
        ]);

        Cache::forget('models_with_trait:' . Mentionable::class);

        MentionableType::autoDiscover();
    }
}
