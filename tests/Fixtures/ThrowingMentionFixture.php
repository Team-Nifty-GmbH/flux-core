<?php

namespace FluxErp\Tests\Fixtures;

use FluxErp\Traits\Model\Mentionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class ThrowingMentionFixture extends Model
{
    use Mentionable;

    protected $table = 'mentionable_fixtures';

    protected $guarded = [];

    public static function register(string $typeKey): void
    {
        Relation::morphMap([
            $typeKey => static::class,
        ]);

        Cache::forget('models_with_trait:' . Mentionable::class);
    }

    public static function searchMentionCandidates(string $query, int $limit = 5): Collection
    {
        throw new RuntimeException('search engine unavailable');
    }

    public function getMentionUrl(): ?string
    {
        return '/x';
    }
}
