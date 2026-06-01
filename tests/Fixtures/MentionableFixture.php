<?php

namespace FluxErp\Tests\Fixtures;

use FluxErp\Contracts\IsSubscribable;
use FluxErp\Traits\Model\Mentionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MentionableFixture extends Model implements IsSubscribable
{
    use Mentionable;

    protected $table = 'mentionable_fixtures';

    protected $guarded = [];

    /**
     * Register the fixture (or any class) in the morph map under the given
     * type key so the `Mentionable` trait + `get_models_with_trait()` helper
     * pick it up. This is the equivalent of calling `Relation::morphMap([...])`
     * manually.
     */
    public static function register(string $typeKey, ?string $class = null): void
    {
        Relation::morphMap([
            $typeKey => $class ?? static::class,
        ]);

        Cache::forget('models_with_trait:' . Mentionable::class);
    }

    /**
     * @return Collection<int, static>
     */
    public static function searchMentionCandidates(string $query, int $limit = 5): Collection
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return collect();
        }

        return static::query()
            ->where('name', 'like', $trimmed . '%')
            ->limit($limit)
            ->get();
    }

    public function getMentionUrl(): string
    {
        return '/fixtures/' . $this->getKey();
    }
}
