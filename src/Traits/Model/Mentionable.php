<?php

namespace FluxErp\Traits\Model;

use FluxErp\Traits\Scout\Searchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

trait Mentionable
{
    public static function mentionTypeKey(): string
    {
        return morph_alias(static::class);
    }

    public static function mentionTypeLabel(): string
    {
        return Str::headline(class_basename(static::class));
    }

    public static function mentionTypeIcon(): string
    {
        return 'tag';
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

        if (in_array(Searchable::class, class_uses_recursive(static::class), true)) {
            return static::search($trimmed)->take($limit)->get();
        }

        return static::query()
            ->where('name', 'like', $trimmed . '%')
            ->limit($limit)
            ->get();
    }

    public function getMentionLabel(): string
    {
        return (string) ($this->name ?? $this->getKey());
    }

    public function getMentionUrl(): string
    {
        throw new RuntimeException(sprintf(
            'Model [%s] uses the Mentionable trait and must implement getMentionUrl().',
            static::class,
        ));
    }
}
